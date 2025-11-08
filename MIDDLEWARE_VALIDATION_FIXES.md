# Middleware & Validation Configuration Fixes

## Summary
Fixed middleware configuration and standardized validation across all controllers for consistency and proper error handling.

## Issues Fixed

### 1. JwtAuthMiddleware ✅
**Problem**: Was returning raw JSON responses instead of using ResponseService
**Fix**: 
- Added ResponseService dependency injection
- Updated all error responses to use ResponseService for consistency
- Added null check for route() method to prevent errors
- All responses now follow consistent format

**File**: `app/Http/Middleware/JwtAuthMiddleware.php`

### 2. RoleMiddleware ✅
**Status**: Already using ResponseService correctly

### 3. PermissionMiddleware ✅
**Status**: Already using ResponseService correctly

### 4. Validation Trait Created ✅
**Created**: `ValidatesRequests` trait for consistent validation
- Provides `validate()` method that throws ValidationException
- Can be used in any controller by using the trait
- Ensures consistent validation behavior

**File**: `app/Traits/ValidatesRequests.php`

### 5. AgencyController ✅
**Problems**:
- Not using ResponseService (raw JSON responses)
- Inconsistent validation error handling
- Mixed validation approaches

**Fixes**:
- Added ResponseService dependency injection
- Added ValidatesRequests trait
- Updated all methods to use ResponseService
- Standardized validation using trait
- Added proper type hints (JsonResponse)
- Consistent exception handling with ResponseService

**File**: `app/Http/Controllers/AgencyController.php`

### 6. AgencyGroupController ✅
**Problems**: Same as AgencyController

**Fixes**: Same as AgencyController
- Added ResponseService
- Added ValidatesRequests trait
- Standardized all responses
- Consistent error handling

**File**: `app/Http/Controllers/AgencyGroupController.php`

### 7. DepartmentController ✅
**Problems**:
- Using `$this->validate()` but not consistently
- Mixed exception handling

**Fixes**:
- Added ValidatesRequests trait for consistency
- Updated to use trait's validate method
- Simplified exception handling
- Consistent with other controllers

**File**: `app/Http/Controllers/DepartmentController.php`

## Middleware Configuration

### Registered Middleware
All middleware properly registered in `bootstrap/app.php`:
```php
$app->routeMiddleware([
    'jwt.auth' => App\Http\Middleware\JwtAuthMiddleware::class,
    'role' => App\Http\Middleware\RoleMiddleware::class,
    'permission' => App\Http\Middleware\PermissionMiddleware::class,
]);
```

### Middleware Behavior
1. **JwtAuthMiddleware**: 
   - Validates JWT token
   - Checks if user exists and is active
   - Adds user to request object
   - Handles token expiration for refresh endpoint
   - Uses ResponseService for all errors

2. **RoleMiddleware**:
   - Checks if user has required role
   - Uses Laratrust `hasRole()` method
   - Uses ResponseService for errors

3. **PermissionMiddleware**:
   - Checks if user has required permission
   - Uses Laratrust `hasPermission()` method
   - Uses ResponseService for errors

## Validation Standards

### Validation Trait Usage
```php
use App\Traits\ValidatesRequests;

class YourController extends Controller
{
    use ValidatesRequests;

    public function store(Request $request): JsonResponse
    {
        try {
            $rules = [
                'field' => 'required|string|max:255',
            ];
            
            $validatedData = $this->validate($request, $rules);
            // Use validated data...
            
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors(), 'Validation failed');
        }
    }
}
```

### Validation Best Practices
1. **Always use ValidatesRequests trait** for consistent validation
2. **Always catch ValidationException** and return via ResponseService
3. **Use type hints** (JsonResponse) for all controller methods
4. **Use ResponseService** for all responses (success and error)
5. **Wrap in try-catch** blocks for proper exception handling

### Validation Rules Standards
- Use `required` for mandatory fields
- Use `nullable` for optional fields
- Use `sometimes|required` for update operations
- Use `integer` type for foreign keys
- Use `exists:table,column` for foreign key validation
- Use `unique:table,column,except_id` for unique validation on updates

## Response Format

All responses now follow consistent format:

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...},
  "meta": {
    "timestamp": "2025-11-05T10:00:00.000000Z",
    "status_code": 200
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

## Testing Recommendations

1. **Test Middleware**:
   - Test JWT authentication with valid/invalid tokens
   - Test role-based access control
   - Test permission-based access control
   - Test expired tokens on refresh endpoint

2. **Test Validation**:
   - Test all required fields
   - Test validation rules (unique, exists, etc.)
   - Test validation error responses
   - Test update validation (sometimes rules)

3. **Test Error Handling**:
   - Test 401 (Unauthorized) responses
   - Test 403 (Forbidden) responses
   - Test 422 (Validation Error) responses
   - Test 404 (Not Found) responses
   - Test 500 (Server Error) responses

## Files Modified

1. `app/Http/Middleware/JwtAuthMiddleware.php` - Added ResponseService
2. `app/Traits/ValidatesRequests.php` - Created validation trait
3. `app/Http/Controllers/AgencyController.php` - Standardized validation and responses
4. `app/Http/Controllers/AgencyGroupController.php` - Standardized validation and responses
5. `app/Http/Controllers/DepartmentController.php` - Standardized validation

## Next Steps

Consider updating other controllers to use the same pattern:
- BrandController
- CityController
- StateController
- IndustryController
- DesignationController
- LeadSourceController
- LeadSubSourceController
- RegionController
- ZoneController
- BrandTypeController
- AgencyTypeController
- BrandAgencyRelationshipController
- CountryController

All should:
1. Use ValidatesRequests trait
2. Use ResponseService for all responses
3. Have consistent exception handling
4. Use proper type hints

