<?php
// provjera radi li API

require_once __DIR__ . '/../konfigBP.php';

function respond_json($data, $status = 200)
{
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

respond_json([
  'ok'   => true,
  'time' => date('Y-m-d H:i:s'),
], 200);
