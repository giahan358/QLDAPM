<?php
/** Keep unchanged historical addresses; validate new selections against the local catalogue. */
function validShippingRegion(array $data, ?array $existing = null): bool {
    foreach (['thanhpho', 'huyen', 'xa'] as $key) {
        if (!isset($data[$key]) || !is_string($data[$key])) return false;
    }
    if ($existing && $data['thanhpho'] === $existing['thanhpho'] &&
        $data['huyen'] === $existing['huyen'] && $data['xa'] === $existing['xa']) return true;
    if ($data['huyen'] !== '') return false;
    $raw = file_get_contents(dirname(__DIR__) . '/database/vietnam-addresses.json');
    $provinces = json_decode(ltrim($raw, "\xEF\xBB\xBF"), true, 32, JSON_THROW_ON_ERROR);
    foreach ($provinces as $province) {
        if ($province['name'] !== $data['thanhpho']) continue;
        foreach ($province['wards'] as $ward) if ($ward['name'] === $data['xa']) return true;
    }
    return false;
}
