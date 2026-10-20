<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=86400');
$path = dirname(__DIR__, 2) . '/database/vietnam-addresses.json';
if (!is_file($path)) {
    http_response_code(503);
    echo '{"error":"Address catalogue unavailable"}';
    exit;
}
// The checked-in snapshot makes checkout independent of third-party API uptime.
echo ltrim(file_get_contents($path), "\xEF\xBB\xBF");
