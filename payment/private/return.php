<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UniBook — Kết quả thanh toán</title>
    <link rel="stylesheet" href="return.css">
</head>
<body>
<main class="result <?= $success ? 'success' : 'pending' ?>">
    <p class="brand">UniBook</p>
    <div class="symbol" aria-hidden="true"><?= $success ? '✓' : '!' ?></div>
    <h1><?= $escape($title) ?></h1>
    <p><?= $escape($message) ?></p>
    <?php if ($valid): ?>
        <p>Mã giao dịch: <strong><?= $escape($data['vnp_TxnRef'] ?? '') ?></strong></p>
    <?php endif; ?>
    <?php if ($valid): ?>
        <p id="storefront-status" role="status">Đang kiểm tra xác nhận thanh toán…</p>
        <script src="return-status.js?v=reconcile-poll-4" defer></script>
    <?php endif; ?>
    <a href="../user/user/controller/index.php?pg=donhang">Xem đơn hàng</a>
    <a href="../user/user/controller/index.php">Về UniBook</a>
</main>
</body>
</html>
