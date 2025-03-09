<?php
// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('GOOGLE_REDIRECT_URI', 'http://localhost/app/google_callback.php');

// Initialize Google Client
$autoload_path = __DIR__ . '/../vendor/autoload.php';

function getGoogleClient() {
    global $autoload_path;
    
    if (!file_exists($autoload_path)) {
        throw new Exception('Google Client library not installed. Please run: composer require google/apiclient:^2.15.0');
    }
    
    require_once $autoload_path;
    
    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->addScope('email');
    $client->addScope('profile');
    
    return $client;
}
?> 