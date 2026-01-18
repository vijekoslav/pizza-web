<?php
// konfigBP.php — centralna konfiguracija

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// prilagodi po potrebi (ako ti je mapa drukčije nazvana/promijenjena)
define('BASE', '/pizza-web/');

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

// --- Helperi koji su ti nedostajali ---

// Vrati PDO konekciju (kompatibilno s postojećim kodom koji zove db())
function db(): PDO {
    // koristimo $pdo iz vanjskog scope-a
    return $GLOBALS['pdo'];
}

// Je li admin prijavljen
function is_admin(): bool {
    return !empty($_SESSION['is_admin']) && (int)$_SESSION['is_admin'] === 1;
}

// Ako nije admin, odbij pristup
function require_admin(): void {
    if (!is_admin()) {
        header('Location: ' . BASE . 'prijava.php');
        exit;
    }
}

// Siguran HTML escape (ispuštanje)
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Jednostavan redirect unutar aplikacije
function redirect(string $path): void {
    header('Location: ' . BASE . ltrim($path, '/'));
    exit;
}
