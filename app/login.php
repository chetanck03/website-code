<?php
session_start();
require_once 'config/db_connect.php';
require_once 'config/google_auth_config.php';

try {
    $client = getGoogleClient();
    $google_login_url = $client->createAuthUrl();
    $google_error = null;
} catch (Exception $e) {
    // Log error for debugging
    error_log("Google Client Error: " . $e->getMessage());
    $google_login_url = null;
    $google_error = "Google Sign-In is temporarily unavailable. Please use email/password to login or try again later.";
}

// Get user's Google profile picture if they're logged in
$user_picture = null;
if (isset($_SESSION['user_id'])) {
    $user_query = "SELECT google_picture FROM users WHERE id = " . $_SESSION['user_id'];
    $user_result = mysqli_query($conn, $user_query);
    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user_data = mysqli_fetch_assoc($user_result);
        $user_picture = $user_data['google_picture'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // First check if user exists
    $query = "SELECT * FROM users WHERE username='$username'";
    $results = mysqli_query($conn, $query);

    if (mysqli_num_rows($results) == 1) {
        $user = mysqli_fetch_assoc($results);
        
        // Check if password matches (either hashed or plain)
        if ($user['password'] === NULL || $user['password'] === '' || 
            password_verify($password, $user['password']) || 
            $user['password'] === $password) { // For legacy plain text passwords
            
            // If password was plain text or NULL, hash it for future use
            if ($user['password'] === NULL || $user['password'] === '' || $user['password'] === $password) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET password = '$hashed_password' WHERE id = " . $user['id']);
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = $user['user_type'];
            
            if ($user['user_type'] == 'admin') {
                header('location: /app/admin/dashboard.php');
            } else {
                header('location: /app/index.php');
            }
            exit();
        }
    }
    
    $error = "Wrong username/password combination";
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Art Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            /* Light Theme Variables */
            --primary-color: #8e44ad;
            --secondary-color: #9b59b6;
            --text-color: #333;
            --bg-color: #fff;
            --card-bg: #fff;
            --input-bg: #fff;
            --input-text: #333;
            --input-border: #ced4da;
            --input-focus-border: #9b59b6;
            --input-focus-shadow: rgba(155, 89, 182, 0.25);
            --btn-text: #fff;
            --link-color: #8e44ad;
            --border-color: #dee2e6;
            --hover-color: #7d3c98;
        }
        
        [data-theme="dark"] {
            /* Dark Theme Variables */
            --primary-color: #9b59b6;
            --secondary-color: #8e44ad;
            --text-color: #f8f9fa;
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --input-bg: #2d2d2d;
            --input-text: #f8f9fa;
            --input-border: #444;
            --input-focus-border: #9b59b6;
            --input-focus-shadow: rgba(155, 89, 182, 0.5);
            --btn-text: #fff;
            --link-color: #bb8fce;
            --border-color: #2d2d2d;
            --hover-color: #a569bd;
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
            min-height: 100vh;
            padding: 40px 0;
            background-image: linear-gradient(135deg, rgba(142, 68, 173, 0.1) 0%, rgba(142, 68, 173, 0.2) 100%);
            background-size: cover;
            background-attachment: fixed;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            margin: 40px auto;
            padding: 30px;
            background-color: var(--card-bg);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            transform: translateY(0);
            animation: fadeInUp 0.5s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }
        
        .login-container h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-control {
            background-color: var(--input-bg);
            color: var(--input-text);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 0.25rem var(--input-focus-shadow);
            background-color: var(--input-bg);
            color: var(--input-text);
        }
        
        .form-control::placeholder {
            color: var(--input-text);
            opacity: 0.6;
        }
        
        .input-icon {
            position: absolute;
            top: 14px;
            left: 15px;
            color: var(--primary-color);
        }
        
        .form-control-with-icon {
            padding-left: 45px;
        }
        
        .btn-login {
            background-color: var(--primary-color);
            border: none;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            color: var(--btn-text);
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background-color: var(--hover-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(142, 68, 173, 0.4);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: var(--text-color);
        }
        
        .login-footer a {
            color: var(--link-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .login-footer a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .brand-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            display: block;
            text-align: center;
        }
        
        .theme-switch-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            position: relative;
            z-index: 10;
        }
        
        .theme-switch {
            display: inline-block;
            position: relative;
            width: 60px;
            height: 30px;
        }
        
        .theme-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: var(--primary-color);
        }
        
        input:checked + .slider:before {
            transform: translateX(30px);
        }
        
        .slider-icons {
            display: flex;
            justify-content: space-between;
            padding: 0 10px;
            align-items: center;
            height: 100%;
            color: white;
            font-size: 14px;
        }
        
        .theme-text {
            margin: 0 10px;
            font-size: 14px;
        }
        
        .btn-google {
            background-color: #fff;
            color: #3c4043;
            border: 1px solid #dadce0;
            width: 100%;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 4px;
            font-weight: 500;
            letter-spacing: 0.25px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            height: 40px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .btn-google:hover {
            background-color: #f8f9fa;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            border-color: #dadce0;
            color: #3c4043;
        }
        
        .btn-google span {
            margin-left: 8px;
            font-family: "Google Sans", Roboto, Arial, sans-serif;
        }
        
        .btn-google .google-icon {
            width: 18px;
            height: 18px;
            min-width: 18px;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48cGF0aCBkPSJNMTcuNiA5LjJsLS4xLTEuOEg5djMuNGg0LjhDMTMuNiAxMiAxMyAxMyAxMiAxMy42djIuMmgzYTguOCA4LjggMCAwIDAgMi42LTYuNnoiIGZpbGw9IiM0Mjg1RjQiIGZpbGwtcnVsZT0ibm9uemVybyIvPjxwYXRoIGQ9Ik05IDE4YzIuNCAwIDQuNS0uOCA2LTIuMmwtMy0yLjJhNS40IDUuNCAwIDAgMS04LTIuOUgxVjEzYTkgOSAwIDAgMCA4IDV6IiBmaWxsPSIjMzRBODUzIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz48cGF0aCBkPSJNNCAxMC43YTUuNCA1LjQgMCAwIDEgMC0zLjRWNUgxYTkgOSAwIDAgMCAwIDhsMy0yLjN6IiBmaWxsPSIjRkJCQzA1IiBmaWxsLXJ1bGU9Im5vbnplcm8iLz48cGF0aCBkPSJNOSAzLjZjMS4zIDAgMi41LjQgMy40IDEuM0wxNSAyLjNBOSA5IDAgMCAwIDEgNWwzIDIuNGE1LjQgNS40IDAgMCAxIDUtMy43eiIgZmlsbD0iI0VBNDMzNSIgZmlsbC1ydWxlPSJub256ZXJvIi8+PHBhdGggZD0iTTAgMGgxOHYxOEgweiIvPjwvZz48L3N2Zz4=');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            margin-right: 8px;
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        
        .divider::before,
        .divider::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background-color: var(--border-color);
        }
        
        .divider::before {
            left: 0;
        }
        
        .divider::after {
            right: 0;
        }
        
        .divider span {
            background-color: var(--card-bg);
            padding: 0 15px;
            color: var(--text-color);
            font-size: 14px;
        }
        
        .user-profile {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: block;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .welcome-text {
            color: var(--text-color);
            margin-bottom: 20px;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <i class="fas fa-paint-brush brand-icon"></i>
            <h2>Welcome Back</h2>
            
            <?php if(isset($_SESSION['user_id']) && $user_picture): ?>
            <div class="user-profile">
                <img src="<?php echo htmlspecialchars($user_picture); ?>" alt="Profile Picture" class="profile-picture">
                <p class="welcome-text">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>
            <?php endif; ?>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if(isset($google_error)): ?>
                <div class="alert alert-warning"><?php echo $google_error; ?></div>
            <?php endif; ?>
            
            <?php if(!isset($_SESSION['user_id'])): ?>
                <?php if ($google_login_url): ?>
                <a href="<?php echo $google_login_url; ?>" class="btn btn-google">
                    <div class="google-icon"></div>
                    <span>Continue with Google</span>
                </a>
                
                <div class="divider">
                    <span>or</span>
                </div>
                <?php endif; ?>
                
                <form action="login.php" method="POST">
                    <div class="form-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-control form-control-with-icon" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control form-control-with-icon" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn btn-login">Login</button>
                </form>
                <div class="login-footer">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                    <a href="index.php">Back to Home</a>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <a href="/app/index.php" class="btn btn-login mb-3">Go to Dashboard</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            <?php endif; ?>

            <div class="theme-switch-wrapper">
                <span class="theme-text">Light</span>
                <label class="theme-switch" for="checkbox">
                    <input type="checkbox" id="checkbox" checked />
                    <div class="slider">
                        <div class="slider-icons">
                            <i class="fas fa-sun"></i>
                            <i class="fas fa-moon"></i>
                        </div>
                    </div>
                </label>
                <span class="theme-text">Dark</span>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check for saved theme preference or use default dark theme
        const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : 'dark';
        
        // Apply the saved theme on page load
        document.documentElement.setAttribute('data-theme', currentTheme);
        
        // Update checkbox state based on current theme
        if (currentTheme === 'dark') {
            document.getElementById('checkbox').checked = true;
        }
        
        // Theme toggle functionality
        document.getElementById('checkbox').addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
        });
    </script>
</body>
</html>
