<?php
require_once __DIR__ . '/includes/auth.php';
if (current_admin()) {
    header('Location: admin.php'); exit;
}
header('Location: login.php');
