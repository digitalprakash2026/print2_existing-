<?php
/**
 * head.php — HTML <head> partial (does NOT open <body>)
 * Included by home.php, product.php, etc. BEFORE header.php
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'RCS Graphic — Premium Print Ordering') ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDesc ?? 'Professional printing services — business cards, brochures, banners and more. Fast delivery, GST invoice, secure Razorpay payment.') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Merriweather:wght@600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/app.css">
<meta name="csrf-token" content="<?= htmlspecialchars($csrf ?? '') ?>">
</head>
<body>
