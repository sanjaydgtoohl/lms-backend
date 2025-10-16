## LMS Portal API (Lumen 10)

A lightweight microservice-style API built with Lumen 10 featuring:

- JWT authentication (tymon/jwt-auth)
- Role/Permission (RBAC) with models and relationships
- Repository + Service layers
- API Resources for consistent responses
- Centralized response/error handling

### Requirements
- PHP 8.1+
- Composer
- MySQL (or compatible) database

### Installation
```bash
git clone <repo-url>
cd lms-protal
composer install
```

### Environment
Create your env file and configure DB + JWT:
```bash
cp .env.example .env   # if not present, create and fill values
```
Required keys in `.env`:
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- JWT_SECRET (run command below to generate)

### Bootstrap
```bash
# Generate JWT secret
php artisan jwt:secret

# Run migrations
php artisan migrate

# Seed roles/permissions
php artisan db:seed --class=RolePermissionSeeder
```

### Run
```bash
php -S localhost:8000 -t public
# or use your preferred PHP server
```

### API Base URL
- `http://localhost:8000/api/v1`

### Auth Endpoints
- POST `/auth/register` – register
- POST `/auth/login` – login
- POST `/auth/logout` – logout (JWT required)
- POST `/auth/refresh` – refresh token (JWT required)
- GET `/auth/me` – current user (JWT required)

Authorization header:
```
Authorization: Bearer <JWT>
```

### User Endpoints (permissions required)
- GET `/users` (`users:read`)
- GET `/users/{id}` (`users:read`)
- GET `/users/search` (`users:read`)
- GET `/users/statistics` (`users:read`)
- POST `/users` (`users:create`)
- PUT `/users/{id}` (`users:update`)
- DELETE `/users/{id}` (`users:delete`)
- POST `/users/{id}/change-password` (`users:update`)

### Role/Permission Model Overview
- `User` ↔ `Role` (many-to-many via `user_roles`)
- `User` ↔ `Permission` (many-to-many via `user_permissions`)
- `Role` ↔ `Permission` (many-to-many via `role_permissions`)

User helpers:
- `hasRole(string $role)`
- `hasPermission(string $permission)`
- `hasAnyPermission(array $permissions)`
- `hasAllPermissions(array $permissions)`

### Consistent Responses
All endpoints return a normalized shape:
```json
{
  "success": true,
  "message": "...",
  "data": {},
  "meta": { "timestamp": "...", "status_code": 200 }
}
```
Errors:
```json
{
  "success": false,
  "message": "...",
  "errors": {},
  "error_code": "VALIDATION_ERROR|UNAUTHORIZED|FORBIDDEN|NOT_FOUND|SERVER_ERROR",
  "meta": { "timestamp": "...", "status_code": 422 }
}
```

### Development Notes
- App config enables facades/eloquent and registers JWT + custom middlewares
- Route groups under `routes/api.php` (prefixed `/api/v1`)
- Global exception handler returns consistent JSON for API routes

### License
MIT
