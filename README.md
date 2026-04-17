## LMS Portal API (Lumen 10)

A lightweight microservice-style API built with Lumen 10 featuring:

- JWT authentication (tymon/jwt-auth)
- Role/Permission (RBAC) system using Laratrust
- Repository + Service layers
- API Resources for consistent responses
- Centralized response/error handling
- Login history tracking
- User profile management
- Geography reference data import from `sql/*.sql`

### Requirements
- PHP 8.1+
- Composer
- MySQL (or compatible) database

### Environment Setup
Create your `.env` file and configure database + JWT:
```bash
cp .env.example .env   # if not present, create and fill values
```

Required keys in `.env`:
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `JWT_SECRET` (run command below to generate)
- `APP_TIMEZONE` (optional, defaults to UTC)

### App Key Generation
```bash
php -r "file_put_contents('.env', preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=base64:'.base64_encode(random_bytes(32)), file_get_contents('.env')));"
```

### Installation
```bash
git clone <repo-url>
cd lms-backend
composer install
```

### Bootstrap & Setup
```bash
# Generate JWT secret
php artisan jwt:secret

# Run migrations
php artisan migrate

# Seed database (geography data, users, roles, permissions, and other data)
php artisan db:seed

# Or seed specific seeders
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=LaratrustDummySeeder
```

### Geography Data Import
The repository includes split SQL files in `sql/` with reference data for:
- `regions`
- `subregions`
- `countries`
- `states`
- `cities`

These tables are seeded automatically through `php artisan db:seed`.

If you need to re-import only the geography data, use the dedicated command:
```bash
php artisan geography:import-dump
```

Useful options:
- `--table=regions` / `--table=countries` / etc. to import only selected tables
- `--no-truncate` to append without clearing existing rows first

Example:
```bash
php artisan geography:import-dump --table=regions --table=subregions
```

### Lead History Table Fix
If you previously hit an error about `lead_assign_histories` not existing, run migrations again after pulling this version. The base migration for that table is now included, along with guards on later alter migrations so partial database states no longer break the migration chain.

### Default Seeded Data
- **Users**: 
  - `admin@example.com` / `password` (admin role)
  - `manager@example.com` / `password` (manager role)
  - `user@example.com` / `password` (user role)

- **Roles**: `admin`, `manager`, `user`
- **Permissions**: Various user, profile, and role management permissions
- **Geography**: Region, country, state, city, and subregion reference tables from `sql/*.sql`

### Run Development Server
```bash
php -S localhost:8000 -t public
# or use your preferred PHP server
```

### API Base URL
- `http://localhost:8000/api/v1`

---

## API Endpoints

### Authentication Endpoints (Public)
- `POST /auth/register` – Register a new user
  - Body: `name`, `email`, `password`, `password_confirmation`, `phone` (optional)
- `POST /auth/login` – Login user
  - Body: `email`, `password`
- `POST /auth/forgot-password` – Request password reset
  - Body: `email`
- `POST /auth/reset-password` – Reset password
  - Body: `token`, `password`, `password_confirmation`

### Authentication Endpoints (Protected - JWT Required)
- `POST /auth/logout` – Logout user
- `POST /auth/refresh` – Refresh JWT token
- `GET /auth/me` – Get current authenticated user

### User Management Endpoints (Protected - JWT Required)
- `GET /users` – List all users (paginated)
  - Query params: `per_page` (default: 15)
- `GET /users/{id}` – Get user by ID
- `GET /users/search` – Search users
  - Query params: `name`, `email`, `role`, `status`, `created_at`, `per_page`
- `GET /users/statistics` – Get user statistics
- `POST /users` – Create new user
  - Body: `name`, `email`, `password`, `phone` (optional), `status` (optional)
- `PUT /users/{id}` – Update user
- `DELETE /users/{id}` – Delete user
- `POST /users/{id}/change-password` – Change user password
  - Body: `current_password`, `password`, `password_confirmation`

### Profile Endpoints (Protected - JWT Required)
- `GET /profile` – Get current user profile
- `PUT /profile` – Update current user profile
- `GET /profile/login-history` – Get login history
  - Query params: `per_page` (default: 15)

### Other Endpoints
- Industries, Departments, Designations
- Countries, States, Cities, Zones
- Agencies, Agency Groups, Agency Types
- Brands, Brand Types
- Lead Sources, Lead Sub Sources
- Regions

See `routes/api.php` for complete route list.

---

## Authentication

### Authorization Header
Include JWT token in the Authorization header for protected endpoints:
```
Authorization: Bearer <JWT_TOKEN>
```

### JWT Token Response
After successful login/register, you'll receive:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

---

## Role & Permission System (Laratrust)

### Database Structure
- `User` ↔ `Role` (many-to-many via `role_user` table)
- `User` ↔ `Permission` (many-to-many via `permission_user` table)
- `Role` ↔ `Permission` (many-to-many via `permission_role` table)

### Role/Permission Tables
- **roles**: `id`, `name`, `display_name`, `description`, `timestamps`
- **permissions**: `id`, `name`, `display_name`, `description`, `timestamps`
- **role_user**: `role_id`, `user_id`, `user_type`, `timestamps`
- **permission_user**: `permission_id`, `user_id`, `user_type`, `timestamps`
- **permission_role**: `permission_id`, `role_id`, `timestamps`

### User Model Methods
```php
// Check roles
$user->hasRole('admin')
$user->hasAnyRole(['admin', 'manager'])
$user->hasAllRoles(['admin', 'manager'])

// Check permissions
$user->hasPermission('users.create')
$user->hasAnyPermission(['users.create', 'users.update'])
$user->hasAllPermissions(['users.create', 'users.update'])

// Get all permissions (including role permissions)
$user->getAllPermissions()

// Role management
$user->assignRole($role)
$user->removeRole($role)

// Permission management
$user->givePermission($permission)
$user->removePermission($permission)
```

### Middleware Usage
```php
// Role middleware
Route::group(['middleware' => ['jwt.auth', 'role:admin']], function () {
    // Admin only routes
});

// Permission middleware
Route::group(['middleware' => ['jwt.auth', 'permission:users.create']], function () {
    // Permission required routes
});
```

---

## API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  },
  "meta": {
    "timestamp": "2025-11-05T10:00:00.000000Z",
    "status_code": 200,
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 100,
      "last_page": 7
    }
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error message"]
  },
  "error_code": "VALIDATION_ERROR",
  "meta": {
    "timestamp": "2025-11-05T10:00:00.000000Z",
    "status_code": 422
  }
}
```

### Error Codes
- `VALIDATION_ERROR` - Validation failed (422)
- `UNAUTHORIZED` - Authentication required (401)
- `FORBIDDEN` - Insufficient permissions (403)
- `NOT_FOUND` - Resource not found (404)
- `METHOD_NOT_ALLOWED` - HTTP method not allowed (405)
- `SERVER_ERROR` - Internal server error (500)

---

## Project Structure

```
app/
├── Console/           # Artisan commands
├── Contracts/         # Repository interfaces
├── Events/            # Event classes
├── Exceptions/        # Exception handlers
├── Http/
│   ├── Controllers/   # API controllers
│   ├── Middleware/    # Custom middleware
│   └── Resources/     # API resources
├── Jobs/              # Queue jobs
├── Listeners/         # Event listeners
├── Models/            # Eloquent models
├── Providers/         # Service providers
├── Repositories/      # Repository implementations
├── Services/          # Business logic services
└── Traits/            # Reusable traits

database/
├── migrations/        # Database migrations
├── seeders/           # Database seeders
└── factories/         # Model factories

routes/
├── api.php            # API routes
└── web.php            # Web routes
```

---

## Development Notes

### Architecture
- **Repository Pattern**: Data access layer abstraction
- **Service Layer**: Business logic separation
- **API Resources**: Consistent response formatting
- **Middleware**: Authentication, authorization, and request handling

### Key Features
- JWT authentication with refresh tokens
- Role-based access control (RBAC)
- Permission-based access control
- Login history tracking
- Soft deletes for users
- UUID support for models
- Centralized exception handling
- Consistent API response format

### Configuration
- Facades and Eloquent enabled in `bootstrap/app.php`
- JWT middleware registered
- Custom role/permission middleware
- Global exception handler for API routes
- Service providers registered

### Database
- All models use soft deletes
- UUID support via trait
- Timestamps automatically managed
- Foreign key constraints with cascade deletes

---

## Testing

### Running Tests
```bash
php artisan test
# or
vendor/bin/phpunit
```

### Manual Testing
1. Register a new user
2. Login to get JWT token
3. Use token in Authorization header for protected routes
4. Test role/permission-based access

---

## Troubleshooting

### Common Issues

1. **JWT Secret Missing**
   ```bash
   php artisan jwt:secret
   ```

2. **Database Connection Error**
   - Check `.env` file database configuration
   - Ensure database exists

3. **Migration Errors**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Role/Permission Not Working**
   - Ensure Laratrust migration has run
   - Check seeders have been executed
   - Verify user has assigned roles/permissions

---

## License
MIT
