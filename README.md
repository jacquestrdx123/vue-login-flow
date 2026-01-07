# Vue Login Flow

A Laravel package that quickly sets up custom authentication guards with Inertia.js/Vue login pages.

## Features

- Automatically configures Laravel guards and providers
- Generates Vue login components with Inertia.js
- Creates login controllers with authentication logic
- Optional HandleInertiaRequest middleware for guard-specific data sharing
- Supports both session and Sanctum authentication

## Installation

1. Install the package via Composer:

```bash
composer require jacquestredoux/vue-login-flow
```

2. During installation, you'll be prompted for:
   - **Model name** (e.g., `User`, `Admin`)
   - **Guard name** (e.g., `admin`, `staff`)
   - **URL prefix** (e.g., `/admin`, `/staff`)
   - **Create login page?** (yes/no)
   - **Create HandleInertiaRequest middleware?** (yes/no)

3. Run the install command to copy files to your Laravel application:

```bash
php artisan vue-login-flow:install
```

4. Compile your assets:

```bash
npm run dev
```

## Usage

After installation, you can access the login page at the URL prefix you specified (e.g., `/admin/login`).

### Authentication

The package creates a login controller that handles:
- `GET {url_prefix}/login` - Show login form
- `POST {url_prefix}/login` - Handle login
- `POST {url_prefix}/logout` - Handle logout

### Using the Guard

In your controllers, use the guard like this:

```php
use Illuminate\Support\Facades\Auth;

// Get authenticated user
$user = Auth::guard('admin')->user();

// Check if authenticated
if (Auth::guard('admin')->check()) {
    // User is authenticated
}

// Require authentication in middleware
Route::middleware(['auth:admin'])->group(function () {
    // Protected routes
});
```

### HandleInertiaRequest Middleware

If you enabled the HandleInertiaRequest middleware, it will share the authenticated user for your guard in Inertia responses:

```vue
<script setup>
// In your Vue components
const props = defineProps({
    auth: {
        admin: {
            user: {
                id: Number,
                name: String,
                email: String,
            }
        }
    }
})
</script>
```

You can apply this middleware to specific routes:

```php
Route::prefix('admin')
    ->middleware(['auth:admin', 'admin.handle-inertia'])
    ->group(function () {
        // Your admin routes
    });
```

## Configuration

The package creates a `config/vue-login-flow.php` file with your settings. You can modify it directly or use environment variables:

```env
VUE_LOGIN_FLOW_MODEL=App\Models\Admin
VUE_LOGIN_FLOW_GUARD=admin
VUE_LOGIN_FLOW_URL_PREFIX=/admin
VUE_LOGIN_FLOW_CREATE_LOGIN_PAGE=true
VUE_LOGIN_FLOW_CREATE_MIDDLEWARE=false
VUE_LOGIN_FLOW_AUTH_TYPE=session
```

## File Structure

After installation, the following files are created:

```
app/
├── Http/
│   ├── Controllers/
│   │   └── {GuardName}/
│   │       └── LoginController.php
│   └── Middleware/
│       └── {GuardName}HandleInertiaRequest.php (optional)

resources/
└── js/
    └── Pages/
        └── {GuardName}/
            └── Login.vue

routes/
└── web.php (routes added)
```

## Requirements

- PHP 8.1+
- Laravel 10.0+ or 11.0+
- Inertia.js Laravel adapter
- Vue 3

## License

MIT

## Support

For issues and questions, please open an issue on GitHub.

# vue-login-flow
