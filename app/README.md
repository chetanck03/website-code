# Website Setup Instructions

## Prerequisites
- PHP 7.4 or higher
- MySQL Database
- XAMPP/WAMP/LAMP server
- Cashfree Account for Payment Integration
- Google Cloud Console Account for Authentication

## Installation Steps

1. Clone this repository to your web server directory:
```bash
git clone [repository-url]
cd [project-directory]
```

2. Configure Database:
- Create a new MySQL database
- Import the database schema (if provided)
- Update database credentials in `includes/config.php`

## API Configuration

### Cashfree Payment Integration
1. Sign up for a Cashfree account at [https://merchant.cashfree.com/signup](https://merchant.cashfree.com/signup)
2. Get your API credentials from the Cashfree Dashboard
3. Open `payment_callback.php` and update the following variables:
```php
$api_key = "YOUR_CASHFREE_API_KEY";
$api_secret = "YOUR_CASHFREE_API_SECRET";
$is_production = false; // Set to true for production environment
```

### Google OAuth Configuration
1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google+ API and Google OAuth API
4. Go to Credentials → Create Credentials → OAuth Client ID
5. Configure the OAuth consent screen:
   - Add your application name
   - Add authorized domains
   - Select the required scopes (email, profile)
6. Create OAuth 2.0 Client ID:
   - Select Web Application as the application type
   - Add authorized JavaScript origins (your domain)
   - Add authorized redirect URIs (e.g., `https://yourdomain.com/auth/google/callback.php`)
7. Copy your Client ID and Client Secret
8. Update `includes/config.php` with your Google credentials:
```php
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/auth/google/callback.php');
```

### Environment Configuration
- For development: Keep `$is_production = false` to use sandbox environment
- For production: Set `$is_production = true` to use live environment

## Important Files
- `payment_callback.php`: Handles payment verification and order status updates
- `includes/header.php`: Contains common header elements and configurations
- `order_confirmation.php`: Displays order confirmation after successful payment
- `orders.php`: Shows order history and status
- `auth/google/login.php`: Handles Google login initialization
- `auth/google/callback.php`: Processes Google OAuth callback
- `includes/config.php`: Contains all API configurations

## Security Notes
- Never commit API keys directly to version control
- Use environment variables or secure configuration files for sensitive data
- Always validate and sanitize user inputs
- Implement proper error handling and logging
- Store Google OAuth tokens securely
- Use HTTPS in production environment
- Implement CSRF protection for OAuth callbacks

## Testing Payments
1. Use Cashfree test credentials in sandbox mode
2. Test card numbers:
   - Success: 4111 1111 1111 1111
   - Failure: 4111 1111 1111 2007

## Testing Google Auth
1. Use test Google accounts
2. Verify email verification process
3. Test account linking if applicable
4. Ensure proper session handling

## Troubleshooting
If you encounter payment verification issues:
1. Check API credentials
2. Verify webhook configurations
3. Check server logs for errors
4. Ensure proper SSL configuration for production

For Google Auth issues:
1. Verify OAuth credentials and redirect URIs
2. Check for valid SSL certificate
3. Ensure cookies are enabled
4. Verify session configuration
5. Check authorized domains in Google Console

## Support
For any issues:
- Cashfree Documentation: [https://docs.cashfree.com/](https://docs.cashfree.com/)
- Google OAuth Documentation: [https://developers.google.com/identity/protocols/oauth2](https://developers.google.com/identity/protocols/oauth2)
- Create an issue in the repository
- Contact system administrator

## License
[Specify your license here] 