<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Hospital Management System') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php
$flash = get_flash();
if ($flash):
?>
<div class="flash-message flash-<?= e($flash['type']) ?>" id="flashMsg">
    <span><?= e($flash['message']) ?></span>
    <button onclick="document.getElementById('flashMsg').style.display='none'">&times;</button>
</div>
<?php endif; ?>
