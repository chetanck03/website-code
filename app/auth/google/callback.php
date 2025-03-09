<?php
require_once '../../includes/config.php';
require_once '../../vendor/autoload.php';

// Initialize Google Client
$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);

try {
    // Get token from code
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);

        // Get user info
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email = mysqli_real_escape_string($conn, $google_account_info->email);
        $name = mysqli_real_escape_string($conn, $google_account_info->name);
        $google_id = mysqli_real_escape_string($conn, $google_account_info->id);
        
        // Check if user exists
        $check_user = mysqli_query($conn, "SELECT * FROM users WHERE google_id = '$google_id' OR email = '$email'");
        
        if (mysqli_num_rows($check_user) > 0) {
            // User exists - update their information
            $user = mysqli_fetch_assoc($check_user);
            $update_query = "UPDATE users SET 
                           google_id = '$google_id',
                           name = '$name',
                           last_login = NOW()
                           WHERE id = " . $user['id'];
            mysqli_query($conn, $update_query);
            
            $_SESSION['user_id'] = $user['id'];
        } else {
            // Create new user
            $insert_query = "INSERT INTO users (google_id, email, name, created_at) 
                           VALUES ('$google_id', '$email', '$name', NOW())";
            mysqli_query($conn, $insert_query);
            $_SESSION['user_id'] = mysqli_insert_id($conn);
        }
        
        $_SESSION['success'] = "Successfully logged in!";
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Authentication failed: " . $e->getMessage();
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

// If we get here, something went wrong
$_SESSION['error'] = "Authentication failed. Please try again.";
header('Location: ' . BASE_URL . '/login.php');
exit();
?> 