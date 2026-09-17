<?php
require __DIR__ . '/../config.php';
if (!csrf_ok()) {
    header('Location: index.php');
    exit;
}
$_SESSION = [];
session_regenerate_id(true);
session_destroy();
header('Location: login.php');
exit;