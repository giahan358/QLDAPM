<?php
declare(strict_types=1);

namespace UniBook\Payment;

use DateTimeImmutable;
use DateTimeZone;
use PDO;
use RuntimeException;
use Throwable;

final class PaymentError extends RuntimeException
{
    public int $httpStatus;
    public function __construct(string $message, int $status = 409)
    {
        parent::__construct($message);
        $this->httpStatus = $status;
    }
}

final class PaymentService
{
    private PDO $db;
    private Gateway $gateway;
    private string $lock;

    public function __construct(PDO $db, Gateway $gateway)
    {
        $this->db = $db;
        $this->gateway = $gateway;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // SQLite is only used by the isolated test suite; deployment uses InnoDB.
        $this->lock = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
    }

    private function run(string $sql, array $args = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }

    private function atomic(callable $work)
    {
        $this->db->beginTransaction();
        try {
            $result = $work();
            $this->db->commit();
            return $result;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function order(int $id): array
    {
        $order = $this->run('SELECT * FROM orders WHERE id = ?' . $this->lock, [$id])->fetch();
        if (!$order) throw new PaymentError('Không tìm thấy đơn hàng.', 404);
        return $order;
    }

    public static function key(string $key): void
    {
        if (!preg_match('/^[A-Za-z0-9_-]{16,64}$/D', $key)) {
            throw new PaymentError('Idempotency-Key phải dài 16–64 ký tự.', 422);
        }
    }

    private static function json(array $value): string
    {
        unset($value['vnp_SecureHash'], $value['vnp_SecureHashType']);
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function log(string $event, ?int $orderId, ?int $paymentId, array $context): void
    {
        $this->run('INSERT INTO payment_logs(order_id,payment_id,event,context,created_at) VALUES(?,?,?,?,?)',
            [$orderId, $paymentId, $event, self::json($context), Gateway::now()->format('Y-m-d H:i:s')]);
    }

    /** Order amount must already be frozen by trusted checkout code, never POST amount. */
    public function createUrl(int $orderId, int $customerId, string $key, string $ip): array
    {
        self::key($key);
        return $this->atomic(function () use ($orderId, $customerId, $key, $ip) {
            $order = $this->order($orderId);
            if ((int)$order['customer_id'] !== $customerId) throw new PaymentError('Không tìm thấy đơn hàng.', 404);
            if (!in_array($order['status'], ['PENDING', 'FAILED'], true) || (int)$order['review_required']) {
                throw new PaymentError('Đơn hàng không thể thanh toán hoặc đang cần kiểm tra.');
            }
            $existing = $this->run('SELECT p.* FROM payments p JOIN payment_attempts a ON a.payment_id=p.id
                WHERE a.order_id=? AND a.idempotency_key=?', [$orderId, $key])->fetch();
            if ($existing) {
                if ($existing['status'] !== 'PENDING' || $existing['expire_date'] <= Gateway::now()->format('YmdHis')) {
                    throw new PaymentError('Lần thanh toán này đã kết thúc hoặc đang chờ đối soát.');
                }
                return $this->urlResult($existing, $ip);
            }
            if ($this->run("SELECT id FROM payments WHERE order_id=? AND status='PENDING'", [$orderId])->fetch()) {
                throw new PaymentError('Đang có giao dịch chờ xác nhận. Không tạo giao dịch trùng.');
            }
            // Initial attempt + at most five retries. The order lock serializes creation.
            if ((int)$order['attempt_count'] >= 6) throw new PaymentError('Đã hết 5 lần thanh toán lại.', 429);
            $now = Gateway::now();
            $payment = ['order_id' => $orderId, 'amount' => Gateway::amount($order['amount']), 'create_ip' => $ip,
                'vnp_txn_ref' => $now->format('YmdHis') . bin2hex(random_bytes(12)),
                'create_date' => $now->format('YmdHis'), 'expire_date' => $now->modify('+15 minutes')->format('YmdHis')];
            // Unique database constraint is the final protection against reference collisions.
            $this->run('INSERT INTO payments(order_id,vnp_txn_ref,amount,create_date,expire_date,create_ip) VALUES(?,?,?,?,?,?)',
                [$orderId, $payment['vnp_txn_ref'], $payment['amount'], $payment['create_date'], $payment['expire_date'], $ip]);
            $payment['id'] = (int)$this->db->lastInsertId();
            $this->run('INSERT INTO payment_attempts(order_id,payment_id,attempt_number,idempotency_key,created_at) VALUES(?,?,?,?,?)',
                [$orderId, $payment['id'], (int)$order['attempt_count'] + 1, $key, $now->format('Y-m-d H:i:s')]);
            $this->run("UPDATE orders SET status='PENDING',attempt_count=attempt_count+1,updated_at=? WHERE id=?",
                [$now->format('Y-m-d H:i:s'), $orderId]);
            $this->log('PAYMENT_CREATED', $orderId, $payment['id'], ['ref' => $payment['vnp_txn_ref']]);
            return $this->urlResult($payment, $ip);
        });
    }

    private function urlResult(array $payment, string $ip): array
    {
        return ['payment_id' => (int)$payment['id'], 'txn_ref' => $payment['vnp_txn_ref'],
            'payment_url' => $this->gateway->paymentUrl($payment, $payment['create_ip']), 'expires_at' => $payment['expire_date']];
    }

    private static function reply(string $code): array
    {
        return ['RspCode' => $code, 'Message' => [
            '00' => 'Confirm Success', '01' => 'Order not found', '02' => 'Order already confirmed',
            '04' => 'Invalid amount', '97' => 'Invalid signature', '99' => 'Unknown error'][$code]];
    }

    /** Always verify before reading database state, including for duplicate callbacks. */
    public function ipn(string $raw): array
    {
        $data = $this->gateway->verifiedQuery($raw);
        if ($data === null) return self::reply('97');
        if (($data['vnp_TmnCode'] ?? '') !== $this->gateway->tmnCode) return self::reply('01');
        foreach (['vnp_TxnRef' => '/^[A-Za-z0-9]{1,100}$/D',
                  'vnp_ResponseCode' => '/^[0-9]{2}$/D', 'vnp_TransactionStatus' => '/^[0-9]{2}$/D'] as $key => $pattern) {
            if (!preg_match($pattern, $data[$key] ?? '')) return self::reply('99');
        }
        if (!preg_match('/^[0-9]{1,12}$/D', $data['vnp_Amount'] ?? '')) return self::reply('04');
        try {
            return $this->atomic(function () use ($data) {
                $candidate = $this->run('SELECT order_id FROM payments WHERE vnp_txn_ref=?', [$data['vnp_TxnRef']])->fetch();
                if (!$candidate) return self::reply('01');
                // Consistent locking order in all writers: order -> payment -> refund.
                $order = $this->order((int)$candidate['order_id']);
                $payment = $this->run('SELECT * FROM payments WHERE vnp_txn_ref=?' . $this->lock, [$data['vnp_TxnRef']])->fetch();
                $orderId = (int)$order['id']; $paymentId = (int)$payment['id'];
                if ((int)$data['vnp_Amount'] !== (int)$payment['amount'] * 100 ||
                    (int)$payment['amount'] !== (int)$order['amount']) {
                    $this->log('IPN_AMOUNT_MISMATCH', $orderId, $paymentId, $data);
                    return self::reply('04');
                }
                if ($payment['confirmed_at'] !== null) {
                    $conflict = $payment['response_code'] !== $data['vnp_ResponseCode'] ||
                        $payment['transaction_status'] !== $data['vnp_TransactionStatus'] ||
                        ($payment['vnp_transaction_no'] !== null && $payment['vnp_transaction_no'] !== ($data['vnp_TransactionNo'] ?? ''));
                    if ($conflict) {
                        $this->run('UPDATE orders SET review_required=1 WHERE id=?', [$orderId]);
                    }
                    $this->log($conflict ? 'IPN_CONFLICT' : 'IPN_DUPLICATE', $orderId, $paymentId, $data);
                    return self::reply('02');
                }
                $paid = $data['vnp_ResponseCode'] === '00' && $data['vnp_TransactionStatus'] === '00';
                $transactionNo = null;
                if ($paid) {
                    $transactionNo = $data['vnp_TransactionNo'] ?? '';
                    $date = $data['vnp_PayDate'] ?? '';
                    $parsed = DateTimeImmutable::createFromFormat('!YmdHis', $date, new DateTimeZone('Asia/Ho_Chi_Minh'));
                    if (!preg_match('/^[1-9][0-9]{0,14}$/D', $transactionNo) || !$parsed || $parsed->format('YmdHis') !== $date) {
                        return self::reply('99');
                    }
                }
                $now = Gateway::now()->format('Y-m-d H:i:s');
                $this->run('UPDATE payments SET status=?,response_code=?,transaction_status=?,vnp_transaction_no=?,
                    bank_code=?,pay_date=?,raw_response=?,confirmed_at=? WHERE id=?',
                    [$paid ? 'PAID' : 'FAILED', $data['vnp_ResponseCode'], $data['vnp_TransactionStatus'],
                     $transactionNo, substr($data['vnp_BankCode'] ?? '', 0, 20), $paid ? $data['vnp_PayDate'] : null,
                     self::json($data), $now, $paymentId]);
                $review = $data['vnp_ResponseCode'] === '07' || $data['vnp_TransactionStatus'] === '07';
                if (in_array($order['status'], ['PENDING', 'FAILED'], true)) {
                    $this->run('UPDATE orders SET status=?,updated_at=?,review_required=? WHERE id=?',
                        [$paid ? 'PAID' : 'FAILED', $now, $review || (int)$order['review_required'] ? 1 : 0, $orderId]);
                } elseif ($paid) {
                    // A late success must be recorded, but must not resurrect a cancelled order.
                    $this->run('UPDATE orders SET review_required=1 WHERE id=?', [$orderId]);
                }
                $this->log('IPN_CONFIRMED', $orderId, $paymentId,
                    ['category' => Gateway::paymentMessage($data['vnp_ResponseCode'])[0], 'response' => $data]);
                return self::reply('00');
            });
        } catch (Throwable $e) {
            // Never expose SQL, raw query strings or configuration/secret values.
            error_log('UniBook payment IPN failed: ' . get_class($e));
            return self::reply('99');
        }
    }

    /** Durable reservation is committed before the external refund call. */
    public function refund(int $paymentId, int $amount, string $key, string $actor, ?callable $transport = null): array
    {
        self::key($key);
        Gateway::amount($amount);
        if (!preg_match('/^[A-Za-z0-9_-]{1,64}$/D', $actor)) throw new PaymentError('Invalid operator identity.', 422);
        $prepared = $this->atomic(function () use ($paymentId, $amount, $key, $actor) {
            $candidate = $this->run('SELECT order_id FROM payments WHERE id=?', [$paymentId])->fetch();
            if (!$candidate) throw new PaymentError('Không tìm thấy thanh toán.', 404);
            $order = $this->order((int)$candidate['order_id']);
            $payment = $this->run('SELECT * FROM payments WHERE id=?' . $this->lock, [$paymentId])->fetch();
            $existing = $this->run('SELECT * FROM refunds WHERE idempotency_key=?', [$key])->fetch();
            if ($existing) {
                if ((int)$existing['payment_id'] !== $paymentId || (int)$existing['amount'] !== $amount) {
                    throw new PaymentError('Idempotency-Key đã dùng cho yêu cầu khác.');
                }
                return ['existing' => $existing];
            }
            if ($order['status'] !== 'PAID' || $order['fulfillment_status'] !== 'PENDING' ||
                $payment['status'] !== 'PAID' || $payment['transaction_status'] !== '00' ||
                (int)$order['review_required']) throw new PaymentError('Đơn hàng chưa đủ điều kiện hoàn tiền.');
            $paidAt = DateTimeImmutable::createFromFormat('!YmdHis', $payment['pay_date'], new DateTimeZone('Asia/Ho_Chi_Minh'));
            if (!$paidAt || $paidAt->modify('+7 days') < Gateway::now()) throw new PaymentError('Đã quá thời hạn hoàn tiền 7 ngày.');
            if ((int)$order['refund_hold']) throw new PaymentError('Đang có yêu cầu hoàn tiền cần xác nhận.');
            $remaining = (int)$payment['amount'] - (int)$payment['refunded_amount'];
            if ($amount > $remaining) throw new PaymentError('Số tiền vượt phần còn có thể hoàn.', 422);
            $now = Gateway::now()->format('Y-m-d H:i:s');
            $refund = ['request_id' => bin2hex(random_bytes(16)), 'amount' => $amount,
                'transaction_type' => $amount === (int)$payment['amount'] ? '02' : '03'];
            $payload = $this->gateway->refundPayload($payment, $refund, $actor);
            $this->run("INSERT INTO refunds(payment_id,request_id,idempotency_key,amount,transaction_type,status,raw_request,created_at,updated_at)
                VALUES(?,?,?,?,?,'PROCESSING',?,?,?)", [$paymentId, $refund['request_id'], $key, $amount,
                    $refund['transaction_type'], self::json($payload), $now, $now]);
            $refundId = (int)$this->db->lastInsertId();
            $this->run('UPDATE orders SET refund_hold=1,updated_at=? WHERE id=?', [$now, $order['id']]);
            $this->log('REFUND_RESERVED', (int)$order['id'], $paymentId, ['refund_id' => $refundId, 'actor' => $actor]);
            return ['refund_id' => $refundId, 'payload' => $payload];
        });
        if (isset($prepared['existing'])) return $this->publicRefund($prepared['existing']);
        try {
            $response = $transport ? $transport($prepared['payload']) : $this->gateway->sendRefund($prepared['payload']);
            if (!$this->gateway->verifyRefund($response, $prepared['payload'])) {
                throw new RuntimeException('Unverified refund response');
            }
            return $this->recordRefund((int)$prepared['refund_id'], $response);
        } catch (Throwable $e) {
            error_log('UniBook refund needs reconciliation: ' . get_class($e));
            // Crash/timeout does not mean rejection. Keep funds reserved and shipping blocked.
            $this->run("UPDATE refunds SET status='UNKNOWN',updated_at=? WHERE id=? AND status='PROCESSING'",
                [Gateway::now()->format('Y-m-d H:i:s'), $prepared['refund_id']]);
            return ['refund_id' => $prepared['refund_id'], 'status' => 'UNKNOWN',
                'message' => 'Chưa xác định kết quả. Cần đối soát trước khi gửi yêu cầu khác.'];
        }
    }

    /** Apply an independently obtained, signed refund response during reconciliation. */
    public function reconcileRefund(int $refundId, array $response): array
    {
        $refund = $this->run('SELECT * FROM refunds WHERE id=?', [$refundId])->fetch();
        if (!$refund) throw new PaymentError('Không tìm thấy yêu cầu hoàn tiền.', 404);
        $request = json_decode($refund['raw_request'], true, 32, JSON_THROW_ON_ERROR);
        if (!$this->gateway->verifyRefund($response, $request)) throw new PaymentError('Kết quả đối soát không hợp lệ.', 422);
        return $this->recordRefund($refundId, $response);
    }

    private function recordRefund(int $refundId, array $response): array
    {
        return $this->atomic(function () use ($refundId, $response) {
            $candidate = $this->run('SELECT p.order_id,p.id AS payment_id FROM payments p JOIN refunds r ON r.payment_id=p.id WHERE r.id=?', [$refundId])->fetch();
            $order = $this->order((int)$candidate['order_id']);
            $payment = $this->run('SELECT * FROM payments WHERE id=?' . $this->lock, [$candidate['payment_id']])->fetch();
            $refund = $this->run('SELECT * FROM refunds WHERE id=?' . $this->lock, [$refundId])->fetch();
            if (in_array($refund['status'], ['SUCCEEDED', 'REJECTED'], true)) return $this->publicRefund($refund);
            $code = $response['vnp_ResponseCode'];
            $txnStatus = $response['vnp_TransactionStatus'] ?? '';
            $status = 'UNKNOWN';
            if ($code === '00' && $txnStatus === '00') $status = 'SUCCEEDED';
            elseif ($code === '94' || ($code === '00' && in_array($txnStatus, ['05', '06'], true))) $status = 'PROCESSING';
            elseif ($txnStatus === '07') $status = 'REVIEW';
            elseif (in_array($code, ['02', '03', '04', '13', '91', '93', '95', '97'], true) || ($code === '00' && $txnStatus === '09')) $status = 'REJECTED';
            $now = Gateway::now()->format('Y-m-d H:i:s');
            if ($status === 'SUCCEEDED') {
                $total = (int)$payment['refunded_amount'] + (int)$refund['amount'];
                if ($total > (int)$payment['amount']) throw new RuntimeException('Refund ledger invariant violated');
                $this->run('UPDATE payments SET refunded_amount=?,status=? WHERE id=?',
                    [$total, $total === (int)$payment['amount'] ? 'REFUNDED' : 'PAID', $payment['id']]);
                $this->run('UPDATE orders SET refunded_amount=?,status=?,refund_hold=0,updated_at=? WHERE id=?',
                    [$total, $total === (int)$order['amount'] ? 'REFUNDED' : 'PAID', $now, $order['id']]);
            } elseif ($status === 'REJECTED') {
                $this->run('UPDATE orders SET refund_hold=0,updated_at=? WHERE id=?', [$now, $order['id']]);
            } elseif ($status === 'REVIEW') {
                $this->run('UPDATE orders SET review_required=1 WHERE id=?', [$order['id']]);
            }
            $this->run('UPDATE refunds SET status=?,response_code=?,transaction_status=?,vnp_transaction_no=?,raw_response=?,updated_at=? WHERE id=?',
                [$status, $code, $txnStatus ?: null, $response['vnp_TransactionNo'] ?? null, self::json($response), $now, $refundId]);
            $this->log('REFUND_RESULT', (int)$order['id'], (int)$payment['id'], ['refund_id' => $refundId, 'status' => $status, 'response' => $response]);
            return $this->publicRefund($this->run('SELECT * FROM refunds WHERE id=?', [$refundId])->fetch());
        });
    }

    private function publicRefund(array $refund): array
    {
        return ['refund_id' => (int)$refund['id'], 'status' => $refund['status'],
            'response_code' => $refund['response_code'], 'transaction_status' => $refund['transaction_status']];
    }
}
