<?php
declare(strict_types=1);
// CLI only. Import authorized Web2 Sandbox credentials without displaying them.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$env = dirname(__DIR__) . '/.env';
if (!is_file($env)) {
    $source = file_get_contents('D:/xampp/htdocs/Web2/src/controller/db_controller/vnpay_helper.php');
    $credentials = [];
    foreach (['tmnCode','hashSecret'] as $key) {
        if (!preg_match("/'".$key."'\\s*=>\\s*'([^']+)'/", $source, $match)) throw new RuntimeException('Cannot locate authorized Sandbox configuration.');
        $credentials[$key]=$match[1];
    }
    $base='https://canal-parlor-fiber.ngrok-free.dev/qlpm';
    $text='vnp_TmnCode='.$credentials['tmnCode']."\n".'vnp_HashSecret='.$credentials['hashSecret']."\n".
        'VNP_RETURN_URL='.$base."/payment/vnpay-return\n".
        'VNP_IPN_URL='.$base."/payment/vnpay-ipn\n".
        "VNP_SERVER_IP=127.0.0.1\nPAYMENT_DB_DSN=mysql:host=127.0.0.1;dbname=webbansach;charset=utf8mb4\nPAYMENT_DB_USER=root\nPAYMENT_DB_PASSWORD=\n";
    if (file_put_contents($env,$text,LOCK_EX)===false) throw new RuntimeException('Cannot save local configuration.');
    echo "Sandbox configuration imported (credentials hidden).\n";
} else echo "Existing .env preserved.\n";
$db=new PDO('mysql:host=127.0.0.1;dbname=webbansach;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->exec(file_get_contents(__DIR__.'/storefront-schema.sql'));
$db->exec(file_get_contents(__DIR__.'/receipt-stock.sql'));
$db->exec(file_get_contents(__DIR__.'/reconciliation.sql'));
$db->exec(file_get_contents(__DIR__.'/after-sales.sql'));
echo "Storefront migration applied to localhost/webbansach.\n";
