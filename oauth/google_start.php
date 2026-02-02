<?php
require_once __DIR__ . '/../konfigBP.php';

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = [
  'client_id' => GOOGLE_CLIENT_ID,
  'redirect_uri' => GOOGLE_REDIRECT_URI,
  'response_type' => 'code',
  'scope' => 'openid email profile',
  'state' => $state,
  'prompt' => 'select_account',
  'access_type' => 'online',
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;
