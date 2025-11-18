# Implementation Plan

- [x] 1. Database schema updates and Spatie package setup
  - Create migration to add status, phone, and address columns to users table
  - Publish and run Spatie Permission migrations to create roles and permissions tables
  - _Requirements: 1.1, 1.2, 2.1, 2.2, 2.3, 3.1_

- [x] 2. Create roles, permissions, and seeder
  - [x] 2.1 Create RolePermissionSeeder with roles and permissions
    - Define superAdmin and supervisor roles in seeder
    - Define all granular permissions for users, clients, products, orders, and inventory
    - Assign all permissions to superAdmin role
    - Assign limited permissions (clients, orders) to supervisor role
    - _Requirements: 3.6, 3.7_

  - [x] 2.2 Remove role field from users table and update seeders
    - Create migration to remove role column from users table if it exists
    - Update AdminUserSeeder to remove role field and assign Spatie roles instead
    - Ensure app follows Spatie permissions architecture (no role column in users)
    - _Requirements: 3.2, 3.3_

- [x] 3. Update User model with new fields and Spatie integration
  - Add HasRoles trait from Spatie Permission
  - Add status, phone, address to fillable array
  - Add status cast to StatusEnum
  - Update isAdmin() to use hasRole('superAdmin')
  - Update isManager() to use hasRole('supervisor')
  - Update hasAdminAccess() to check Spatie roles
  - Add isActive() method to check status
  - Add scopeActive() and scopeByStatus() query scopes
  - Add scopeSearch() for multi-field searching (name, email, phone)
  - _Requirements: 1.1, 1.4, 2.1, 2.2, 2.5, 3.1, 3.2, 3.3, 4.2, 4.3, 4.4_

- [x] 4. Create API versioning structure
  - [x] 4.1 Create V1 directory structure
    - Create app/Http/Controllers/Api/V1 directory with Admin and Public subdirectories
    - Create app/Http/Requests/V1 directory
    - Create app/Http/Resources/V1 directory
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

  - [x] 4.2 Move existing controllers to V1 namespace
    - Move UserController to V1/Admin namespace and update namespace
    - Move ClientController to V1/Admin namespace and update namespace
    - Move ProductController to V1/Admin namespace and update namespace
    - Move OrderController to V1/Admin namespace and update namespace
    - Move InventoryController to V1/Admin namespace and update namespace
    - Move Public controllers to V1/Public namespace
    - Move AuthController, FileUploadController, LocaleController to V1 namespace
    - _Requirements: 6.4, 6.5_

- [x] 5. Create Request validation classes for user management
  - [x] 5.1 Create StoreUserRequest
    - Add validation rules for name, email, password, phone, address, status, roles
    - Add phone regex validation pattern
    - Add authorization check for create-users permission
    - _Requirements: 1.2, 2.1, 2.2, 2.4, 3.4_

  - [x] 5.2 Create UpdateUserRequest
    - Add validation rules for name, email, password, phone, address, roles
    - Add phone regex validation pattern
    - Add unique email validation with ignore current user
    - Add authorization check for update-users permission
    - _Requirements: 1.3, 2.1, 2.2, 2.4, 3.4_

  - [x] 5.3 Create UpdateUserStatusRequest
    - Add validation rule for status field (in:0,1)
    - Add authorization check for update-user-status permission
    - _Requirements: 1.3, 3.4_

- [x] 6. Create Resource classes for user responses
  - [x] 6.1 Create UserResource
    - Transform user data including id, name, email, phone, address, status
    - Add status_label field for human-readable status
    - Include roles and permissions from Spatie
    - Include created_at and updated_at timestamps
    - _Requirements: 1.4, 2.5, 5.1, 5.2_

  - [x] 6.2 Create UserCollection
    - Extend ResourceCollection for paginated user lists
    - Include pagination metadata
    - _Requirements: 5.1, 5.2_

- [x] 7. Update UserController with new functionality
  - [x] 7.1 Update controller namespace and add permission middleware
    - Update namespace to App\Http\Controllers\Api\V1\Admin
    - Add auth:sanctum middleware in constructor
    - Add permission middleware for each action (view-users, create-users, update-users, delete-users)
    - _Requirements: 3.4, 3.8_

  - [x] 7.2 Refactor index method with Spatie Query Builder
    - Replace manual query with QueryBuilder::for(User::class)
    - Add allowedFilters for status (exact) and search (scope)
    - Add allowedSorts for name, email, created_at, status
    - Add allowedIncludes for roles and permissions
    - Add pagination with configurable per_page
    - Replace sendResponse with ApiResponse::success
    - Return UserCollection resource
    - _Requirements: 1.5, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 5.1_

  - [x] 7.3 Refactor store method
    - Replace manual validation with StoreUserRequest
    - Set default status to 1 (ACTIVE) if not provided
    - Assign roles using Spatie's assignRole method
    - Replace sendResponse with ApiResponse::success
    - Return UserResource with 201 status
    - _Requirements: 1.2, 3.5, 5.1, 5.2_

  - [x] 7.4 Refactor show method
    - Replace sendResponse with ApiResponse::success
    - Return UserResource
    - _Requirements: 5.1, 5.2_

  - [x] 7.5 Refactor update method
    - Replace manual validation with UpdateUserRequest
    - Sync roles if provided using Spatie's syncRoles method
    - Replace sendResponse with ApiResponse::success
    - Return UserResource
    - _Requirements: 1.3, 3.5, 5.1, 5.2_

  - [x] 7.6 Add updateStatus method
    - Validate with UpdateUserStatusRequest
    - Update only the status field
    - Add permission middleware for update-user-status
    - Replace sendResponse with ApiResponse::success
    - Return UserResource
    - _Requirements: 1.3, 5.1, 5.2_

  - [x] 7.7 Refactor destroy method
    - Keep self-deletion prevention logic
    - Replace sendError with ApiResponse::error
    - Replace sendResponse with ApiResponse::success
    - _Requirements: 5.1, 5.2_

- [x] 8. Update routes with v1 versioning
  - Update routes/api.php to use v1 prefix for all routes
  - Update all controller references to V1 namespaced controllers
  - Add PATCH route for /admin/users/{user}/status endpoint
  - _Requirements: 6.1, 6.5_

- [x] 9. Add permission middleware to other admin controllers
  - [x] 9.1 Update ClientController with permission middleware
    - Add auth:sanctum and permission middleware in constructor
    - Define permissions: view-clients, create-clients, update-clients, delete-clients
    - _Requirements: 3.8_

  - [x] 9.2 Update ProductController with permission middleware
    - Add auth:sanctum and permission middleware in constructor
    - Define permissions: view-products, create-products, update-products, delete-products, bulk-update-products
    - _Requirements: 3.8_

  - [x] 9.3 Update OrderController with permission middleware
    - Add auth:sanctum and permission middleware in constructor
    - Define permissions: view-orders, create-orders, update-orders, delete-orders, approve-orders, reject-orders, complete-orders
    - _Requirements: 3.8_

  - [x] 9.4 Update InventoryController with permission middleware
    - Add auth:sanctum and permission middleware in constructor
    - Define permissions: view-inventory, update-inventory, update-stock, bulk-update-stock
    - _Requirements: 3.8_

  - [x] 9.5 Update FileUploadController with permission middleware
    - Add auth:sanctum and permission middleware in constructor
    - Define permissions: upload-files, delete-files
    - _Requirements: 3.8_

- [x] 10. Write tests for user management enhancements
  - [ ]* 10.1 Write User model tests
    - Test status enum casting
    - Test role assignment and checking methods
    - Test query scopes (active, byStatus, search)
    - _Requirements: 1.1, 3.2, 4.2, 4.3, 4.4_

  - [ ] 10.2 Write Request validation tests
    - Test StoreUserRequest validation rules
    - Test UpdateUserRequest validation rules
    - Test UpdateUserStatusRequest validation rules
    - Test phone number format validation
    - _Requirements: 1.2, 1.3, 2.4_

  - [ ]* 10.3 Write UserController feature tests
    - Test user listing with status filter
    - Test user listing with search
    - Test user listing with sorting
    - Test user creation with roles
    - Test user update with role changes
    - Test status update endpoint
    - Test user deletion and self-deletion prevention
    - Test unauthorized access (missing permissions)
    - _Requirements: 1.2, 1.3, 1.5, 3.4, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_

  - [ ]* 10.4 Write permission and role tests
    - Test superAdmin has all permissions
    - Test supervisor has limited permissions
    - Test permission middleware blocks unauthorized users
    - _Requirements: 3.4, 3.6, 3.7_
