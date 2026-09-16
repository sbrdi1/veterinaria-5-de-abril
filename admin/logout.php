<?php
require __DIR__ . '/../config.php';
session_regenerate_id(true);
session_unset();
session_destroy();
header('Location: login.php');
exit;