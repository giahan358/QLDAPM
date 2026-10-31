<?php
declare(strict_types=1);

// CLI only. Input must be an authentic signed refund result, never hand-edited JSON.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/bootstrap.php';
if ($argc !== 3 || !ctype_digit($argv[1]) || !is_file($argv[2])) {
    fwrite(STDERR, "Usage: php reconcile-refund.php REFUND_ID signed-result.json\n");
    exit(1);
}
try {
    $config = paymentConfig();
    $service = paymentService($config, paymentGateway($config));
    $result = json_decode(file_get_contents($argv[2]), true, 32, JSON_THROW_ON_ERROR);
    echo json_encode($service->reconcileRefund((int)$argv[1], $result), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'Reconciliation failed: ' . get_class($e) . PHP_EOL);
    exit(1);
}
