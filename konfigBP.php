<?php
// konfigBP - centralni config

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

define('BASE', '/pizza-web/');

// Google OAuth (demo user login)
define('GOOGLE_CLIENT_ID', '894454318340-rbbtu2fl3ggcaamppupjs92k798lc16q.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'secret');
define('GOOGLE_REDIRECT_URI', 'http://localhost/pizza-web/oauth/google_callback.php');

// gdje vratiti usera nakon logina
define('LOGIN_REDIRECT', BASE);

// --- PDO konekcija ---
$DB_HOST = '127.0.0.1';
$DB_NAME = 'pizza_web';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

try {
  $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ]);
} catch (PDOException $e) {
  die('Greška spajanja na bazu: ' . $e->getMessage());
}

function db(): PDO
{
  return $GLOBALS['pdo'];
}

function is_admin(): bool
{
  return !empty($_SESSION['is_admin']) && (int)$_SESSION['is_admin'] === 1;
}

function require_admin(): void
{
  if (!is_admin()) {
    header('Location: ' . BASE . 'prijava.php');
    exit;
  }
}

function h(string $s): string
{
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
  header('Location: ' . BASE . ltrim($path, '/'));
  exit;
}
