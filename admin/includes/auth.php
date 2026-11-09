<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
