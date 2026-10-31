<?php
declare(strict_types=1);

namespace UniBook\Payment;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;

final class Gateway
{
    public const PAY_URL = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
    public const API_URL = 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction';
    public const REFUND_FIELDS = ['vnp_RequestId','vnp_Version','vnp_Command','vnp_TmnCode',
        'vnp_TransactionType','vnp_TxnRef','vnp_Amount','vnp_TransactionNo','vnp_TransactionDate',
        'vnp_CreateBy','vnp_CreateDate','vnp_IpAddr','vnp_OrderInfo'];
    public const RESPONSE_FIELDS = ['vnp_ResponseId','vnp_Command','vnp_ResponseCode','vnp_Message',
        'vnp_TmnCode','vnp_TxnRef','vnp_Amount','vnp_BankCode','vnp_PayDate','vnp_TransactionNo',
        'vnp_TransactionType','vnp_TransactionStatus','vnp_OrderInfo'];

    private string $secret;
    public string $tmnCode;
    private string $returnUrl;
    public string $serverIp;

    public function __construct(string $tmnCode, string $secret, string $returnUrl, string $serverIp)
    {
        if (!preg_match('/^[A-Za-z0-9]{8}$/D', $tmnCode) || $secret === '' ||
            !filter_var($returnUrl, FILTER_VALIDATE_URL) || parse_url($returnUrl, PHP_URL_SCHEME) !== 'https' ||
            strlen($returnUrl) > 255 || !filter_var($serverIp, FILTER_VALIDATE_IP)) {
            throw new RuntimeException('Invalid payment configuration');
        }
        $this->tmnCode = $tmnCode;
        $this->secret = $secret;
        $this->returnUrl = $returnUrl;
        $this->serverIp = $serverIp;
    }

    public static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
    }

    public static function amount($value): int
    {
        if ((!is_string($value) && !is_int($value)) ||
            !preg_match('/^[1-9][0-9]{0,9}$/D', (string)$value) || (int)$value > 9999999999) {
            throw new InvalidArgumentException('Số tiền phải là số nguyên VND hợp lệ.');
        }
        return (int)$value;
    }

    public static function canonical(array $params): string
    {
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);
        ksort($params, SORT_STRING);
        foreach ($params as $value) {
            if (!is_string($value) && !is_int($value)) {
                throw new InvalidArgumentException('Invalid gateway parameter');
            }
        }
        // PHP urlencode / RFC1738: spaces become '+'. Do not encode twice.
        return http_build_query($params, '', '&', PHP_QUERY_RFC1738);
    }

    public function sign(array $params): string
    {
        return hash_hmac('sha512', self::canonical($params), $this->secret);
    }

    /** Reject duplicate keys and PHP array syntax before verifying the signature. */
    public function verifiedQuery(string $raw): ?array
    {
        if (strlen($raw) > 16384) return null;
        $params = [];
        foreach (explode('&', $raw) as $pair) {
            if ($pair === '') continue;
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $key = urldecode($key);
            if (strpos($key, 'vnp_') !== 0) continue;
            if (!preg_match('/^vnp_[A-Za-z0-9_]+$/D', $key) || array_key_exists($key, $params)) return null;
            $params[$key] = urldecode($value);
        }
        $hash = $params['vnp_SecureHash'] ?? '';
        if (!preg_match('/^[a-fA-F0-9]{128}$/D', $hash) ||
            !hash_equals($this->sign($params), strtolower($hash))) return null;
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);
        return $params;
    }

    public function paymentUrl(array $payment, string $ip): string
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) throw new InvalidArgumentException('Invalid IP');
        $data = [
            'vnp_Version' => '2.1.0', 'vnp_Command' => 'pay', 'vnp_TmnCode' => $this->tmnCode,
            'vnp_Amount' => (string)(self::amount($payment['amount']) * 100),
            'vnp_CurrCode' => 'VND', 'vnp_Locale' => 'vn', 'vnp_OrderType' => 'other',
            'vnp_TxnRef' => $payment['vnp_txn_ref'],
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $payment['order_id'],
            'vnp_CreateDate' => $payment['create_date'], 'vnp_ExpireDate' => $payment['expire_date'],
            'vnp_ReturnUrl' => $this->returnUrl, 'vnp_IpAddr' => $ip,
        ];
        return self::PAY_URL . '?' . self::canonical($data) . '&vnp_SecureHash=' . $this->sign($data);
    }

    public function pipeHash(array $params, array $fields): string
    {
        $parts = [];
        foreach ($fields as $field) {
            $value = $params[$field] ?? '';
            if ((!is_string($value) && !is_int($value)) || strpos((string)$value, '|') !== false) {
                throw new InvalidArgumentException('Invalid API field');
            }
            $parts[] = (string)$value;
        }
        return hash_hmac('sha512', implode('|', $parts), $this->secret);
    }

    public function refundPayload(array $payment, array $refund, string $actor): array
    {
        $data = [
            'vnp_RequestId' => $refund['request_id'], 'vnp_Version' => '2.1.0',
            'vnp_Command' => 'refund', 'vnp_TmnCode' => $this->tmnCode,
            'vnp_TransactionType' => $refund['transaction_type'], 'vnp_TxnRef' => $payment['vnp_txn_ref'],
            'vnp_Amount' => (string)(self::amount($refund['amount']) * 100),
            'vnp_TransactionNo' => $payment['vnp_transaction_no'],
            // Original create date, NOT the payment date or the refund date.
            'vnp_TransactionDate' => $payment['create_date'], 'vnp_CreateBy' => $actor,
            'vnp_CreateDate' => self::now()->format('YmdHis'), 'vnp_IpAddr' => $this->serverIp,
            'vnp_OrderInfo' => 'Hoan tien ' . $refund['request_id'],
        ];
        $data['vnp_SecureHash'] = $this->pipeHash($data, self::REFUND_FIELDS);
        return $data;
    }

    public function verifyRefund(array $response, array $request): bool
    {
        $hash = $response['vnp_SecureHash'] ?? null;
        if (!is_string($hash) || !preg_match('/^[a-fA-F0-9]{128}$/D', $hash)) return false;
        try {
            if (!hash_equals($this->pipeHash($response, self::RESPONSE_FIELDS), strtolower($hash))) return false;
        } catch (InvalidArgumentException $e) { return false; }
        // A valid signature is insufficient: bind the result to this request too.
        foreach (['vnp_Command','vnp_TmnCode','vnp_TxnRef','vnp_Amount','vnp_TransactionType','vnp_OrderInfo'] as $key) {
            if ((string)($response[$key] ?? '') !== (string)$request[$key]) return false;
        }
        return preg_match('/^[0-9]{2}$/D', (string)($response['vnp_ResponseCode'] ?? '')) === 1;
    }

    public function queryPayload(array $payment): array
    {
        $data=['vnp_RequestId'=>bin2hex(random_bytes(16)), 'vnp_Version'=>'2.1.0',
            'vnp_Command'=>'querydr','vnp_TmnCode'=>$this->tmnCode,
            'vnp_TxnRef'=>$payment['txn_ref'],'vnp_TransactionDate'=>$payment['create_date'],
            'vnp_CreateDate'=>self::now()->format('YmdHis'),'vnp_IpAddr'=>$this->serverIp,
            'vnp_OrderInfo'=>'Query UniBook payment '.$payment['txn_ref']];
        $data['vnp_SecureHash']=$this->pipeHash($data,['vnp_RequestId','vnp_Version','vnp_Command',
            'vnp_TmnCode','vnp_TxnRef','vnp_TransactionDate','vnp_CreateDate','vnp_IpAddr','vnp_OrderInfo']);
        return $data;
    }

    public function verifyQueryResponse(array $response, array $request): bool
    {
        $hash=$response['vnp_SecureHash'] ?? '';
        if (!is_string($hash) || !preg_match('/^[a-f0-9]{128}$/iD',$hash)) return false;
        try {
            if (!hash_equals($this->pipeHash($response,array_merge(self::RESPONSE_FIELDS,
                ['vnp_PromotionCode','vnp_PromotionAmount'])),strtolower($hash))) return false;
        } catch (InvalidArgumentException $e) { return false; }
        return ($response['vnp_Command'] ?? '')==='querydr' &&
            ($response['vnp_TmnCode'] ?? '')===$this->tmnCode &&
            ($response['vnp_TxnRef'] ?? '')===($request['vnp_TxnRef'] ?? null);
    }

    public function sendQuery(array $payload): array
    {
        if (($payload['vnp_Command'] ?? '')!=='querydr') throw new InvalidArgumentException('Expected querydr');
        return $this->sendApi($payload);
    }

    /** No automatic POST retries: a timeout may mean the refund was accepted. */
    public function sendRefund(array $payload): array
    {
        return $this->sendApi($payload);
    }

    private function sendApi(array $payload): array
    {
        $curl = curl_init(self::API_URL);
        if (is_file(__DIR__.'/cacert.pem')) curl_setopt($curl,CURLOPT_CAINFO,__DIR__.'/cacert.pem');
        curl_setopt($curl,CURLOPT_USERAGENT,'UniBook/1.0');
        curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_PROTOCOLS => CURLPROTO_HTTPS]);
        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($body === false || $status !== 200 || strlen($body) > 65536) {
            throw new RuntimeException('VNPAY API transport outcome unknown');
        }
        $response = json_decode($body, true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($response)) throw new RuntimeException('Invalid refund response');
        return $response;
    }

    public static function paymentMessage(string $code): array
    {
        $messages = [
            '00' => ['success', 'Thanh toán thành công.'],
            '07' => ['fraud', 'Giao dịch cần kiểm tra an toàn. Vui lòng liên hệ hỗ trợ.'],
            '09' => ['user', 'Thẻ chưa đăng ký dịch vụ thanh toán trực tuyến.'],
            '10' => ['user', 'Thông tin xác thực không đúng quá số lần cho phép.'],
            '11' => ['user', 'Đã hết thời gian thanh toán.'],
            '12' => ['user', 'Thẻ hoặc tài khoản đang bị khóa.'],
            '13' => ['user', 'Mã OTP không đúng.'],
            '24' => ['user', 'Bạn đã hủy giao dịch thanh toán.'],
            '51' => ['user', 'Tài khoản không đủ số dư.'],
            '65' => ['user', 'Giao dịch vượt hạn mức trong ngày.'],
            '79' => ['user', 'Xác thực sai quá số lần cho phép.'],
            '75' => ['system', 'Ngân hàng đang bảo trì, vui lòng thử lại sau.'],
            '99' => ['system', 'Chưa thể hoàn tất thanh toán, vui lòng liên hệ hỗ trợ.'],
        ];
        return $messages[$code] ?? ['system', 'Giao dịch chưa được xác nhận thành công.'];
    }
}
