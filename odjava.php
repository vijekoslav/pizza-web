<?php
require_once __DIR__ . '/konfigBP.php';
session_destroy();
header('Location: ' . BASE . 'prijava.php');
