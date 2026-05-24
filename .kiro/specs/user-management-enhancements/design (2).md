# User Management Enhancements - Design Document

## Overview

This design document outlines the enhancements to the existing user management system in the Cosmetics E-commerce API. The enhancements include adding user status tracking, contact information fields, implementing Spatie permissions for role-based access control, adding search capabilities using Spatie Query Builder, standardizing API responses, and implementing API versioning.

The design maintains backward compatibility with the existing system while introducing modern Laravel best practices and leveraging established packages (Spatie Permission and Query Builder) that are already included in the project dependencies.

## Architecture

### High-Level Architecture

The user management system follows Laravel's MVC architecture with additional layers for API versioning:

```
API Request → Routes (v1) → Middleware (Auth + Permission) → Request Validation → Controller → Service Layer → Model → Database
                                                                                      ↓
API Response ← Resource Transformation ← ApiResponse Helper ← Controller ← Service Layer
```

### Key Architectural Decisions

1. **API Versioning Strategy**: Implement namespace-based versioning (v1) at the route, controller, request, and resource levels to support future API evolution without breaking existing integrations.

2. **Permission System**: Replace the simple role field with Spatie Permission package to provide flexible role and permission management while maintaining backward compatibility with existing admin/manager roles.

3. **Query Builder Integration**: Use Spatie Query Builder for the index endpoint to provide powerful filtering, sorting, and searching capabilities with minimal code.

4. **Response Standardization**: Migrate from BaseApiController's sendResponse/sendError methods to the existing ApiResponse helper for consistency across the entire API.

5. **Database Schema Evolution**: Add new fields (status, phone, address) to the users table while maintaining existing functionality.

## Components and Interfaces

### 1. Database Schema Changes

#### Users Table Migration
Add the following columns to the existing users table:

- `status` (tinyInteger, default: 1) - Uses StatusEnum values (1=ACTIVE, 0=INACTIVE)
- `phone` (string, nullable) - User phone number with validation
- `address` (text, nullable) - User address information

**Design Rationale**: Using tinyInteger for status aligns with the existing StatusEnum pattern used elsewhere in the application. Making phone and address nullable allows gradual data collection without forcing immediate updates to existing records.

#### Spatie Permission Tables
The Spatie Permission package will automatically create the following tables:
- `roles` - Stores role definitions
- `permissions` - Stores permission definitions
- `model_has_roles` - Pivot table linking users to roles
- `model_has_permissions` - Pivot table for direct user permissions
- `role_has_permissions` - Pivot table linking roles to permissions

### 2. Model Enhancements

#### User Model Updates

**New Attributes**:
- Add `status`, `phone`, `address` to `$fillable` array
- Add `status` cast to StatusEnum
- Remove `role` field from fillable (replaced by Spatie roles)

**New Relationships**:
- Spatie Permission traits will add: `roles()`, `permissions()`

**Updated Methods**:
- Modify `isAdmin()` to use Spatie: `hasRole('superAdmin')`
- Modify `isManager()` to use Spatie: `hasRole('supervisor')`
- Modify `hasAdminAccess()` to check multiple roles via Spatie
- Remove role-based scopes (replaced by Spatie query methods)

**New Methods**:
- `isActive()`: Check if user status is ACTIVE
- `isSuspended()`: Check if user status is SUSPENDED (if added)
- `scopeActive($query)`: Query scope for active users
- `scopeByStatus($query, $status)`: Query scope for filtering by status

**Design Rationale**: Maintaining helper methods like `isAdmin()` ensures backward compatibility with existing code while internally leveraging Spatie's more powerful permission system.

### 3. API Versioning Structure

#### Directory Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── V1/
│   │           ├── Admin/
│   │           │   ├── UserController.php
│   │           │   ├── ClientController.php
│   │           │   ├── ProductController.php
│   │           │   ├── OrderController.php
│   │           │   └── InventoryController.php
│   │           ├── Public/
│   │           │   ├── ProductController.php
│   │           │   ├── ClientController.php
│   │           │   └── OrderController.php
│   │           ├── AuthController.php
│   │           ├── FileUploadController.php
│   │           └── LocaleController.php
│   ├── Requests/
│   │   └── V1/
│   │       └── User/
│   │           ├── StoreUserRequest.php
│   │           ├── UpdateUserRequest.php
│   │           └── UpdateUserStatusRequest.php
│   └── Resources/
│       └── V1/
│           ├── UserResource.php
│           └── UserCollection.php
```

**Design Rationale**: Organizing by version at the controller, request, and resource levels allows independent evolution of each API version. The V1 namespace establishes a clear versioning pattern for future versions (V2, V3, etc.).

### 4. Request Validation Classes

#### StoreUserRequest
**Validation Rules**:
- `name`: required, string, max:255
- `email`: required, email, unique:users
- `password`: required, min:8, confirmed
- `phone`: nullable, regex:/^[0-9+\-\s()]+$/, max:20
- `address`: nullable, string, max:500
- `status`: nullable, in:0,1 (defaults to 1)
- `roles`: required, array, exists:roles,name

**Authorization**: Only users with 'create-users' permission

#### UpdateUserRequest
**Validation Rules**:
- `name`: sometimes, string, max:255
- `email`: sometimes, email, unique:users,email,{id}
- `password`: sometimes, min:8, confirmed
- `phone`: nullable, regex:/^[0-9+\-\s()]+$/, max:20
- `address`: nullable, string, max:500
- `roles`: sometimes, array, exists:roles,name

**Authorization**: Only users with 'update-users' permission

#### UpdateUserStatusRequest
**Validation Rules**:
- `status`: required, in:0,1

**Authorization**: Only users with 'update-user-status' permission

**Design Rationale**: Separating status updates into a dedicated request class allows for granular permission control. Phone validation uses a flexible regex pattern to accommodate international formats.

### 5. Resource Classes

#### UserResource
**Transformed Fields**:
```php
[
    'id' => $this->id,
    'name' => $this->name,
    'email' => $this->email,
    'phone' => $this->phone,
    'address' => $this->address,
    'status' => $this->status,
    'status_label' => $this->status === StatusEnum::ACTIVE->value ? 'Active' : 'Inactive',
    'roles' => $this->roles->pluck('name'),
    'permissions' => $this->getAllPermissions()->pluck('name'),
    'created_at' => $this->created_at,
    'updated_at' => $this->updated_at,
]
```

**Conditional Fields**:
- Include `email_verified_at` if not null
- Include `last_login_at` if tracking is implemented

**Design Rationale**: Including both status value and label provides flexibility for frontend implementations. Exposing roles and permissions allows frontend to implement role-based UI features.

### 6. Controller Implementation

#### UserController (V1)

**Endpoints**:

1. **GET /api/v1/admin/users** - List users with filtering
   - Uses Spatie Query Builder
   - Supports: filter[status], filter[search], sort, include
   - Middleware: `auth:sanctum`, `permission:view-users`
   - Returns: UserCollection

2. **POST /api/v1/admin/users** - Create user
   - Validates via StoreUserRequest
   - Assigns roles via Spatie
   - Middleware: `auth:sanctum`, `permission:create-users`
   - Returns: UserResource with 201 status

3. **GET /api/v1/admin/users/{user}** - Show user
   - Middleware: `auth:sanctum`, `permission:view-users`
   - Returns: UserResource

4. **PUT/PATCH /api/v1/admin/users/{user}** - Update user
   - Validates via UpdateUserRequest
   - Syncs roles if provided
   - Middleware: `auth:sanctum`, `permission:update-users`
   - Returns: UserResource

5. **PATCH /api/v1/admin/users/{user}/status** - Update status
   - Validates via UpdateUserStatusRequest
   - Middleware: `auth:sanctum`, `permission:update-user-status`
   - Returns: UserResource

6. **DELETE /api/v1/admin/users/{user}** - Delete user
   - Prevents self-deletion
   - Middleware: `auth:sanctum`, `permission:delete-users`
   - Returns: Success message

**Query Builder Configuration**:
```php
QueryBuilder::for(User::class)
    ->allowedFilters([
        AllowedFilter::exact('status'),
        AllowedFilter::scope('search'), // searches name, email, phone
    ])
    ->allowedSorts(['name', 'email', 'created_at', 'status'])
    ->allowedIncludes(['roles', 'permissions'])
    ->paginate($perPage);
```

**Design Rationale**: Separating status updates into a dedicated endpoint allows for granular permission control and clearer audit trails. The search scope in the User model will handle multi-field searching efficiently.

### 7. Middleware Configuration

#### Permission Middleware Application

All admin controllers will be updated to use Spatie permission middleware:

```php
public function __construct()
{
    $this->middleware('auth:sanctum');
    $this->middleware('permission:view-users')->only(['index', 'show']);
    $this->middleware('permission:create-users')->only('store');
    $this->middleware('permission:update-users')->only('update');
    $this->middleware('permission:update-user-status')->only('updateStatus');
    $this->middleware('permission:delete-users')->only('destroy');
}
```

**Controllers to Update**:
- UserController - user management permissions
- ClientController - client management permissions
- ProductController - product management permissions
- OrderController - order management permissions
- InventoryController - inventory management permissions

**Design Rationale**: Using constructor middleware provides clear, centralized permission definitions for each controller. This approach is more maintainable than route-level middleware for resource controllers.

### 8. Roles and Permissions Seeder

#### RolePermissionSeeder

**Roles to Create**:
1. **superAdmin** - Full system access
2. **supervisor** - Limited access to clients and orders

**Permissions to Create**:

*User Management*:
- view-users, create-users, update-users, update-user-status, delete-users

*Client Management*:
- view-clients, create-clients, update-clients, delete-clients

*Product Management*:
- view-products, create-products, update-products, delete-products, bulk-update-products

*Order Management*:
- view-orders, create-orders, update-orders, delete-orders, approve-orders, reject-orders, complete-orders

*Inventory Management*:
- view-inventory, update-inventory, update-stock, bulk-update-stock

*File Management*:
- upload-files, delete-files

**Permission Assignments**:
- **superAdmin**: All permissions
- **supervisor**: view-clients, create-clients, update-clients, view-orders, create-orders, update-orders, approve-orders, reject-orders, complete-orders

**Migration Strategy for Existing Users**:
```php
// Migrate existing role field to Spatie roles
User::where('role', 'admin')->each(fn($user) => $user->assignRole('superAdmin'));
User::where('role', 'manager')->each(fn($user) => $user->assignRole('supervisor'));
```

**Design Rationale**: Granular permissions allow for flexible role composition in the future. The supervisor role matches the requirement for limited access to clients and orders only.

## Data Models

### User Model Schema

```php
// Database columns
id: bigInteger (primary key)
name: string(255)
email: string(255) unique
email_verified_at: timestamp nullable
password: string(255)
phone: string(20) nullable
address: text nullable
status: tinyInteger default(1)
remember_token: string(100) nullable
created_at: timestamp
updated_at: timestamp

// Relationships (via Spatie)
roles: BelongsToMany<Role>
permissions: BelongsToMany<Permission>

// Casts
email_verified_at: datetime
password: hashed
status: integer
```

### StatusEnum Values

```php
enum StatusEnum: int {
    case INACTIVE = 0;
    case ACTIVE = 1;
}
```

**Future Extensibility**: Additional statuses (SUSPENDED = 2, PENDING = 3) can be added without breaking existing functionality.

## Error Handling

### Validation Errors

**Format** (via ApiResponse helper):
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."],
        "phone": ["The phone format is invalid."]
    }
}
```

**HTTP Status**: 422 Unprocessable Entity

### Authorization Errors

**Format**:
```json
{
    "success": false,
    "message": "Unauthorized. You do not have permission to perform this action.",
    "errors": []
}
```

**HTTP Status**: 403 Forbidden

### Authentication Errors

**Format**:
```json
{
    "success": false,
    "message": "Unauthenticated.",
    "errors": []
}
```

**HTTP Status**: 401 Unauthorized

### Resource Not Found

**Format**:
```json
{
    "success": false,
    "message": "User not found",
    "errors": []
}
```

**HTTP Status**: 404 Not Found

### Self-Deletion Prevention

**Format**:
```json
{
    "success": false,
    "message": "Cannot delete your own account",
    "errors": []
}
```

**HTTP Status**: 400 Bad Request

**Design Rationale**: Using the ApiResponse helper ensures consistent error formatting across the entire API. HTTP status codes follow REST conventions for clear client-side error handling.

## Testing Strategy

### Unit Tests

**User Model Tests**:
- Test status enum casting
- Test phone validation
- Test role assignment via Spatie
- Test permission checking methods
- Test query scopes (active, byStatus)
- Test search scope functionality

**Request Validation Tests**:
- Test StoreUserRequest validation rules
- Test UpdateUserRequest validation rules
- Test UpdateUserStatusRequest validation rules
- Test phone number format validation
- Test role existence validation

### Feature Tests

**User Management API Tests**:
- Test user listing with filters (status, search)
- Test user listing with sorting
- Test user creation with roles
- Test user creation with default status
- Test user update with role changes
- Test status update endpoint
- Test user deletion
- Test self-deletion prevention
- Test unauthorized access (missing permissions)
- Test query builder filtering and sorting

**Permission Tests**:
- Test superAdmin has all permissions
- Test supervisor has limited permissions
- Test permission middleware blocks unauthorized users
- Test role assignment and removal

**Migration Tests**:
- Test existing users migrate to Spatie roles correctly
- Test backward compatibility of isAdmin() and isManager() methods

### Integration Tests

**End-to-End Scenarios**:
- Create user → Assign role → Verify permissions → Update status → Delete
- Search users by name, email, phone
- Filter users by status
- Sort users by various fields
- Test API versioning (v1 routes work correctly)

**Design Rationale**: Focus on testing core functionality and edge cases. Permission tests ensure security requirements are met. Migration tests verify backward compatibility during the transition from simple roles to Spatie permissions.

## Migration and Deployment Strategy

### Phase 1: Database Changes
1. Run migration to add status, phone, address columns
2. Run Spatie permission migrations
3. Run seeder to create roles and permissions

### Phase 2: Code Updates
1. Update User model with new fields and Spatie traits
2. Create versioned directory structure (V1)
3. Move existing controllers to V1 namespace
4. Create Request and Resource classes
5. Update controllers to use ApiResponse helper
6. Implement Query Builder in UserController

### Phase 3: Permission Migration
1. Run seeder to migrate existing role data to Spatie
2. Update all controllers with permission middleware
3. Test permission enforcement

### Phase 4: Route Updates
1. Update routes/api.php to use v1 prefix
2. Update all route references to V1 controllers
3. Test all endpoints

### Rollback Strategy
- Keep migration down() methods to reverse database changes
- Maintain old role field temporarily for rollback capability
- Use feature flags if deploying incrementally

**Design Rationale**: Phased deployment minimizes risk and allows for testing at each stage. Keeping the old role field temporarily provides a safety net during the transition period.

## Security Considerations

1. **Permission Enforcement**: All admin endpoints protected by Spatie permission middleware
2. **Self-Deletion Prevention**: Users cannot delete their own accounts
3. **Password Hashing**: Passwords hashed using Laravel's Hash facade
4. **Input Validation**: All inputs validated via Form Request classes
5. **SQL Injection Prevention**: Using Eloquent ORM and Query Builder
6. **Mass Assignment Protection**: Using $fillable arrays on models
7. **API Authentication**: Laravel Sanctum for token-based auth

## Performance Considerations

1. **Query Optimization**: 
   - Use eager loading for roles and permissions when needed
   - Index status column for filtering
   - Index phone column if searching frequently

2. **Caching Strategy**:
   - Cache role and permission queries (Spatie has built-in caching)
   - Consider caching user lists for read-heavy operations

3. **Pagination**:
   - Default pagination of 15 items per page
   - Configurable via query parameter

**Design Rationale**: Spatie Permission package includes query optimization and caching out of the box. Adding database indexes on frequently queried columns improves performance for large user bases.

## Backward Compatibility

### Maintaining Existing Functionality

1. **Role Methods**: `isAdmin()`, `isManager()`, `hasAdminAccess()` methods maintained but internally use Spatie
2. **API Routes**: Existing routes moved to v1 namespace but remain functionally identical
3. **Response Format**: ApiResponse helper maintains same structure as BaseApiController methods
4. **Authentication**: No changes to Sanctum authentication flow

### Breaking Changes (Minimal)

1. **Role Field**: Direct access to `$user->role` will no longer work (use `$user->roles` collection)
2. **Route Paths**: All routes now prefixed with `/v1` (e.g., `/api/v1/admin/users`)

**Migration Guide for Frontend**:
- Update API base URL to include `/v1`
- Update role checking to use `roles` array instead of `role` string
- Update user objects to include new fields: `status`, `phone`, `address`

**Design Rationale**: Minimizing breaking changes ensures smooth transition for existing integrations. The v1 prefix clearly indicates this is a versioned API, setting expectations for future changes.
