<?php
require_once '../../includes/config.php';
require_once '../../vendor/autoload.php';

// Initialize Google Client
$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope("email");
$client->addScope("profile");

// Generate URL for Google login
$auth_url = $client->createAuthUrl();

// Redirect to Google login page
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit;
?> 