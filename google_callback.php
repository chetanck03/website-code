<?php
session_start();
require_once 'config/db_connect.php';
require_once 'config/google_auth_config.php';

try {
    $client = getGoogleClient();
    
    // Get token from Google
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (isset($token['error'])) {
        throw new Exception($token['error_description']);
    }
    
    $client->setAccessToken($token);
    
    // Get user info
    $google_oauth = new Google\Service\Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $google_id = mysqli_real_escape_string($conn, $google_account_info->id);
    $email = mysqli_real_escape_string($conn, $google_account_info->email);
    $name = mysqli_real_escape_string($conn, $google_account_info->name);
    $picture = mysqli_real_escape_string($conn, $google_account_info->picture);
    
    // Check if user exists
    $check_user = mysqli_query($conn, "SELECT * FROM users WHERE google_id = '$google_id' OR email = '$email'");
    
    if (mysqli_num_rows($check_user) > 0) {
        // User exists - update their info and log them in
        $user = mysqli_fetch_assoc($check_user);
        
        if (!$user['google_id']) {
            // Link Google account to existing email account
            mysqli_query($conn, "UPDATE users SET google_id = '$google_id', google_picture = '$picture' WHERE email = '$email'");
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['success'] = "Successfully logged in with Google!";
        
        if ($user['user_type'] == 'admin') {
            header('location: /app/admin/dashboard.php');
        } else {
            header('location: /app/index.php');
        }
        exit();
    } else {
        // Create new user
        // Generate username from email
        $username = strtolower(explode('@', $email)[0]);
        $base_username = $username;
        $counter = 1;
        
        // Make sure username is unique
        while (mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'"))) {
            $username = $base_username . $counter;
            $counter++;
        }
        
        $insert_query = "INSERT INTO users (username, email, full_name, google_id, google_picture, user_type) 
                        VALUES ('$username', '$email', '$name', '$google_id', '$picture', 'user')";
        
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = 'user';
            $_SESSION['success'] = "Account created successfully with Google!";
            
            header('location: /app/index.php');
            exit();
        } else {
            throw new Exception("Failed to create user account.");
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Authentication failed: " . $e->getMessage();
    header('location: /app/login.php');
    exit();
}
?> 