<?php
require_once __DIR__ . '/../konfigBP.php';

unset($_SESSION['user_logged_in'], $_SESSION['user_email'], $_SESSION['user_name'], $_SESSION['user_picture']);
header('Location: ' . BASE);
exit;
