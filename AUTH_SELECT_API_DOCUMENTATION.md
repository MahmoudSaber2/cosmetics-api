# Authentication & Select API Documentation

## Overview
This document provides comprehensive documentation for the Authentication and Select Options API endpoints. These APIs handle admin user authentication and provide dynamic select options for forms and dropdowns.

## Base URLs

### Local Development
- **Base URL**: `http://127.0.0.1:8000`
- **Authentication**: `/api/v1/admin/auth`
- **Select Options**: `/api/v1/selects`

### Testing Server
- **Base URL**: `https://ecv1-api.testingelmo.com`
- **Authentication**: `/api/v1/admin/auth`
- **Select Options**: `/api/v1/selects`

## Authentication
- **Login**: No authentication required
- **Logout**: Requires Sanctum token authentication
- **Select Options**: No authentication required (public endpoint)

## Headers
- `Accept: application/json`
- `Content-Type: application/json` (for POST requests)
- `Accept-Language: ar|en` (optional, for localization)
- `Authorization: Bearer {token}` (for logout endpoint)

---

## Authentication Endpoints

### 1. Admin Login
**POST** `/api/v1/admin/auth/login`

Authenticate admin user and return access token with user profile and permissions.

#### Request Body
```json
{
    "email": "admin@example.com",
    "password": "password123"
}
```

#### Validation Rules
- `email`: Required, valid email format
- `password`: Required string

#### Example Request

**Local Development:**
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

**Testing Server:**
```bash
curl -X POST "https://ecv1-api.testingelmo.com/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

#### Example Response (Success)
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "profile": {
            "name": "أحمد محمد",
            "email": "admin@example.com"
        },
        "tokenDetails": {
            "accessToken": "1|abcdef123456789",
            "expiresIn": "1440"
        },
        "role": "admin",
        "permissions": [
            {
                "permissionName": "view_users",
                "access": true
            },
            {
                "permissionName": "create_users", 
                "access": true
            },
            {
                "permissionName": "edit_users",
                "access": true
            },
            {
                "permissionName": "delete_users",
                "access": false
            },
            {
                "permissionName": "view_products",
                "access": true
            }
        ]
    }
}
```

#### Business Logic
1. **Email Validation**: Check if user exists with provided email
2. **Password Verification**: Verify password using Hash::check()
3. **User Status Check**: Ensure user is active (`isActive()` method)
4. **Admin Access Check**: Verify user has admin access (`hasAdminAccess()` method)
5. **Token Generation**: Create Sanctum access token
6. **Permissions Loading**: Load user permissions via UserPermissionService

#### Error Responses

**Invalid Credentials (401)**
```json
{
    "success": false,
    "message": "بيانات الدخول غير صحيحة",
    "data": []
}
```

**Inactive User (422)**
```json
{
    "success": false,
    "message": "المستخدم غير نشط",
    "data": []
}
```

**Unauthorized Access (401)**
```json
{
    "success": false,
    "message": "غير مصرح",
    "data": []
}
```

**Validation Error (422)**
```json
{
    "success": false,
    "message": "",
    "data": {
        "email": [
            "البريد الإلكتروني مطلوب"
        ],
        "password": [
            "كلمة المرور مطلوبة"
        ]
    }
}
```

---

### 2. Admin Logout
**POST** `/api/v1/admin/auth/logout`

Logout admin user and revoke current access token.

#### Authentication Required
```
Authorization: Bearer {access_token}
```

#### Example Request

**Local Development:**
```bash
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer 1|abcdef123456789" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Testing Server:**
```bash
curl -X POST "https://ecv1-api.testingelmo.com/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer 1|abcdef123456789" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

#### Example Response (Success)
```json
{
    "success": true,
    "message": "تم تسجيل الخروج بنجاح",
    "data": []
}
```

#### Business Logic
1. **Token Validation**: Verify current access token
2. **Token Revocation**: Delete current access token only (not all user tokens)
3. **Session Cleanup**: Clear authentication session

#### Error Responses

**Unauthorized (401)**
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

---

## Select Options Endpoint

### 3. Get Select Options
**GET** `/api/v1/selects`

Retrieve dynamic select options for forms and dropdowns based on requested selects.

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `allSelects` | string | Yes | Comma-separated list of select types |

#### Available Select Types
- `users`: Get all users (id as value, name as label)
- `outerCategories`: Get all active categories (slug as value, name as label)

#### Example Request

**Local Development:**
```bash
curl -X GET "http://localhost:8000/api/v1/selects?allSelects=users,outerCategories" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

**Testing Server:**
```bash
curl -X GET "https://ecv1-api.testingelmo.com/api/v1/selects?allSelects=users,outerCategories" \
  -H "Accept: application/json" \
  -H "Accept-Language: ar"
```

#### Example Response
```json
{
    "success": true,
    "message": "Success",
    "data": [
        {
            "label": "users",
            "options": [
                {
                    "value": 1,
                    "label": "أحمد محمد"
                },
                {
                    "value": 2,
                    "label": "فاطمة علي"
                },
                {
                    "value": 3,
                    "label": "محمد حسن"
                }
            ]
        },
        {
            "label": "outerCategories",
            "options": [
                {
                    "value": "electronics",
                    "label": "إلكترونيات"
                },
                {
                    "value": "clothing",
                    "label": "ملابس"
                },
                {
                    "value": "books",
                    "label": "كتب"
                }
            ]
        }
    ]
}
```

#### Business Logic
1. **Parameter Parsing**: Split comma-separated select types
2. **Service Resolution**: Map select types to appropriate service classes
3. **Data Retrieval**: Call corresponding service methods
4. **Response Formatting**: Format data with label and options structure

---

## Select Service Architecture

### SelectService Class
The main service that orchestrates select option retrieval.

#### Key Methods
- `getSelects(String $selects)`: Main method that processes select requests
- `resolveSelectService($select)`: Maps select types to service classes

#### Service Mapping
```php
$selectServiceMap = [
    'users' => ['getAllPersons', UserSelectService::class],
    'outerCategories' => ['getAllActiveCategories', CategorySelectService::class],
];
```

#### Parameter Support
The service supports parameters in select requests:
- `users=123`: Get users with specific parameter
- `parameters=value`: Special parameter handling
- `claimTextSelect={param}`: Claim text select handling

### UserSelectService Class
Handles user-related select options.

#### Methods
- `getAllPersons()`: Returns all users with id as value and name as label
- `getAllRelatedPersons(?int $workspaceId = null)`: Returns workspace-related users

#### Example Usage
```php
// Get all users
$userService = new UserSelectService();
$users = $userService->getAllPersons();
// Returns: [['value' => 1, 'label' => 'User Name'], ...]
```

### CategorySelectService Class
Handles category-related select options.

#### Methods
- `getAllActiveCategories()`: Returns active categories with slug as value and name as label

#### Example Usage
```php
// Get active categories
$categoryService = new CategorySelectService();
$categories = $categoryService->getAllActiveCategories();
// Returns: [['value' => 'electronics', 'label' => 'Electronics'], ...]
```

---

## Environment Configuration

### Switching Between Servers
The API supports multiple environments. You can switch between them by changing the base URL:

**Local Development:**
- Base URL: `http://localhost:8000/api/v1`
- Full endpoints: `/admin/auth/login`, `/admin/auth/logout`, `/selects`

**Testing Server:**
- Base URL: `https://ecv1-api.testingelmo.com/api/v1`
- Full endpoints: `/admin/auth/login`, `/admin/auth/logout`, `/selects`

### Environment Variables
For frontend applications, use environment variables to manage different server configurations:

```javascript
// .env.local (for local development)
REACT_APP_API_BASE_URL=http://localhost:8000/api/v1

// .env.production (for testing server)
REACT_APP_API_BASE_URL=https://ecv1-api.testingelmo.com/api/v1
```

---

## Frontend Integration Examples

### Authentication Flow
```javascript
// Login function with environment support
const login = async (credentials) => {
    try {
        const baseURL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api/v1';
        const response = await fetch(`${baseURL}/admin/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Accept-Language': 'ar'
            },
            body: JSON.stringify(credentials)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Store token
            localStorage.setItem('access_token', result.data.tokenDetails.accessToken);
            localStorage.setItem('user_profile', JSON.stringify(result.data.profile));
            localStorage.setItem('user_permissions', JSON.stringify(result.data.permissions));
            
            return result.data;
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Login error:', error);
        throw error;
    }
};

// Logout function with environment support
const logout = async () => {
    try {
        const token = localStorage.getItem('access_token');
        const baseURL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api/v1';
        
        const response = await fetch(`${baseURL}/admin/auth/logout`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Accept-Language': 'ar'
            }
        });
        
        const result = await response.json();
        
        // Clear local storage regardless of response
        localStorage.removeItem('access_token');
        localStorage.removeItem('user_profile');
        localStorage.removeItem('user_permissions');
        
        return result;
    } catch (error) {
        console.error('Logout error:', error);
        // Still clear local storage on error
        localStorage.removeItem('access_token');
        localStorage.removeItem('user_profile');
        localStorage.removeItem('user_permissions');
    }
};
```

### Select Options Usage
```javascript
// Fetch select options with environment support
const fetchSelectOptions = async (selectTypes) => {
    try {
        const baseURL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api/v1';
        const response = await fetch(`${baseURL}/selects?allSelects=${selectTypes.join(',')}`, {
            headers: {
                'Accept': 'application/json',
                'Accept-Language': 'ar'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Convert to object for easier access
            const options = {};
            result.data.forEach(item => {
                options[item.label] = item.options;
            });
            return options;
        }
        
        throw new Error(result.message);
    } catch (error) {
        console.error('Error fetching select options:', error);
        throw error;
    }
};

// Usage example
const loadFormOptions = async () => {
    try {
        const options = await fetchSelectOptions(['users', 'outerCategories']);
        
        // Use options in form
        populateSelect('user-select', options.users);
        populateSelect('category-select', options.outerCategories);
    } catch (error) {
        console.error('Error loading form options:', error);
    }
};

// Helper function to populate select element
const populateSelect = (selectId, options) => {
    const selectElement = document.getElementById(selectId);
    selectElement.innerHTML = '<option value="">اختر...</option>';
    
    options.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option.value;
        optionElement.textContent = option.label;
        selectElement.appendChild(optionElement);
    });
};
```

### React Integration Example
```javascript
// Custom hook for authentication
const useAuth = () => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [permissions, setPermissions] = useState([]);

    useEffect(() => {
        const token = localStorage.getItem('access_token');
        const profile = localStorage.getItem('user_profile');
        const userPermissions = localStorage.getItem('user_permissions');
        
        if (token && profile) {
            setUser(JSON.parse(profile));
            setPermissions(JSON.parse(userPermissions || '[]'));
        }
        setLoading(false);
    }, []);

    const login = async (credentials) => {
        setLoading(true);
        try {
            const baseURL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api/v1';
            const response = await fetch(`${baseURL}/admin/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Accept-Language': 'ar'
                },
                body: JSON.stringify(credentials)
            });
            
            const result = await response.json();
            
            if (result.success) {
                localStorage.setItem('access_token', result.data.tokenDetails.accessToken);
                localStorage.setItem('user_profile', JSON.stringify(result.data.profile));
                localStorage.setItem('user_permissions', JSON.stringify(result.data.permissions));
                
                setUser(result.data.profile);
                setPermissions(result.data.permissions);
                
                return result.data;
            } else {
                throw new Error(result.message);
            }
        } finally {
            setLoading(false);
        }
    };

    const logout = async () => {
        try {
            const token = localStorage.getItem('access_token');
            const baseURL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api/v1';
            
            await fetch(`${baseURL}/admin/auth/logout`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
        } finally {
            localStorage.removeItem('access_token');
            localStorage.removeItem('user_profile');
            localStorage.removeItem('user_permissions');
            setUser(null);
            setPermissions([]);
        }
    };

    const hasPermission = (permission) => {
        const permissionObj = permissions.find(p => p.permissionName === permission);
        return permissionObj ? permissionObj.access : false;
    };

    return { user, loading, permissions, login, logout, hasPermission };
};

// Custom hook for select options
const useSelectOptions = (selectTypes) => {
    const [options, setOptions] = useState({});
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchOptions = async () => {
            try {
                setLoading(true);
                const baseURL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api/v1';
                const response = await fetch(`${baseURL}/selects?allSelects=${selectTypes.join(',')}`, {
                    headers: {
                        'Accept': 'application/json',
                        'Accept-Language': 'ar'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const optionsMap = {};
                    result.data.forEach(item => {
                        optionsMap[item.label] = item.options;
                    });
                    setOptions(optionsMap);
                } else {
                    setError(result.message);
                }
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        if (selectTypes.length > 0) {
            fetchOptions();
        }
    }, [selectTypes]);

    return { options, loading, error };
};
```

---

## Security Considerations

### Authentication Security
- **Password Hashing**: Uses Laravel's Hash facade for secure password verification
- **Token Management**: Sanctum tokens with configurable expiration
- **Role-Based Access**: Admin access verification before token generation
- **User Status Check**: Active user verification

### Token Security
- **Single Token Revocation**: Logout only revokes current token, not all user tokens
- **Token Expiration**: Configurable token expiration time
- **Secure Storage**: Tokens should be stored securely on client side

### Select Options Security
- **Data Filtering**: Only active/valid records are returned
- **No Sensitive Data**: Only necessary fields (id, name, slug) are exposed
- **Input Validation**: Select types are validated against allowed options

---

## Error Handling

### Common Error Patterns
```javascript
// Generic error handler
const handleApiError = (error, response) => {
    if (response.status === 401) {
        // Redirect to login
        window.location.href = '/login';
    } else if (response.status === 422) {
        // Handle validation errors
        return error.data; // Validation error details
    } else if (response.status === 500) {
        // Handle server errors
        console.error('Server error:', error.message);
        alert('حدث خطأ في الخادم');
    }
};
```

### Validation Error Handling
```javascript
// Handle login validation errors
const handleLoginErrors = (errors) => {
    const errorMessages = {};
    
    Object.keys(errors).forEach(field => {
        errorMessages[field] = errors[field][0]; // Get first error message
    });
    
    return errorMessages;
};
```

---

## Rate Limiting
Consider implementing rate limiting for authentication endpoints:
- **Login**: 5 attempts per minute per IP
- **Logout**: 10 requests per minute per user
- **Select Options**: 60 requests per minute per IP

---

## Monitoring & Logging
- **Login Attempts**: Log successful and failed login attempts
- **Token Usage**: Monitor token creation and revocation
- **Select Requests**: Track select option usage patterns
- **Error Rates**: Monitor error rates for each endpoint

---

## Testing Examples

### cURL Test Commands
```bash
# Test login
curl -X POST "http://localhost:8000/api/v1/admin/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password123"}'

# Test logout (replace token)
curl -X POST "http://localhost:8000/api/v1/admin/auth/logout" \
  -H "Authorization: Bearer 1|your-token-here"

# Test select options
curl -X GET "http://localhost:8000/api/v1/selects?allSelects=users,outerCategories"
```

### PHPUnit Test Examples
```php
public function test_admin_can_login()
{
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password123')
    ]);
    
    $response = $this->postJson('/api/v1/admin/auth/login', [
        'email' => 'admin@example.com',
        'password' => 'password123'
    ]);
    
    $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'profile',
                    'tokenDetails' => ['accessToken', 'expiresIn'],
                    'role',
                    'permissions'
                ]
            ]);
}

public function test_can_get_select_options()
{
    User::factory()->count(3)->create();
    Category::factory()->count(2)->create(['status' => StatusEnum::ACTIVE]);
    
    $response = $this->getJson('/api/v1/selects?allSelects=users,outerCategories');
    
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
            ]);
}
```
