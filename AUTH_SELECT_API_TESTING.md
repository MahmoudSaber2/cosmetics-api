# Authentication & Select API Testing Guide

## Overview
This document provides comprehensive testing scenarios for the Authentication and Select Options API endpoints using various tools like Postman, cURL, and automated testing frameworks.

## Prerequisites
- Test database with sample data (users, categories)
- API base URLs:

### Local Development
- Authentication: `http://localhost:8000/api/v1/admin/auth`
- Select Options: `http://localhost:8000/api/v1/selects`

### Testing Server
- Authentication: `https://ecv1-api.testingelmo.com/api/v1/admin/auth`
- Select Options: `https://ecv1-api.testingelmo.com/api/v1/selects`

---

## Authentication Testing

### 1. Admin Login Tests

#### Test 1.1: Successful Login (Valid Credentials)
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

**Expected Response:**
- Status: 200
- Contains profile, tokenDetails, role, and permissions
- Access token is valid Sanctum token format
- Permissions array contains user's permissions

**Validation Points:**
- `success: true`
- `data.profile` contains name and email
- `data.tokenDetails.accessToken` is not empty
- `data.role` matches user's role
- `data.permissions` is an array

#### Test 1.2: Invalid Email
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "nonexistent@example.com",
    "password": "password123"
  }'
```

**Expected Response:**
- Status: 401
- Error message about invalid credentials

#### Test 1.3: Invalid Password
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "wrongpassword"
  }'
```

**Expected Response:**
- Status: 401
- Error message about invalid credentials

#### Test 1.4: Missing Required Fields
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com"
  }'
```

**Expected Response:**
- Status: 422
- Validation errors for missing password field

#### Test 1.5: Invalid Email Format
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "invalid-email",
    "password": "password123"
  }'
```

**Expected Response:**
- Status: 422
- Validation error for invalid email format

#### Test 1.6: Inactive User
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "inactive@example.com",
    "password": "password123"
  }'
```

**Expected Response:**
- Status: 422
- Error message about inactive user

#### Test 1.7: Non-Admin User
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

**Expected Response:**
- Status: 401
- Error message about unauthorized access

#### Test 1.8: Empty Request Body
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{}'
```

**Expected Response:**
- Status: 422
- Validation errors for both email and password

---

### 2. Admin Logout Tests

#### Test 2.1: Successful Logout (Valid Token)
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer 1|your-valid-token-here" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Status: 200
- Success message in Arabic
- Empty data array

**Validation Points:**
- `success: true`
- Message indicates successful logout
- Token is revoked (subsequent requests with same token should fail)

#### Test 2.2: Logout Without Token
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 401
- Unauthenticated error message

#### Test 2.3: Logout With Invalid Token
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer invalid-token" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 401
- Unauthenticated error message

#### Test 2.4: Logout With Expired Token
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer 1|expired-token-here" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 401
- Unauthenticated error message

#### Test 2.5: Multiple Logout Attempts (Same Token)
```bash
# First logout (should succeed)
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer 1|your-token-here" \
  -H "Accept: application/json"

# Second logout with same token (should fail)
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer 1|your-token-here" \
  -H "Accept: application/json"
```

**Expected Response:**
- First request: Status 200 (success)
- Second request: Status 401 (token already revoked)

---

## Select Options Testing

### 3. Select Options Tests

#### Test 3.1: Get Single Select Type (Users)
```bash
curl -X GET "http://localhost:8000/api/v1/selects?allSelects=users" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Status: 200
- Single object in data array with label "users"
- Options array contains user objects with value (id) and label (name)

**Validation Points:**
- `success: true`
- `data` is array with one element
- `data[0].label` equals "users"
- `data[0].options` is array of objects
- Each option has `value` (integer) and `label` (string)

#### Test 3.2: Get Single Select Type (Categories)
```bash
curl -X GET "http://localhost:8000/api/v1/selects?allSelects=outerCategories" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Status: 200
- Single object with label "outerCategories"
- Options contain active categories with slug as value and name as label

#### Test 3.3: Get Multiple Select Types
```bash
curl -X GET "http://localhost:8000/api/v1/selects?allSelects=users,outerCategories" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Expected Response:**
- Status: 200
- Two objects in data array
- First object for users, second for categories
- Each with appropriate options structure

#### Test 3.4: Invalid Select Type
```bash
curl -X GET "http://localhost:8000/api/v1/selects?allSelects=invalidType" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Empty data array or filtered results (invalid types ignored)

#### Test 3.5: Mixed Valid and Invalid Select Types
```bash
curl -X GET "http://localhost:8000/api/v1/selects?allSelects=users,invalidType,outerCategories" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Only valid select types returned (users and outerCategories)
- Invalid types silently ignored

#### Test 3.6: Empty Select Parameter
```bash
curl -X GET "http://localhost:8000/api/v1/selects?allSelects=" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Empty data array

#### Test 3.7: Missing Select Parameter
```bash
curl -X GET "http://localhost:8000/api/v1/selects" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200 or 400 (depending on implementation)
- May return error or empty results

#### Test 3.8: Select Types with Spaces
```bash
curl -X GET "http://localhost:8000/api/v1/selects?allSelects=users, outerCategories" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Should handle spaces gracefully
- Return valid select types

#### Test 3.9: Case Sensitivity Test
```bash
curl -X GET "http://localhost:8000/api/v1/selects?allSelects=Users,OUTERCATEGORIES" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- May return empty results if case-sensitive
- Or return results if case-insensitive

#### Test 3.10: Large Number of Select Types
```bash
curl -X GET "http://localhost:8000/api/v1/selects?allSelects=users,outerCategories,users,outerCategories,users" \
  -H "Accept: application/json"
```

**Expected Response:**
- Status: 200
- Should handle duplicates appropriately
- May return unique results only

---

## Integration Testing

### 4. Authentication Flow Tests

#### Test 4.1: Complete Login-Logout Flow
```bash
# Step 1: Login
LOGIN_RESPONSE=$(curl -s -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }')

# Extract token from response
TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.data.tokenDetails.accessToken')

# Step 2: Use token for logout
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Flow:**
- Login succeeds and returns token
- Logout succeeds with the token
- Token is invalidated after logout

#### Test 4.2: Token Validation After Login
```bash
# Login and get token
TOKEN=$(curl -s -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password123"}' | \
  jq -r '.data.tokenDetails.accessToken')

# Test token with protected endpoint (e.g., dashboard)
curl -X GET "http://localhost:8000/api/v1/admin/dashboard" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**Expected Response:**
- Dashboard endpoint should accept the token
- Return successful response

---

## Postman Collection

### Environment Variables

**Local Development:**
```json
{
  "base_url": "http://localhost:8000/api/v1",
  "admin_email": "admin@example.com",
  "admin_password": "password123",
  "access_token": ""
}
```

**Testing Server:**
```json
{
  "base_url": "https://ecv1-api.testingelmo.com/api/v1",
  "admin_email": "admin@example.com",
  "admin_password": "password123",
  "access_token": ""
}
```

### Pre-request Scripts

#### Global Pre-request Script
```javascript
pm.request.headers.add({
    key: 'Accept',
    value: 'application/json'
});

pm.request.headers.add({
    key: 'Accept-Language',
    value: 'ar'
});

// For POST requests
if (pm.request.method === 'POST') {
    pm.request.headers.add({
        key: 'Content-Type',
        value: 'application/json'
    });
}
```

#### Login Pre-request Script
```javascript
// Clear any existing token before login
pm.environment.unset("access_token");
```

#### Logout Pre-request Script
```javascript
// Add authorization header if token exists
const token = pm.environment.get("access_token");
if (token) {
    pm.request.headers.add({
        key: 'Authorization',
        value: `Bearer ${token}`
    });
}
```

### Test Scripts

#### Login Test Script
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response indicates success", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

pm.test("Response contains required fields", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('profile');
    pm.expect(jsonData.data).to.have.property('tokenDetails');
    pm.expect(jsonData.data).to.have.property('role');
    pm.expect(jsonData.data).to.have.property('permissions');
});

pm.test("Token is valid format", function () {
    const jsonData = pm.response.json();
    const token = jsonData.data.tokenDetails.accessToken;
    pm.expect(token).to.be.a('string');
    pm.expect(token).to.not.be.empty;
    pm.expect(token).to.match(/^\d+\|.+$/); // Sanctum token format
});

pm.test("Store access token", function () {
    const jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.tokenDetails) {
        pm.environment.set("access_token", jsonData.data.tokenDetails.accessToken);
    }
});

pm.test("Profile contains user info", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data.profile).to.have.property('name');
    pm.expect(jsonData.data.profile).to.have.property('email');
    pm.expect(jsonData.data.profile.email).to.equal(pm.environment.get("admin_email"));
});

pm.test("Permissions is array of objects", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data.permissions).to.be.an('array');
    
    if (jsonData.data.permissions.length > 0) {
        const permission = jsonData.data.permissions[0];
        pm.expect(permission).to.have.property('permissionName');
        pm.expect(permission).to.have.property('access');
        pm.expect(permission.access).to.be.a('boolean');
    }
});
```

#### Logout Test Script
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response indicates success", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

pm.test("Success message exists", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.message).to.not.be.empty;
});

pm.test("Clear stored token", function () {
    pm.environment.unset("access_token");
});
```

#### Select Options Test Script
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response indicates success", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.success).to.be.true;
});

pm.test("Data is array", function () {
    const jsonData = pm.response.json();
    pm.expect(jsonData.data).to.be.an('array');
});

pm.test("Each select has label and options", function () {
    const jsonData = pm.response.json();
    jsonData.data.forEach(select => {
        pm.expect(select).to.have.property('label');
        pm.expect(select).to.have.property('options');
        pm.expect(select.options).to.be.an('array');
    });
});

pm.test("Options have correct structure", function () {
    const jsonData = pm.response.json();
    jsonData.data.forEach(select => {
        if (select.options.length > 0) {
            const option = select.options[0];
            pm.expect(option).to.have.property('value');
            pm.expect(option).to.have.property('label');
        }
    });
});

pm.test("Response time is acceptable", function () {
    pm.expect(pm.response.responseTime).to.be.below(1000);
});
```

---

## Automated Testing with PHPUnit

### Test Class Structure
```php
<?php

namespace Tests\Feature\Api\V1\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'status' => 1 // active
        ]);
        
        // Assign admin role
        $this->adminUser->assignRole('admin');
    }

    public function test_admin_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'profile' => ['name', 'email'],
                        'tokenDetails' => ['accessToken', 'expiresIn'],
                        'role',
                        'permissions' => [
                            '*' => ['permissionName', 'access']
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true
                ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false
                ]);
    }

    public function test_login_requires_email_and_password()
    {
        $response = $this->postJson('/api/v1/admin/auth/login', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_inactive_user_cannot_login()
    {
        $inactiveUser = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'status' => 0 // inactive
        ]);

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(422);
    }

    public function test_non_admin_user_cannot_login()
    {
        $regularUser = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123')
        ]);
        
        // Don't assign admin role

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_can_logout()
    {
        // First login to get token
        $loginResponse = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123'
        ]);

        $token = $loginResponse->json('data.tokenDetails.accessToken');

        // Then logout
        $response = $this->postJson('/api/v1/admin/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);
    }

    public function test_logout_requires_authentication()
    {
        $response = $this->postJson('/api/v1/admin/auth/logout');

        $response->assertStatus(401);
    }

    public function test_logout_with_invalid_token_fails()
    {
        $response = $this->postJson('/api/v1/admin/auth/logout', [], [
            'Authorization' => 'Bearer invalid-token'
        ]);

        $response->assertStatus(401);
    }
}
```

### Select Options Test Class
```php
<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Enums\StatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SelectOptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        User::factory()->count(3)->create();
        Category::factory()->count(2)->create(['status' => StatusEnum::ACTIVE]);
        Category::factory()->create(['status' => StatusEnum::INACTIVE]); // Should not appear
    }

    public function test_can_get_users_select_options()
    {
        $response = $this->getJson('/api/v1/selects?allSelects=users');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'label',
                            'options' => [
                                '*' => ['value', 'label']
                            ]
                        ]
                    ]
                ])
                ->assertJson([
                    'success' => true
                ]);

        $data = $response->json('data');
        $this->assertEquals('users', $data[0]['label']);
        $this->assertCount(3, $data[0]['options']); // 3 users created
    }

    public function test_can_get_categories_select_options()
    {
        $response = $this->getJson('/api/v1/selects?allSelects=outerCategories');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals('outerCategories', $data[0]['label']);
        $this->assertCount(2, $data[0]['options']); // Only active categories
    }

    public function test_can_get_multiple_select_options()
    {
        $response = $this->getJson('/api/v1/selects?allSelects=users,outerCategories');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data); // Two select types
        
        $labels = array_column($data, 'label');
        $this->assertContains('users', $labels);
        $this->assertContains('outerCategories', $labels);
    }

    public function test_invalid_select_type_is_ignored()
    {
        $response = $this->getJson('/api/v1/selects?allSelects=invalidType');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEmpty($data); // No valid select types
    }

    public function test_mixed_valid_and_invalid_select_types()
    {
        $response = $this->getJson('/api/v1/selects?allSelects=users,invalidType,outerCategories');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data); // Only valid types returned
        
        $labels = array_column($data, 'label');
        $this->assertContains('users', $labels);
        $this->assertContains('outerCategories', $labels);
        $this->assertNotContains('invalidType', $labels);
    }
}
```

---

## Performance Testing

### Load Testing with Apache Bench
```bash
# Test login endpoint
ab -n 100 -c 10 -p login_data.json -T application/json \
   http://localhost:8000/api/v1/admin/auth/login

# Test select options endpoint
ab -n 200 -c 20 \
   "http://localhost:8000/api/v1/selects?allSelects=users,outerCategories"
```

### login_data.json
```json
{
    "email": "admin@example.com",
    "password": "password123"
}
```

### Expected Performance Metrics
- **Login**: < 300ms average response time
- **Logout**: < 100ms average response time
- **Select Options**: < 200ms average response time

---

## Security Testing

### Authentication Security Tests
```bash
# Test SQL injection in login
curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com'\'' OR 1=1 --",
    "password": "anything"
  }'

# Test XSS in login
curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "<script>alert(\"XSS\")</script>@example.com",
    "password": "password123"
  }'

# Test brute force protection
for i in {1..20}; do
  curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
    -H "Content-Type: application/json" \
    -d '{"email":"admin@example.com","password":"wrong'$i'"}' &
done
```

### Token Security Tests
```bash
# Test token manipulation
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer 1|manipulated-token-here"

# Test token reuse after logout
TOKEN="1|your-token-here"

# First logout
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer $TOKEN"

# Try to use same token again
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer $TOKEN"
```

---

## Test Data Setup

### Sample Data Creation
```sql
-- Create test admin user
INSERT INTO users (name, email, password, status, created_at, updated_at) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW());

-- Create test regular user
INSERT INTO users (name, email, password, status, created_at, updated_at) VALUES
('Regular User', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW());

-- Create test inactive user
INSERT INTO users (name, email, password, status, created_at, updated_at) VALUES
('Inactive User', 'inactive@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, NOW(), NOW());

-- Create test categories
INSERT INTO categories (name, slug, status, created_at, updated_at) VALUES
('Electronics', 'electronics', 1, NOW(), NOW()),
('Clothing', 'clothing', 1, NOW(), NOW()),
('Inactive Category', 'inactive', 0, NOW(), NOW());
```

---

## Troubleshooting

### Common Issues
1. **401 Unauthorized**: Check token format and validity
2. **422 Validation Error**: Verify request body structure and required fields
3. **Empty Select Options**: Check if test data exists and is active
4. **Token Not Working**: Ensure token is properly formatted and not expired

### Debug Commands
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check user roles and permissions
php artisan tinker
>>> $user = User::find(1);
>>> $user->getRoleNames();
>>> $user->getAllPermissions();

# Clear application cache
php artisan cache:clear
php artisan config:clear
```

---

## Test Checklist

### Authentication Tests
- [ ] Successful login with valid credentials
- [ ] Failed login with invalid email
- [ ] Failed login with invalid password
- [ ] Validation errors for missing fields
- [ ] Inactive user cannot login
- [ ] Non-admin user cannot login
- [ ] Successful logout with valid token
- [ ] Failed logout without token
- [ ] Failed logout with invalid token
- [ ] Token invalidation after logout

### Select Options Tests
- [ ] Get users select options
- [ ] Get categories select options
- [ ] Get multiple select types
- [ ] Invalid select types are ignored
- [ ] Mixed valid/invalid select types
- [ ] Empty select parameter handling
- [ ] Response structure validation
- [ ] Performance under load

### Security Tests
- [ ] SQL injection prevention
- [ ] XSS prevention
- [ ] Token manipulation prevention
- [ ] Brute force protection
- [ ] Rate limiting
- [ ] Input validation

### Performance Tests
- [ ] Response time under normal load
- [ ] Response time under heavy load
- [ ] Memory usage
- [ ] Concurrent request handling
