<?php
declare(strict_types=1);

require_once __DIR__ . '/Gateway.php';
require_once __DIR__ . '/PaymentService.php';

/** Minimal literal .env reader: no interpolation, shell execution or secret logging. */
function paymentConfig(): array
{
    $values = [];
    $path = dirname(__DIR__) . '/.env';
    if (is_file($path)) {
        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (!preg_match('/^([A-Za-z_][A-Za-z0-9_]*)=(.*)$/D', $line, $match)) {
                throw new RuntimeException('Invalid payment configuration file');
            }
            $value = trim($match[2]);
            if (strlen($value) >= 2 && (($value[0] === '"' && substr($value, -1) === '"') ||
                ($value[0] === "'" && substr($value, -1) === "'"))) $value = substr($value, 1, -1);
            $values[$match[1]] = $value;
        }
    }
    foreach (['vnp_TmnCode', 'vnp_HashSecret', 'VNP_RETURN_URL', 'VNP_SERVER_IP',
              'PAYMENT_DB_DSN', 'PAYMENT_DB_USER', 'PAYMENT_DB_PASSWORD', 'PAYMENT_ADMIN_KEY'] as $key) {
        $env = getenv($key);
        if ($env !== false) $values[$key] = $env;
    }
    return $values;
}

function paymentGateway(array $config): \UniBook\Payment\Gateway
{
    return new \UniBook\Payment\Gateway($config['vnp_TmnCode'] ?? '', $config['vnp_HashSecret'] ?? '',
        $config['VNP_RETURN_URL'] ?? '', $config['VNP_SERVER_IP'] ?? '');
}

function paymentService(array $config, \UniBook\Payment\Gateway $gateway): \UniBook\Payment\PaymentService
{
    $dsn = $config['PAYMENT_DB_DSN'] ?? '';
    if (strpos($dsn, 'mysql:') !== 0) throw new RuntimeException('MySQL DSN is required');
    $pdo = new PDO($dsn, $config['PAYMENT_DB_USER'] ?? '', $config['PAYMENT_DB_PASSWORD'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]);
    $pdo->exec("SET time_zone = '+07:00'");
    return new \UniBook\Payment\PaymentService($pdo, $gateway);
}
