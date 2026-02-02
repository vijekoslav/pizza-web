<?php
require_once __DIR__ . '/../konfigBP.php';

$code  = $_GET['code']  ?? '';
$state = $_GET['state'] ?? '';

if ($code === '' || $state === '' || empty($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $state)) {
  die('OAuth error (invalid state).');
}
unset($_SESSION['oauth_state']);

// 1) exchange code -> token
$tokenUrl = 'https://oauth2.googleapis.com/token';

$post = [
  'code' => $code,
  'client_id' => GOOGLE_CLIENT_ID,
  'client_secret' => GOOGLE_CLIENT_SECRET,
  'redirect_uri' => GOOGLE_REDIRECT_URI,
  'grant_type' => 'authorization_code',
];

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query($post),
  CURLOPT_RETURNTRANSFER => true,
]);
$tokenRaw = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($tokenRaw === false || $http >= 400) {
  die('Token exchange failed.');
}

$token = json_decode($tokenRaw, true);
$accessToken = $token['access_token'] ?? '';
if ($accessToken === '') die('No access token.');

// 2) userinfo
$ch = curl_init('https://openidconnect.googleapis.com/v1/userinfo');
curl_setopt_array($ch, [
  CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
  CURLOPT_RETURNTRANSFER => true,
]);
$userRaw = curl_exec($ch);
$http2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($userRaw === false || $http2 >= 400) {
  die('Userinfo failed.');
}

$user = json_decode($userRaw, true);

$email = $user['email'] ?? '';
$name  = $user['name'] ?? '';
$pic   = $user['picture'] ?? '';

if ($email === '') die('No email returned.');

// 3) store demo-user in session
$_SESSION['user_logged_in'] = 1;
$_SESSION['user_email'] = $email;
$_SESSION['user_name'] = $name;
$_SESSION['user_picture'] = $pic;

header('Location: ' . LOGIN_REDIRECT);
exit;
