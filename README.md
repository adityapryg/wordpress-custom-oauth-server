# Custom OAuth Server

A professional WordPress plugin that provides OAuth 2.0 server functionality for multiple web applications. Enable single sign-on (SSO) across all your applications using WordPress as the central authentication provider.

## 🚀 Features

- **OAuth 2.0 Server** - Full OAuth 2.0 authorization server implementation
- **Multiple Applications** - Support unlimited client applications
- **Professional UI** - Modern, responsive authorization interface
- **WordPress Integration** - Uses WordPress users and authentication
- **REST API** - Standard OAuth 2.0 endpoints
- **Admin Interface** - Easy client management through WordPress admin
- **Security First** - Secure token generation and validation
- **Developer Friendly** - Clean, well-documented code

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- HTTPS (recommended for production)

## 📦 Installation

### Manual Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/custom-oauth-server/`
3. Activate the plugin through WordPress admin
4. Go to **Settings → OAuth Server** to configure

### Via WordPress Admin

1. Upload the plugin ZIP file through **Plugins → Add New → Upload Plugin**
2. Activate the plugin
3. Configure in **Settings → OAuth Server**

## ⚙️ Configuration

### 1. Create OAuth Client

1. Go to **Settings → OAuth Server**
2. Click **Add New Client**
3. Fill in the form:
   - **Client Name**: Your application name (e.g., "Marketing Dashboard")
   - **Client ID**: Unique identifier (e.g., "marketing-dashboard")
   - **Client Secret**: Auto-generated secure secret
   - **Redirect URIs**: Your app's callback URLs

### 2. OAuth Endpoints

After activation, these endpoints are available:

```
Authorization: https://yoursite.com/oauth/authorize
Token:         https://yoursite.com/oauth/token
User Info:     https://yoursite.com/wp-json/oauth/v1/me
```

## 🔧 Usage

### Laravel Integration

1. **Install Laravel Socialite**:
```bash
composer require laravel/socialite
```

2. **Add to `config/services.php`**:
```php
'wordpress' => [
    'client_id' => env('WORDPRESS_CLIENT_ID'),
    'client_secret' => env('WORDPRESS_CLIENT_SECRET'),
    'redirect' => env('WORDPRESS_REDIRECT_URI'),
    'base_url' => env('WORDPRESS_BASE_URL'),
],
```

3. **Add to `.env`**:
```env
WORDPRESS_BASE_URL=https://your-wordpress-site.com
WORDPRESS_CLIENT_ID=your-client-id
WORDPRESS_CLIENT_SECRET=your-client-secret
WORDPRESS_REDIRECT_URI=https://your-app.com/auth/wordpress/callback
```

4. **Create Provider**:
```php
// app/Providers/WordPressOAuthProvider.php
<?php

namespace App\Providers;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

class WordPressOAuthProvider extends AbstractProvider
{
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            config('services.wordpress.base_url').'/oauth/authorize',
            $state
        );
    }

    protected function getTokenUrl()
    {
        return config('services.wordpress.base_url').'/oauth/token';
    }

    protected function getTokenFields($code)
    {
        return [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            config('services.wordpress.base_url').'/wp-json/oauth/v1/me',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                    'Accept' => 'application/json',
                ],
            ]
        );

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['ID'],
            'name' => $user['display_name'],
            'email' => $user['user_email'],
            'avatar' => $user['avatar_url'] ?? null,
        ]);
    }
}
```

5. **Register Provider in AppServiceProvider**:
```php
use Laravel\Socialite\Facades\Socialite;
use App\Providers\WordPressOAuthProvider;

public function boot()
{
    Socialite::extend('wordpress', function ($app) {
        $config = $app['config']['services.wordpress'];
        return new WordPressOAuthProvider(
            $app['request'],
            $config['client_id'],
            $config['client_secret'],
            $config['redirect']
        );
    });
}
```

6. **Use in Controller**:
```php
// Redirect to WordPress for authentication
return Socialite::driver('wordpress')->redirect();

// Handle callback
$user = Socialite::driver('wordpress')->user();
```

### Generic OAuth 2.0 Flow

1. **Authorization Request**:
```
GET https://yoursite.com/oauth/authorize?
    response_type=code&
    client_id=YOUR_CLIENT_ID&
    redirect_uri=YOUR_CALLBACK_URL&
    scope=basic&
    state=RANDOM_STRING
```

2. **Token Exchange**:
```bash
curl -X POST https://yoursite.com/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=authorization_code" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET" \
  -d "code=AUTHORIZATION_CODE" \
  -d "redirect_uri=YOUR_CALLBACK_URL"
```

3. **Get User Info**:
```bash
curl -X GET https://yoursite.com/wp-json/oauth/v1/me \
  -H "Authorization: Bearer ACCESS_TOKEN"
```

## 🔒 Security

- All tokens are securely generated using WordPress functions
- Authorization codes expire in 10 minutes
- Access tokens expire in 1 hour
- Refresh tokens expire in 30 days
- Client secrets should be kept secure
- HTTPS is strongly recommended for production

## 🎨 Customization

### Custom Scopes

Edit `includes/functions.php` to add custom scopes:

```php
function custom_oauth_sanitize_scope($scope) {
    $allowed_scopes = [
        'basic',    // Basic profile info
        'profile',  // Full profile
        'email',    // Email address
        'posts',    // User posts (custom)
        'admin'     // Admin access (custom)
    ];
    // ... rest of function
}
```

### Custom Authorization Page

Modify `views/oauth/authorize.php` to customize the authorization interface.

### Custom User Data

Edit `includes/class-oauth-api.php` in the `get_current_user` method to return additional user data.

## 🐛 Troubleshooting

### Common Issues

**"Invalid client_id" error**
- Check that the client ID exists in Settings → OAuth Server
- Verify the client ID matches exactly (case-sensitive)

**"Invalid redirect_uri" error**
- Ensure the redirect URI is registered for the client
- Check for trailing slashes and exact URL matching

**"Invalid authorization code" error**
- Authorization codes expire in 10 minutes
- Each code can only be used once
- Check system time synchronization

**CSS/JS not loading**
- Clear WordPress cache
- Check file permissions
- Verify plugin is activated

### Debug Mode

Enable WordPress debug mode to see OAuth logs:

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check logs in `/wp-content/debug.log` for OAuth-related messages.

## 🚀 Multi-App Architecture Example

```
WordPress OAuth Server (auth.yourcompany.com)
├── Marketing Dashboard (marketing.yourcompany.com)
├── CRM System (crm.yourcompany.com)
├── Analytics Platform (analytics.yourcompany.com)
├── Mobile App (iOS/Android)
└── Third-party Integrations
```

## 📝 API Reference

### Authorization Endpoint

```
GET /oauth/authorize
```

**Parameters:**
- `response_type` (required): Must be "code"
- `client_id` (required): Your client identifier
- `redirect_uri` (required): Callback URL
- `scope` (optional): Requested permissions (default: "basic")
- `state` (recommended): Random string for CSRF protection

### Token Endpoint

```
POST /oauth/token
```

**Parameters:**
- `grant_type` (required): "authorization_code" or "refresh_token"
- `client_id` (required): Your client identifier
- `client_secret` (required): Your client secret
- `code` (required for auth code): Authorization code
- `redirect_uri` (required for auth code): Must match authorization request
- `refresh_token` (required for refresh): Valid refresh token

**Response:**
```json
{
  "access_token": "...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "...",
  "scope": "basic"
}
```

### User Info Endpoint

```
GET /wp-json/oauth/v1/me
Authorization: Bearer ACCESS_TOKEN
```

**Response:**
```json
{
  "ID": 123,
  "user_login": "johndoe",
  "display_name": "John Doe",
  "user_email": "john@example.com",
  "avatar_url": "https://..."
}
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 👨‍💻 Author

**Aditya Prayoga**
- Website: [https://adityapryg.my.id](https://adityapryg.my.id)
- GitHub: [@adityapryg](https://github.com/adityapryg)

## 📞 Support

- **Documentation**: Check this README first
- **Issues**: Report bugs on GitHub
- **Community**: WordPress support forums

## 🔄 Changelog

### Version 1.0.0
- Initial release
- OAuth 2.0 server implementation
- Admin interface for client management
- REST API endpoints
- Laravel integration example

---

**Made with ❤️ for the company (just for formality btw)**
