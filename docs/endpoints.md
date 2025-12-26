# API Endpoints Documentation

This document lists all available API endpoints organized by user role and functionality.

## Table of Contents

-   [Shared Endpoints](#shared-endpoints)
-   [Admin Endpoints](#admin-endpoints)
-   [Owner Endpoints](#owner-endpoints)
-   [Tenant Endpoints](#tenant-endpoints)

---

## Shared Endpoints

Endpoints accessible by all authenticated users (Admin, Owner, Tenant).

### Authentication Endpoints

#### 1. Send OTP

**Endpoint:** `POST /api/auth/send-otp`  
**Access:** Public (no authentication required)  
**Description:** Sends an OTP code to the provided mobile number for registration or login verification.

**Request:**

```json
{
    "mobile_number": "0991877688"
}
```

**Response (Success):**

```json
{
    "success": true,
    "message": "OTP sent successfully",
    "data": {
        "otp_id": "123",
        "otp_code": "123456" // Only in development/testing
    }
}
```

**Response (Error):**

```json
{
    "success": false,
    "message": "Error message here"
}
```

**Scenario:** Used during registration flow. User enters mobile number, system sends OTP via SMS. The OTP code is returned in development mode for testing purposes.

---

#### 2. Verify OTP

**Endpoint:** `POST /api/auth/verify-otp`  
**Access:** Public (no authentication required)  
**Description:** Verifies the OTP code sent to the mobile number.

**Request:**

```json
{
    "mobile_number": "0991877688",
    "otp_code": "123456"
}
```

**Response (Success):**

```json
{
    "success": true,
    "message": "OTP verified successfully",
    "data": {
        "otp_id": "123"
    }
}
```

**Response (Error):**

```json
{
    "success": false,
    "message": "Invalid or expired OTP code"
}
```

**Scenario:** User enters the OTP code received via SMS. System verifies it and marks the OTP as verified. This verified OTP is then required during registration.

---

#### 3. Register

**Endpoint:** `POST /api/auth/register`  
**Access:** Public (no authentication required)  
**Description:** Registers a new user (tenant or owner). User status is set to 'pending' and requires admin approval before login.

**Request (multipart/form-data):**

```
mobile_number: "0991877688"
otp_code: "123456"
password: "password123"
password_confirmation: "password123"
first_name: "John"
last_name: "Doe"
date_of_birth: "1990-01-15"
role: "tenant"  // or "owner"
personal_photo: [file]
id_photo: [file]
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Registration successful. Please wait for admin approval.",
    "data": {
        "user": {
            "id": 1,
            "mobile_number": "+963991877688",
            "first_name": "John",
            "last_name": "Doe",
            "role": "tenant",
            "status": "pending"
        }
    }
}
```

**Response (Error):**

```json
{
    "success": false,
    "message": "Invalid or expired OTP code"
}
```

**Scenario:** After OTP verification, user completes registration by providing personal information, photos, and choosing role. User cannot login until admin approves the account.

---

#### 4. Login

**Endpoint:** `POST /api/auth/login`  
**Access:** Public (no authentication required)  
**Description:** Authenticates user and returns JWT token. Only approved users can login.

**Request:**

```json
{
    "mobile_number": "0991877688",
    "password": "password123"
}
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 1440,
        "user": {
            "id": 1,
            "mobile_number": "+963991877688",
            "first_name": "John",
            "last_name": "Doe",
            "personal_photo": "/storage/users/photos/...",
            "role": "tenant",
            "status": "approved",
            "language_preference": "en",
            "balance": 0.0
        }
    }
}
```

**Response (Error - Invalid Credentials):**

```json
{
    "success": false,
    "message": "Invalid mobile number or password"
}
```

**Response (Error - Not Approved):**

```json
{
    "success": false,
    "message": "Your account is pending approval. Please wait for admin approval."
}
```

**Scenario:** User enters mobile number and password. System validates credentials and user approval status. Returns JWT token for authenticated requests.

---

#### 5. Logout

**Endpoint:** `POST /api/auth/logout`  
**Access:** Protected (requires authentication + approved status + verified OTP)  
**Description:** Invalidates the current JWT token.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Logout successful"
}
```

**Scenario:** User logs out, token is invalidated and cannot be used for further requests.

---

#### 6. Get Current User

**Endpoint:** `GET /api/auth/me`  
**Access:** Protected (requires authentication + approved status + verified OTP)  
**Description:** Returns the currently authenticated user's information.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Response (Success):**

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "mobile_number": "+963991877688",
            "first_name": "John",
            "last_name": "Doe",
            "personal_photo": "/storage/users/photos/...",
            "date_of_birth": "1990-01-15",
            "role": "tenant",
            "status": "approved",
            "language_preference": "en",
            "balance": 0.0
        }
    }
}
```

**Scenario:** Used to get current user information, typically called on app startup to verify authentication status.

---

### Profile Management Endpoints

#### 7. Get Profile

**Endpoint:** `GET /api/profile`  
**Access:** Protected (requires authentication + approved status + verified OTP)  
**Description:** Returns user profile with role-specific statistics.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Response (Success - Owner):**

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "mobile_number": "+963991877688",
            "first_name": "John",
            "last_name": "Doe",
            "personal_photo": "/storage/users/photos/...",
            "date_of_birth": "1990-01-15",
            "role": "owner",
            "language_preference": "en",
            "status": "approved",
            "created_at": "2024-01-15T10:00:00Z"
        },
        "statistics": {
            "total_apartments": 5,
            "total_bookings": 12,
            "average_rating": 4.5,
            "reviews_received": 8
        },
        "balance": 1500.0
    }
}
```

**Response (Success - Tenant):**

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 2,
            "mobile_number": "+963991234567",
            "first_name": "Jane",
            "last_name": "Smith",
            "personal_photo": "/storage/users/photos/...",
            "date_of_birth": "1995-05-20",
            "role": "tenant",
            "language_preference": "en",
            "status": "approved",
            "created_at": "2024-01-20T14:30:00Z"
        },
        "statistics": {
            "total_bookings": 3,
            "average_rating": 4.0,
            "reviews_given": 2,
            "favorites_count": 5
        },
        "balance": 500.0
    }
}
```

**Scenario:** Used to display user profile screen with personalized statistics based on user role (owner or tenant).

---

#### 8. Update Profile

**Endpoint:** `PUT /api/profile`  
**Access:** Protected (requires authentication + approved status + verified OTP)  
**Description:** Updates user profile information.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request (multipart/form-data):**

```
first_name: "John"  // optional
last_name: "Doe"    // optional
personal_photo: [file]  // optional
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "user": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "personal_photo": "/storage/users/photos/..."
        }
    }
}
```

**Scenario:** User edits their profile information. Old photo is deleted when new photo is uploaded.

---

#### 9. Upload Profile Photo

**Endpoint:** `POST /api/profile/upload-photo`  
**Access:** Protected (requires authentication + approved status + verified OTP)  
**Description:** Uploads a new profile photo, replacing the existing one.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request (multipart/form-data):**

```
photo: [file]  // required, max 5MB, jpeg/jpg/png
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Photo uploaded successfully",
    "data": {
        "file_path": "/storage/users/photos/...",
        "personal_photo": "/storage/users/photos/..."
    }
}
```

**Scenario:** User wants to change their profile picture. Old photo is automatically deleted.

---

#### 10. Update Language Preference

**Endpoint:** `PUT /api/profile/language`  
**Access:** Protected (requires authentication + approved status + verified OTP)  
**Description:** Updates user's language preference (en/ar).

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**

```json
{
    "language_preference": "ar"
}
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Language updated successfully",
    "data": {
        "language_preference": "ar"
    }
}
```

**Scenario:** User changes app language preference. This affects the language of API responses and app UI.

---

#### 11. View User Profile

**Endpoint:** `GET /api/users/{userId}`  
**Access:** Protected (requires authentication + approved status + verified OTP)  
**Description:** View another user's public profile information. Available to all authenticated users (Admin, Owner, Tenant).

**Request Headers:**

```
Authorization: Bearer {token}
```

**URL Parameters:**

-   `userId` (integer, required) - The ID of the user whose profile to view

**Response (Success - Owner Profile):**

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "personal_photo": "/storage/users/photos/...",
            "role": "owner",
            "status": "approved",
            "created_at": "2024-01-15T10:00:00Z"
        },
        "statistics": {
            "total_apartments": 5,
            "total_bookings": 12,
            "average_rating": 4.5,
            "reviews_received": 8
        }
    }
}
```

**Response (Success - Tenant Profile):**

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 2,
            "first_name": "Jane",
            "last_name": "Smith",
            "personal_photo": "/storage/users/photos/...",
            "role": "tenant",
            "status": "approved",
            "created_at": "2024-01-20T14:30:00Z"
        },
        "statistics": {
            "total_bookings": 3,
            "average_rating": 4.0,
            "reviews_given": 2
        }
    }
}
```

**Response (Error - User Not Found):**

```json
{
    "success": false,
    "message": "User not found"
}
```

**Note:** This endpoint returns only public information. Sensitive data like `mobile_number`, `date_of_birth`, `balance`, and `language_preference` are not included in the response.

**Scenario:** Used when viewing another user's profile, such as when:

-   A tenant views an owner's profile from an apartment listing
-   An owner views a tenant's profile from a booking
-   An admin views any user's profile
-   Users view profiles from messaging conversations

---

## Admin Endpoints

Endpoints accessible only by users with the `admin` role.

### User Management Endpoints

#### 11. List All Users

**Endpoint:** `GET /api/admin/users`  
**Access:** Protected (requires authentication + admin role + approved status + verified OTP)  
**Description:** Lists all users in the system with filtering, searching, and pagination options.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Query Parameters:**

-   `status` (optional): Filter by status - `all`, `approved`, `pending`, `rejected` (default: `all`)
-   `role` (optional): Filter by role - `all`, `tenant`, `owner` (default: `all`)
-   `search` (optional): Search by name or mobile number
-   `sort` (optional): Sort order - `newest`, `oldest`, `name_asc`, `name_desc` (default: `newest`)
-   `per_page` (optional): Items per page (default: 50)
-   `page` (optional): Page number (default: 1)

**Request Example:**

```
GET /api/admin/users?status=pending&role=owner&search=john&sort=newest&per_page=20&page=1
```

**Response (Success):**

```json
{
    "success": true,
    "data": {
        "users": [
            {
                "id": 1,
                "mobile_number": "+963991877688",
                "first_name": "John",
                "last_name": "Doe",
                "personal_photo": "/storage/users/photos/...",
                "role": "owner",
                "status": "pending",
                "created_at": "2024-01-15T10:00:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 3,
            "per_page": 50,
            "total": 123
        }
    }
}
```

**Scenario:** Admin views the user management screen to see all registered users. Can filter by status (pending approvals), role, and search for specific users.

---

#### 12. Approve User Registration

**Endpoint:** `PUT /api/admin/registrations/{user_id}/approve`  
**Access:** Protected (requires authentication + admin role + approved status + verified OTP)  
**Description:** Approves a pending user registration, allowing them to login.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**URL Parameters:**

-   `user_id` (required): The ID of the user to approve

**Request Example:**

```
PUT /api/admin/registrations/1/approve
```

**Response (Success):**

```json
{
    "success": true,
    "message": "User approved successfully",
    "data": {
        "user": {
            "id": 1,
            "mobile_number": "+963991877688",
            "first_name": "John",
            "last_name": "Doe",
            "status": "approved"
        }
    }
}
```

**Response (Error - User Not Found):**

```json
{
    "success": false,
    "message": "User not found"
}
```

**Response (Error - Already Approved):**

```json
{
    "success": false,
    "message": "User is already approved"
}
```

**Scenario:** Admin reviews a pending registration request, verifies the user's information and ID photo, then approves the account. User can now login to the application.

---

## Owner Endpoints

Endpoints accessible only by users with the `owner` role.

### Apartment Management Endpoints

#### 13. List Owner's Apartments

**Endpoint:** `GET /api/owner/apartments`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Lists all apartments owned by the authenticated owner with statistics.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Query Parameters:**

-   `status` (optional): Filter by status - `active`, `inactive` (default: all)
-   `search` (optional): Search by address, governorate, or city
-   `sort_by` (optional): Sort field (default: `created_at`)
-   `sort_order` (optional): Sort direction - `asc`, `desc` (default: `desc`)
-   `per_page` (optional): Items per page (default: 10)
-   `page` (optional): Page number (default: 1)

**Request Example:**

```
GET /api/owner/apartments?status=active&search=damascus&sort_by=nightly_price&sort_order=asc
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Apartments retrieved successfully",
    "data": {
        "statistics": {
            "total_apartments": 5,
            "active_apartments": 4,
            "pending_bookings": 2
        },
        "apartments": [
            {
                "id": 1,
                "photos": ["/storage/apartments/photos/..."],
                "address": "123 Main Street, Damascus",
                "governorate": "Damascus",
                "city": "Damascus",
                "nightly_price": "50.00",
                "monthly_price": "1200.00",
                "status": "active",
                "bedrooms": 2,
                "bathrooms": 1,
                "living_rooms": 1,
                "size": "80.50",
                "average_rating": 4.5,
                "rating_count": 8,
                "pending_requests_count": 1,
                "total_bookings_count": 12,
                "created_at": "2024-01-15T10:00:00Z"
            }
        ]
    }
}
```

**Scenario:** Owner views their apartment dashboard to see all their listed apartments, statistics, and booking requests.

---

#### 14. Create Apartment

**Endpoint:** `POST /api/owner/apartments`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Creates a new apartment listing.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**

```json
{
    "photos": ["apartments/photos/photo1.png", "apartments/photos/photo2.png"],
    "governorate": "Damascus",
    "governorate_ar": "دمشق",
    "city": "Damascus",
    "city_ar": "دمشق",
    "address": "123 Main Street, Damascus",
    "address_ar": "123 الشارع الرئيسي، دمشق",
    "nightly_price": 50.0,
    "monthly_price": 1200.0,
    "bedrooms": 2,
    "bathrooms": 1,
    "living_rooms": 1,
    "size": 80.5,
    "description": "Beautiful apartment in the heart of Damascus with modern amenities and great location.",
    "description_ar": "شقة جميلة في قلب دمشق مع وسائل الراحة الحديثة وموقع رائع.",
    "amenities": ["WiFi", "Parking", "A/C", "Heating"],
    "status": "active"
}
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Apartment published successfully",
    "data": {
        "apartment_id": 1
    }
}
```

**Response (Error - Validation):**

```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "photos": ["At least one apartment photo is required."],
        "address": ["Address must be at least 10 characters."]
    }
}
```

**Scenario:** Owner creates a new apartment listing with all details, photos (uploaded separately first), and amenities. Apartment is immediately available for booking if status is 'active'.

---

#### 15. Get Apartment Details

**Endpoint:** `GET /api/owner/apartments/{apartment_id}`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Retrieves detailed information about a specific apartment owned by the authenticated owner.

**Request Headers:**

```
Authorization: Bearer {token}
```

**URL Parameters:**

-   `apartment_id` (required): The ID of the apartment

**Request Example:**

```
GET /api/owner/apartments/1
```

**Response (Success):**

```json
{
    "success": true,
    "data": {
        "apartment": {
            "id": 1,
            "photos": ["/storage/apartments/photos/..."],
            "governorate": "Damascus",
            "governorate_ar": "دمشق",
            "city": "Damascus",
            "city_ar": "دمشق",
            "address": "123 Main Street, Damascus",
            "address_ar": "123 الشارع الرئيسي، دمشق",
            "nightly_price": "50.00",
            "monthly_price": "1200.00",
            "bedrooms": 2,
            "bathrooms": 1,
            "living_rooms": 1,
            "size": "80.50",
            "description": "Beautiful apartment in the heart of Damascus...",
            "description_ar": "شقة جميلة في قلب دمشق...",
            "amenities": ["WiFi", "Parking", "A/C", "Heating"],
            "status": "active",
            "average_rating": 4.5,
            "rating_count": 8,
            "total_bookings": 12,
            "pending_bookings": 1,
            "created_at": "2024-01-15T10:00:00Z",
            "updated_at": "2024-01-15T10:00:00Z"
        }
    }
}
```

**Response (Error - Not Found):**

```json
{
    "success": false,
    "message": "Apartment not found"
}
```

**Scenario:** Owner views detailed information about one of their apartments, including booking statistics and ratings.

---

#### 16. Update Apartment

**Endpoint:** `PUT /api/owner/apartments/{apartment_id}`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Updates an existing apartment listing. Cannot set to inactive if there are active/pending bookings.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**URL Parameters:**

-   `apartment_id` (required): The ID of the apartment to update

**Request (all fields optional):**

```json
{
    "photos": ["apartments/photos/new_photo.png"],
    "governorate": "Damascus",
    "governorate_ar": "دمشق",
    "city": "Damascus",
    "city_ar": "دمشق",
    "address": "456 New Street, Damascus",
    "address_ar": "456 شارع جديد، دمشق",
    "nightly_price": 60.0,
    "monthly_price": 1500.0,
    "bedrooms": 3,
    "bathrooms": 2,
    "living_rooms": 1,
    "size": 100.0,
    "description": "Updated description...",
    "description_ar": "وصف محدث...",
    "amenities": ["WiFi", "Parking", "A/C", "Heating", "Pool"],
    "status": "active"
}
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Apartment updated successfully",
    "data": {
        "apartment_id": 1
    }
}
```

**Response (Error - Active Bookings):**

```json
{
    "success": false,
    "message": "Cannot set apartment to inactive. There are active or pending bookings."
}
```

**Scenario:** Owner edits apartment details, prices, or photos. Old photos are deleted when new photos are provided. Cannot deactivate apartment with active bookings.

---

#### 17. Delete Apartment

**Endpoint:** `DELETE /api/owner/apartments/{apartment_id}`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Deletes an apartment. Cannot delete if there are active or pending bookings.

**Request Headers:**

```
Authorization: Bearer {token}
```

**URL Parameters:**

-   `apartment_id` (required): The ID of the apartment to delete

**Request Example:**

```
DELETE /api/owner/apartments/1
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Apartment deleted successfully"
}
```

**Response (Error - Active Bookings):**

```json
{
    "success": false,
    "message": "Cannot delete apartment. There are active or pending bookings."
}
```

**Response (Error - Not Found):**

```json
{
    "success": false,
    "message": "Apartment not found"
}
```

**Scenario:** Owner wants to remove an apartment listing. System checks for active bookings first. All photos are deleted from storage.

---

#### 18. Upload Apartment Photo

**Endpoint:** `POST /api/owner/apartments/upload-photo`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Uploads a photo for an apartment. Returns the file path to be used in the photos array when creating/updating an apartment.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request (multipart/form-data):**

```
photo: [file]  // required, max 5MB, jpeg/jpg/png
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Photo uploaded successfully",
    "data": {
        "file_path": "apartments/photos/LMbC1RccMq3LqQXf6S5MBYzvA4Upgw0u2pvFwQTZ.png"
    }
}
```

**Scenario:** Owner uploads photos before creating or updating an apartment. The returned file path is then included in the photos array when creating/updating the apartment listing.

---

## Tenant Endpoints

Endpoints accessible only by users with the `tenant` role.

---

#### 19. List All Apartments (Tenant View)

**Endpoint:** `GET /api/apartments`  
**Access:** Protected (requires authentication + tenant role + approved status + verified OTP)  
**Description:** Lists all active apartments available for booking. Includes filtering, searching, and sorting capabilities.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Query Parameters (all optional):**

-   `governorate` (string): Filter by governorate name (matches English or Arabic)
-   `city` (string): Filter by city name (matches English or Arabic)
-   `min_nightly_price` (number): Minimum nightly price
-   `max_nightly_price` (number): Maximum nightly price
-   `min_monthly_price` (number): Minimum monthly price
-   `max_monthly_price` (number): Maximum monthly price
-   `bedrooms` (number): Exact number of bedrooms
-   `min_bedrooms` (number): Minimum number of bedrooms
-   `bathrooms` (number): Exact number of bathrooms
-   `min_bathrooms` (number): Minimum number of bathrooms
-   `amenities` (array): Array of amenity keys to filter by (all must match)
-   `search` (string): Search in address, city, or governorate (English or Arabic)
-   `sort_by` (string): Sort field - `price_low`, `price_high`, `rating`, `newest`, `oldest`, or any column name
-   `sort_order` (string): `asc` or `desc` (default: `desc`)
-   `per_page` (number): Number of results per page (default: 20)
-   `page` (number): Page number (default: 1)

**Request Example:**

```
GET /api/apartments?governorate=Damascus&min_nightly_price=50&max_nightly_price=200&bedrooms=2&sort_by=price_low&per_page=10
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Apartments retrieved successfully",
    "data": {
        "apartments": [
            {
                "id": 1,
                "photos": ["/storage/apartments/photos/..."],
                "governorate": "Damascus",
                "governorate_ar": "دمشق",
                "city": "Damascus",
                "city_ar": "دمشق",
                "address": "123 Main Street",
                "address_ar": "123 الشارع الرئيسي",
                "nightly_price": "50.00",
                "monthly_price": "1200.00",
                "bedrooms": 2,
                "bathrooms": 1,
                "living_rooms": 1,
                "size": "80.50",
                "amenities": ["WiFi", "Parking", "A/C"],
                "average_rating": 4.5,
                "rating_count": 8,
                "owner": {
                    "id": 3,
                    "first_name": "Owner",
                    "last_name": "Name",
                    "personal_photo": "/storage/users/photos/..."
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 20,
            "total": 95
        }
    }
}
```

**Response (Error - Unauthorized):**

```json
{
    "success": false,
    "message": "Unauthorized. Tenant access required."
}
```

**Scenario:** Tenant browses available apartments to find a suitable rental. Can filter by location, price, bedrooms, bathrooms, amenities, and search by text. Results are paginated and sortable.

---

#### 20. Get Apartment Details (Tenant View)

**Endpoint:** `GET /api/apartments/{apartment_id}`  
**Access:** Protected (requires authentication + tenant role + approved status + verified OTP)  
**Description:** Retrieves detailed information about a specific apartment for viewing and potential booking.

**Request Headers:**

```
Authorization: Bearer {token}
```

**URL Parameters:**

-   `apartment_id` (required): The ID of the apartment

**Request Example:**

```
GET /api/apartments/1
```

**Response (Success):**

```json
{
    "success": true,
    "data": {
        "apartment": {
            "id": 1,
            "photos": ["/storage/apartments/photos/..."],
            "governorate": "Damascus",
            "governorate_ar": "دمشق",
            "city": "Damascus",
            "city_ar": "دمشق",
            "address": "123 Main Street",
            "address_ar": "123 الشارع الرئيسي",
            "nightly_price": "50.00",
            "monthly_price": "1200.00",
            "bedrooms": 2,
            "bathrooms": 1,
            "living_rooms": 1,
            "size": "80.50",
            "description": "Beautiful apartment in the heart of Damascus...",
            "description_ar": "شقة جميلة في قلب دمشق...",
            "amenities": ["WiFi", "Parking", "A/C", "Heating"],
            "average_rating": 4.5,
            "rating_count": 8,
            "owner": {
                "id": 3,
                "first_name": "Owner",
                "last_name": "Name",
                "personal_photo": "/storage/users/photos/...",
                "average_rating": 4.2
            },
            "reviews": [
                {
                    "id": 1,
                    "rating": 5,
                    "comment": "Great apartment, very clean and well-located!",
                    "user": {
                        "id": 2,
                        "first_name": "Tenant",
                        "last_name": "Name",
                        "personal_photo": "/storage/users/photos/..."
                    },
                    "created_at": "2024-01-15T10:00:00Z"
                }
            ],
            "created_at": "2024-01-10T10:00:00Z",
            "updated_at": "2024-01-15T10:00:00Z"
        }
    }
}
```

**Response (Error - Not Found):**

```json
{
    "success": false,
    "message": "Apartment not found or not available"
}
```

**Response (Error - Unauthorized):**

```json
{
    "success": false,
    "message": "Unauthorized. Tenant access required."
}
```

**Scenario:** Tenant views detailed information about an apartment they're interested in booking. This includes full details, owner information, recent reviews, and all photos. This is the screen shown before making a booking request.

---

#### 21. Create Booking

**Endpoint:** `POST /api/bookings`  
**Access:** Protected (requires authentication + tenant role + approved status + verified OTP)  
**Description:** Creates a new booking request for an apartment. Checks for date conflicts, calculates rent, verifies sufficient balance, processes payment, and creates the booking with "pending" status.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "apartment_id": 1,
    "check_in_date": "2025-12-26",
    "check_out_date": "2025-12-29",
    "number_of_guests": 2,
    "payment_method": "Cash"
}
```

**Request Fields:**

-   `apartment_id` (required, integer): The ID of the apartment to book
-   `check_in_date` (required, date): Check-in date (must be today or in the future, format: YYYY-MM-DD)
-   `check_out_date` (required, date): Check-out date (must be after check-in date, format: YYYY-MM-DD)
-   `number_of_guests` (optional, integer): Number of guests (1-20, default: null)
-   `payment_method` (required, string): Payment method (max 50 characters, e.g., "Cash", "Credit Card")

**Response (Success):**

```json
{
    "success": true,
    "message": "Booking request submitted successfully",
    "data": {
        "booking_id": 1,
        "status": "pending",
        "total_rent": "150.00",
        "check_in_date": "2025-12-26",
        "check_out_date": "2025-12-29",
        "remaining_balance": "850.00"
    }
}
```

**Response (Error - Date Conflict):**

```json
{
    "success": false,
    "message": "Selected dates are no longer available. Please select different dates."
}
```

**Response (Error - Insufficient Balance):**

```json
{
    "success": false,
    "message": "Insufficient balance. Required: 150.00, Available: 50.00"
}
```

**Response (Error - Apartment Not Found):**

```json
{
    "success": false,
    "message": "Apartment not found or not available"
}
```

**Response (Error - Validation):**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "check_in_date": ["Check-in date must be today or in the future"],
        "check_out_date": ["Check-out date must be after check-in date"]
    }
}
```

**Business Logic:**

1. **Conflict Detection:** System checks if any approved or pending bookings overlap with the requested dates. If conflict exists, booking is rejected.
2. **Rent Calculation:**
    - For stays ≤ 30 days: `total_rent = nightly_price × number_of_nights`
    - For stays > 30 days: Compares `nightly_price × nights` vs `monthly_price × ceil(nights/30)` and uses the cheaper option
3. **Balance Check:** Verifies tenant has sufficient balance (`balance >= total_rent`)
4. **Payment Processing:**
    - Deducts `total_rent` from tenant balance
    - Adds `total_rent` to owner balance
    - Creates two transaction records (one for tenant debit, one for owner credit)
5. **Booking Creation:** Creates booking with status "pending" (requires owner approval)

**Scenario:** Tenant selects an apartment and dates, submits booking request. System automatically calculates rent, checks for conflicts, verifies balance, processes payment, and creates the booking. Tenant receives confirmation with booking details and remaining balance. Owner receives notification of new booking request.

---

#### 22. List Tenant Bookings

**Endpoint:** `GET /api/bookings`  
**Access:** Protected (requires authentication + tenant role + approved status + verified OTP)  
**Description:** Lists all bookings for the authenticated tenant with status filtering and pagination.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Query Parameters:**

-   `status` (optional): Filter by status - `current`, `past`, `cancelled`, or null for all
-   `per_page` (optional): Items per page (default: 20)
-   `page` (optional): Page number (default: 1)

**Request Example:**

```
GET /api/bookings?status=current&per_page=10&page=1
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Bookings retrieved successfully",
    "data": {
        "bookings": [
            {
                "id": 1,
                "apartment": {
                    "id": 2,
                    "governorate": "Damascus",
                    "governorate_ar": "دمشق",
                    "city": "Damascus",
                    "city_ar": "دمشق",
                    "address": "123 Main Street, Damascus",
                    "address_ar": "123 الشارع الرئيسي، دمشق",
                    "photos": ["/storage/apartments/photos/..."]
                },
                "check_in_date": "2025-12-26",
                "check_out_date": "2025-12-29",
                "status": "pending",
                "number_of_guests": 2,
                "payment_method": "Cash",
                "total_rent": "150.00",
                "can_cancel": false,
                "can_modify": false,
                "can_rate": false,
                "created_at": "2025-12-25T14:21:27+00:00"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 20,
            "total": 1
        }
    }
}
```

**Scenario:** Tenant views their booking history. Can filter by status (current bookings, past bookings, or cancelled bookings). Each booking shows available actions (cancel, modify, rate) based on booking status and timing.

---

#### 23. Get Booking Details (Tenant)

**Endpoint:** `GET /api/bookings/{id}`  
**Access:** Protected (requires authentication + tenant role + approved status + verified OTP)  
**Description:** Retrieves detailed information about a specific booking.

**Request Headers:**

```
Authorization: Bearer {token}
```

**URL Parameters:**

-   `id` (required): The ID of the booking

**Response (Success):**

```json
{
    "success": true,
    "data": {
        "booking": {
            "id": 1,
            "status": "approved",
            "check_in_date": "2025-12-26",
            "check_out_date": "2025-12-29",
            "duration_nights": 3,
            "number_of_guests": 2,
            "payment_method": "Cash",
            "total_rent": "150.00",
            "can_cancel": true,
            "can_modify": true,
            "can_rate": false,
            "created_at": "2025-12-25T14:21:27+00:00",
            "updated_at": "2025-12-25T14:30:00+00:00",
            "apartment": {
                "id": 2,
                "governorate": "Damascus",
                "governorate_ar": "دمشق",
                "city": "Damascus",
                "city_ar": "دمشق",
                "address": "123 Main Street, Damascus",
                "address_ar": "123 الشارع الرئيسي، دمشق",
                "photos": ["/storage/apartments/photos/..."],
                "nightly_price": "50.00",
                "monthly_price": "1,000.00",
                "bedrooms": 2,
                "bathrooms": 1,
                "living_rooms": 1,
                "size": "80.00",
                "amenities": ["WiFi", "Parking", "Air Conditioning"],
                "owner": {
                    "id": 3,
                    "first_name": "Owner",
                    "last_name": "Name",
                    "personal_photo": "/storage/users/photos/..."
                }
            },
            "rating": null
        }
    }
}
```

**Scenario:** Tenant views detailed information about a specific booking, including apartment details, owner information, and available actions.

---

#### 24. Modify Booking

**Endpoint:** `PUT /api/bookings/{id}`  
**Access:** Protected (requires authentication + tenant role + approved status + verified OTP)  
**Description:** Modifies a booking (dates or guest count). Creates a modification request that requires owner approval.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "check_in_date": "2025-12-27",
    "check_out_date": "2025-12-30",
    "number_of_guests": 3
}
```

**Request Fields:**

-   `check_in_date` (optional, date): New check-in date
-   `check_out_date` (optional, date): New check-out date
-   `number_of_guests` (optional, integer): New number of guests

**Response (Success):**

```json
{
    "success": true,
    "message": "Modification request sent to owner",
    "data": {
        "booking_id": 1,
        "status": "modified_pending",
        "check_in_date": "2025-12-27",
        "check_out_date": "2025-12-30",
        "total_rent": "150.00"
    }
}
```

**Response (Error - Cannot Modify):**

```json
{
    "success": false,
    "message": "This booking cannot be modified. Only approved bookings can be modified."
}
```

**Response (Error - Too Close to Check-in):**

```json
{
    "success": false,
    "message": "Modification is only allowed at least 24 hours before check-in."
}
```

**Business Logic:**

-   Only approved or modified_approved bookings can be modified
-   Modification must be at least 24 hours before check-in
-   If new dates are provided, system checks for conflicts (excluding current booking)
-   If rent increases, additional payment is processed immediately
-   Booking status changes to "modified_pending" for owner approval

**Scenario:** Tenant wants to change booking dates or guest count. System validates changes, checks for conflicts, processes additional payment if needed, and sends modification request to owner.

---

#### 25. Cancel Booking

**Endpoint:** `POST /api/bookings/{id}/cancel`  
**Access:** Protected (requires authentication + tenant role + approved status + verified OTP)  
**Description:** Cancels a booking and processes partial refund (80% to tenant, 20% cancellation fee).

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**URL Parameters:**

-   `id` (required): The ID of the booking to cancel

**Response (Success):**

```json
{
    "success": true,
    "message": "Booking cancelled successfully",
    "data": {
        "booking_id": 1,
        "status": "cancelled",
        "refund_amount": "120.00",
        "cancellation_fee": "30.00",
        "remaining_balance": "970.00"
    }
}
```

**Response (Error - Cannot Cancel):**

```json
{
    "success": false,
    "message": "This booking cannot be cancelled."
}
```

**Response (Error - Too Close to Check-in):**

```json
{
    "success": false,
    "message": "Cancellation is only allowed at least 24 hours before check-in."
}
```

**Business Logic:**

-   Only pending, approved, or modified_approved bookings can be cancelled
-   Cancellation must be at least 24 hours before check-in
-   Processes 80% refund to tenant
-   Owner keeps 20% as cancellation fee
-   Creates transaction records for refund and cancellation fee
-   Booking status changes to "cancelled"

**Scenario:** Tenant cancels a booking. System processes partial refund (80%), owner keeps 20% as cancellation fee, and transaction records are created.

---

## Owner Endpoints

Endpoints accessible only by users with the `owner` role.

### Booking Management Endpoints

#### 26. List Owner Bookings

**Endpoint:** `GET /api/owner/bookings`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Lists all bookings for owner's apartments with status filtering and pagination.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Query Parameters:**

-   `status` (optional): Filter by status - `pending`, `approved`, `history`, or null for all
-   `per_page` (optional): Items per page (default: 20)
-   `page` (optional): Page number (default: 1)

**Request Example:**

```
GET /api/owner/bookings?status=pending&per_page=10
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Bookings retrieved successfully",
    "data": {
        "bookings": [
            {
                "id": 1,
                "status": "pending",
                "check_in_date": "2025-12-26",
                "check_out_date": "2025-12-29",
                "number_of_guests": 2,
                "payment_method": "Cash",
                "total_rent": "150.00",
                "created_at": "2025-12-25T14:21:27+00:00",
                "tenant": {
                    "id": 2,
                    "first_name": "Tenant",
                    "last_name": "Name",
                    "personal_photo": "/storage/users/photos/...",
                    "mobile_number": "+963935218432"
                },
                "apartment": {
                    "id": 2,
                    "address": "123 Main Street, Damascus",
                    "address_ar": "123 الشارع الرئيسي، دمشق",
                    "city": "Damascus",
                    "city_ar": "دمشق"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 20,
            "total": 1
        }
    }
}
```

**Scenario:** Owner views all booking requests for their apartments. Can filter by status (pending requests, approved bookings, or history).

---

#### 27. Get Booking Details (Owner)

**Endpoint:** `GET /api/owner/bookings/{id}`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Retrieves detailed information about a specific booking for owner's apartment.

**Request Headers:**

```
Authorization: Bearer {token}
```

**URL Parameters:**

-   `id` (required): The ID of the booking

**Response (Success):**

```json
{
    "success": true,
    "data": {
        "booking": {
            "id": 1,
            "status": "pending",
            "check_in_date": "2025-12-26",
            "check_out_date": "2025-12-29",
            "duration_nights": 3,
            "number_of_guests": 2,
            "payment_method": "Cash",
            "total_rent": "150.00",
            "created_at": "2025-12-25T14:21:27+00:00",
            "updated_at": "2025-12-25T14:21:27+00:00",
            "tenant": {
                "id": 2,
                "first_name": "Tenant",
                "last_name": "Name",
                "personal_photo": "/storage/users/photos/...",
                "mobile_number": "+963935218432"
            },
            "apartment": {
                "id": 2,
                "governorate": "Damascus",
                "governorate_ar": "دمشق",
                "city": "Damascus",
                "city_ar": "دمشق",
                "address": "123 Main Street, Damascus",
                "address_ar": "123 الشارع الرئيسي، دمشق",
                "photos": ["/storage/apartments/photos/..."]
            }
        }
    }
}
```

**Scenario:** Owner views detailed information about a booking request, including tenant details and apartment information, before making approval/rejection decision.

---

#### 28. Approve Booking

**Endpoint:** `PUT /api/owner/bookings/{id}/approve`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Approves a pending booking request.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**URL Parameters:**

-   `id` (required): The ID of the booking to approve

**Response (Success):**

```json
{
    "success": true,
    "message": "Booking approved successfully",
    "data": {
        "booking_id": 1,
        "status": "approved"
    }
}
```

**Response (Error - Cannot Approve):**

```json
{
    "success": false,
    "message": "Only pending bookings can be approved."
}
```

**Business Logic:**

-   Only pending bookings can be approved
-   Booking status changes to "approved"
-   Tenant receives notification

**Scenario:** Owner reviews a booking request and approves it. Booking status changes to "approved" and tenant is notified.

---

#### 29. Reject Booking

**Endpoint:** `PUT /api/owner/bookings/{id}/reject`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Rejects a pending booking request and processes full refund (100% returned to tenant).

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**URL Parameters:**

-   `id` (required): The ID of the booking to reject

**Response (Success):**

```json
{
    "success": true,
    "message": "Booking rejected and refund processed",
    "data": {
        "booking_id": 1,
        "status": "rejected",
        "refund_amount": "150.00"
    }
}
```

**Response (Error - Cannot Reject):**

```json
{
    "success": false,
    "message": "Only pending bookings can be rejected."
}
```

**Business Logic:**

-   Only pending bookings can be rejected
-   Full refund (100%) is processed and returned to tenant
-   Owner balance is decreased by refund amount
-   Transaction records are created
-   Booking status changes to "rejected"
-   Tenant receives notification

**Scenario:** Owner rejects a booking request. Full payment is refunded to tenant, owner balance is adjusted, and transaction records are created.

---

#### 30. Approve Modification

**Endpoint:** `PUT /api/owner/bookings/{id}/approve-modification`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Approves a pending modification request.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**URL Parameters:**

-   `id` (required): The ID of the booking

**Response (Success):**

```json
{
    "success": true,
    "message": "Modification approved successfully",
    "data": {
        "booking_id": 1,
        "status": "modified_approved",
        "check_in_date": "2025-12-27",
        "check_out_date": "2025-12-30"
    }
}
```

**Response (Error - Cannot Approve):**

```json
{
    "success": false,
    "message": "Only pending modifications can be approved."
}
```

**Business Logic:**

-   Only bookings with status "modified_pending" can have modifications approved
-   Booking status changes to "modified_approved"
-   Tenant receives notification

**Scenario:** Owner approves a tenant's modification request. Booking dates/guests are updated and booking status changes to "modified_approved".

---

#### 31. Reject Modification

**Endpoint:** `PUT /api/owner/bookings/{id}/reject-modification`  
**Access:** Protected (requires authentication + owner role + approved status + verified OTP)  
**Description:** Rejects a pending modification request and reverts booking to approved status.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**URL Parameters:**

-   `id` (required): The ID of the booking

**Response (Success):**

```json
{
    "success": true,
    "message": "Modification rejected. Booking reverted to approved status.",
    "data": {
        "booking_id": 1,
        "status": "approved"
    }
}
```

**Response (Error - Cannot Reject):**

```json
{
    "success": false,
    "message": "Only pending modifications can be rejected."
}
```

**Business Logic:**

-   Only bookings with status "modified_pending" can have modifications rejected
-   Booking status reverts to "approved"
-   Original booking details are preserved
-   Tenant receives notification

**Scenario:** Owner rejects a tenant's modification request. Booking reverts to original approved status with original dates/guests.

---

## Admin Endpoints

Endpoints accessible only by users with the `admin` role.

### Apartment Management Endpoints

#### 32. List All Apartments (Admin)

**Endpoint:** `GET /api/admin/apartments`  
**Access:** Protected (requires authentication + admin role + approved status + verified OTP)  
**Description:** Lists all apartments in the system with filtering, searching, and pagination.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Query Parameters:**

-   `status` (optional): Filter by status - `active`, `inactive`, `deleted`
-   `governorate` (optional): Filter by governorate (matches English or Arabic)
-   `city` (optional): Filter by city (matches English or Arabic)
-   `search` (optional): Search in address, governorate, city, or owner name
-   `sort` (optional): Sort field - `id`, `created_at`, `nightly_price`, `monthly_price`, `status` (default: `created_at`)
-   `sort_order` (optional): Sort direction - `asc`, `desc` (default: `desc`)
-   `per_page` (optional): Items per page (default: 25)
-   `page` (optional): Page number (default: 1)

**Request Example:**

```
GET /api/admin/apartments?status=active&governorate=Damascus&search=main&sort=created_at&sort_order=desc&per_page=25&page=1
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Apartments retrieved successfully",
    "data": {
        "apartments": [
            {
                "id": 1,
                "status": "active",
                "governorate": "Damascus",
                "governorate_ar": "دمشق",
                "city": "Damascus",
                "city_ar": "دمشق",
                "address": "123 Main Street, Damascus",
                "address_ar": "123 الشارع الرئيسي، دمشق",
                "nightly_price": "50.00",
                "monthly_price": "1,000.00",
                "bedrooms": 2,
                "bathrooms": 1,
                "living_rooms": 1,
                "size": "80.00",
                "photos": ["/storage/apartments/photos/..."],
                "amenities": ["WiFi", "Parking", "Air Conditioning"],
                "total_bookings": 12,
                "average_rating": "4.5",
                "created_at": "2024-01-15T10:00:00Z",
                "updated_at": "2024-01-15T10:00:00Z",
                "owner": {
                    "id": 3,
                    "first_name": "Owner",
                    "last_name": "Name",
                    "mobile_number": "+9639914343250",
                    "personal_photo": "/storage/users/photos/..."
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 25,
            "total": 120
        }
    }
}
```

**Scenario:** Admin views all apartments in the system for monitoring and content management. Can filter by status, location, search by text, and sort by various fields.

---

### Booking Management Endpoints

#### 33. List All Bookings (Admin)

**Endpoint:** `GET /api/admin/bookings`  
**Access:** Protected (requires authentication + admin role + approved status + verified OTP)  
**Description:** Lists all bookings in the system with filtering, searching, and pagination.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Query Parameters:**

-   `status` (optional): Filter by booking status
-   `search` (optional): Search by booking ID, tenant name, or apartment address
-   `check_in_from` (optional): Filter by check-in date (from)
-   `check_in_to` (optional): Filter by check-in date (to)
-   `sort_by` (optional): Sort field - `id`, `created_at`, `check_in_date`, `check_out_date`, `total_rent`, `status` (default: `created_at`)
-   `sort_order` (optional): Sort direction - `asc`, `desc` (default: `desc`)
-   `per_page` (optional): Items per page (default: 25)
-   `page` (optional): Page number (default: 1)

**Request Example:**

```
GET /api/admin/bookings?status=approved&search=john&check_in_from=2025-12-01&sort_by=check_in_date&sort_order=asc
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Bookings retrieved successfully",
    "data": {
        "bookings": [
            {
                "id": 1,
                "status": "approved",
                "check_in_date": "2025-12-26",
                "check_out_date": "2025-12-29",
                "number_of_guests": 2,
                "payment_method": "Cash",
                "total_rent": "150.00",
                "created_at": "2025-12-25T14:21:27+00:00",
                "updated_at": "2025-12-25T14:30:00+00:00",
                "tenant": {
                    "id": 2,
                    "first_name": "Tenant",
                    "last_name": "Name",
                    "mobile_number": "+963935218432",
                    "personal_photo": "/storage/users/photos/..."
                },
                "apartment": {
                    "id": 2,
                    "address": "123 Main Street, Damascus",
                    "address_ar": "123 الشارع الرئيسي، دمشق",
                    "city": "Damascus",
                    "city_ar": "دمشق",
                    "governorate": "Damascus",
                    "governorate_ar": "دمشق"
                },
                "owner": {
                    "id": 3,
                    "first_name": "Owner",
                    "last_name": "Name",
                    "mobile_number": "+9639914343250"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 10,
            "per_page": 25,
            "total": 245
        }
    }
}
```

**Scenario:** Admin monitors all bookings in the system. Can filter by status, search by tenant or apartment, filter by date range, and sort by various fields.

---

## Tenant Endpoints

Endpoints accessible only by users with the `tenant` role.

### Rating Endpoints

#### 34. Create Rating

**Endpoint:** `POST /api/ratings`  
**Access:** Protected (requires authentication + tenant role + approved status + verified OTP)  
**Description:** Creates a rating and review for a completed booking. Only allows rating after check-out date has passed and booking status is "completed".

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "booking_id": 1,
    "rating": 5,
    "review_text": "Excellent apartment, very clean and well-located!"
}
```

**Request Fields:**

-   `booking_id` (required, integer): The ID of the completed booking to rate
-   `rating` (required, integer): Rating value (1-5 stars)
-   `review_text` (optional, string): Review text (max 500 characters)

**Response (Success):**

```json
{
    "success": true,
    "message": "Rating submitted successfully",
    "data": {
        "rating_id": 1,
        "booking_id": 1,
        "apartment_id": 2,
        "rating": 5,
        "review_text": "Excellent apartment, very clean and well-located!",
        "apartment_average_rating": "4.5",
        "created_at": "2025-12-25T15:07:33+00:00"
    }
}
```

**Response (Error - Booking Not Found):**

```json
{
    "success": false,
    "message": "Booking not found or you do not have permission to rate this booking"
}
```

**Response (Error - Not Completed):**

```json
{
    "success": false,
    "message": "You can only rate completed bookings. This booking status is: approved"
}
```

**Response (Error - Check-out Not Passed):**

```json
{
    "success": false,
    "message": "You can only rate after the check-out date has passed."
}
```

**Response (Error - Already Rated):**

```json
{
    "success": false,
    "message": "You have already rated this booking"
}
```

**Response (Error - Validation):**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "rating": ["Rating must be at least 1 star"],
        "review_text": ["Review text cannot exceed 500 characters"]
    }
}
```

**Business Logic:**

1. **Booking Verification:** Checks that booking exists and belongs to the authenticated tenant
2. **Status Check:** Only allows rating for bookings with status "completed"
3. **Date Check:** Only allows rating after check-out date has passed
4. **Duplicate Prevention:** Prevents multiple ratings for the same booking (one rating per booking)
5. **Rating Creation:** Creates rating record linked to booking, apartment, and tenant
6. **Average Update:** Apartment's average rating is automatically recalculated (via model accessor)

**Scenario:** Tenant completes a stay and wants to rate the apartment. System validates that the booking is completed and check-out date has passed, then creates the rating. Apartment's average rating is automatically updated.

---

### Favorites Management Endpoints

#### 35. List Favorites

**Endpoint:** `GET /api/favorites`  
**Access:** Protected (requires authentication + tenant role + approved status + verified OTP)  
**Description:** Lists all apartments favorited by the authenticated tenant with pagination.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Query Parameters:**

-   `per_page` (optional): Items per page (default: 20)
-   `page` (optional): Page number (default: 1)

**Request Example:**

```
GET /api/favorites?per_page=10&page=1
```

**Response (Success):**

```json
{
    "success": true,
    "data": {
        "apartments": [
            {
                "id": 2,
                "photos": ["/storage/apartments/photos/..."],
                "governorate": "Damascus",
                "governorate_ar": "دمشق",
                "city": "Damascus",
                "city_ar": "دمشق",
                "address": "123 Main Street, Damascus",
                "address_ar": "123 الشارع الرئيسي، دمشق",
                "nightly_price": "50.00",
                "monthly_price": "1,000.00",
                "bedrooms": 2,
                "bathrooms": 1,
                "living_rooms": 1,
                "size": "80.00",
                "amenities": ["WiFi", "Parking", "Air Conditioning"],
                "average_rating": "4.0",
                "rating_count": 2,
                "favorited_at": "2025-12-25T15:24:11.000000Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 20,
            "total": 1
        }
    }
}
```

**Response (Empty):**

```json
{
    "success": true,
    "data": {
        "apartments": [],
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 20,
            "total": 0
        }
    }
}
```

**Scenario:** Tenant views their saved favorite apartments. Can scroll through paginated results. Each apartment shows full details including photos, location, prices, ratings, and when it was favorited.

---

#### 36. Add to Favorites

**Endpoint:** `POST /api/favorites`  
**Access:** Protected (requires authentication + tenant role + approved status + verified OTP)  
**Description:** Adds an apartment to the tenant's favorites list.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "apartment_id": 2
}
```

**Request Fields:**

-   `apartment_id` (required, integer): The ID of the apartment to favorite

**Response (Success):**

```json
{
    "success": true,
    "message": "Apartment added to favorites",
    "data": {
        "favorite_id": 1,
        "apartment_id": 2,
        "favorited_at": "2025-12-25T15:24:11.000000Z"
    }
}
```

**Response (Error - Already Favorited):**

```json
{
    "success": false,
    "message": "Apartment is already in your favorites"
}
```

**Response (Error - Not Found):**

```json
{
    "success": false,
    "message": "Apartment not found or not available"
}
```

**Business Logic:**

-   Only active apartments can be favorited
-   Duplicate prevention (same tenant, same apartment)
-   Unique constraint in database prevents duplicates

**Scenario:** Tenant browses apartments and finds one they like. They tap the heart icon to add it to favorites. System validates the apartment exists and is active, then adds it to their favorites list.

---

#### 37. Remove from Favorites

**Endpoint:** `DELETE /api/favorites/{apartment_id}`  
**Access:** Protected (requires authentication + tenant role + approved status + verified OTP)  
**Description:** Removes an apartment from the tenant's favorites list.

**Request Headers:**

```
Authorization: Bearer {token}
```

**URL Parameters:**

-   `apartment_id` (required): The ID of the apartment to remove from favorites

**Response (Success):**

```json
{
    "success": true,
    "message": "Apartment removed from favorites"
}
```

**Response (Error - Not in Favorites):**

```json
{
    "success": false,
    "message": "Apartment is not in your favorites"
}
```

**Scenario:** Tenant views their favorites list and decides to remove an apartment. They tap the heart icon or use the remove action. System removes the apartment from their favorites list.

---

## Shared Endpoints

Endpoints accessible by all authenticated users (Tenants, Owners, Admins).

### Messaging Endpoints

#### 38. List Conversations

**Endpoint:** `GET /api/messages`  
**Access:** Protected (requires authentication + approved status + verified OTP)  
**Description:** Lists all conversations for the authenticated user, showing the last message and unread count for each conversation partner.

**Request Headers:**

```
Authorization: Bearer {token}
```

**Response (Success):**

```json
{
    "success": true,
    "data": {
        "conversations": [
            {
                "user": {
                    "id": 3,
                    "first_name": "Owner",
                    "last_name": "Name",
                    "personal_photo": "/storage/users/photos/...",
                    "role": "owner"
                },
                "last_message": {
                    "id": 2,
                    "message_text": "Hello! Thank you for your interest.",
                    "sender_id": 3,
                    "is_read": false,
                    "created_at": "2025-12-25T15:30:00+00:00"
                },
                "unread_count": 1,
                "last_message_at": "2025-12-25T15:30:00+00:00"
            }
        ]
    }
}
```

**Response (Empty):**

```json
{
    "success": true,
    "data": {
        "conversations": []
    }
}
```

**Scenario:** User opens the messages screen. System shows all users they have conversations with, sorted by most recent message. Each conversation shows the partner's info, last message preview, and unread count.

---

#### 39. Get Conversation with User

**Endpoint:** `GET /api/messages/{user_id}`  
**Access:** Protected (requires authentication + approved status + verified OTP)  
**Description:** Retrieves all messages in a conversation with a specific user. Automatically marks all messages from that user as read.

**Request Headers:**

```
Authorization: Bearer {token}
```

**URL Parameters:**

-   `user_id` (required): The ID of the user to get conversation with

**Response (Success):**

```json
{
    "success": true,
    "data": {
        "conversation_with": {
            "id": 3,
            "first_name": "Owner",
            "last_name": "Name",
            "personal_photo": "/storage/users/photos/...",
            "role": "owner"
        },
        "messages": [
            {
                "id": 1,
                "sender": {
                    "id": 2,
                    "first_name": "Tenant",
                    "last_name": "Name",
                    "personal_photo": "/storage/users/photos/..."
                },
                "recipient": {
                    "id": 3,
                    "first_name": "Owner",
                    "last_name": "Name",
                    "personal_photo": "/storage/users/photos/..."
                },
                "message_text": "Hello, I am interested in your apartment!",
                "attachment_path": null,
                "is_read": true,
                "created_at": "2025-12-25T15:25:00+00:00"
            },
            {
                "id": 2,
                "sender": {
                    "id": 3,
                    "first_name": "Owner",
                    "last_name": "Name",
                    "personal_photo": "/storage/users/photos/..."
                },
                "recipient": {
                    "id": 2,
                    "first_name": "Tenant",
                    "last_name": "Name",
                    "personal_photo": "/storage/users/photos/..."
                },
                "message_text": "Hello! Thank you for your interest. The apartment is available.",
                "attachment_path": null,
                "is_read": true,
                "created_at": "2025-12-25T15:30:00+00:00"
            }
        ]
    }
}
```

**Response (Error - User Not Found):**

```json
{
    "success": false,
    "message": "User not found"
}
```

**Response (Error - Cannot Message Yourself):**

```json
{
    "success": false,
    "message": "You cannot message yourself"
}
```

**Business Logic:**

-   Automatically marks all messages from the conversation partner as read when viewing the conversation
-   Messages are sorted chronologically (oldest first)
-   Shows full conversation history between the two users

**Scenario:** User opens a conversation with another user. System displays all messages in chronological order and automatically marks messages from the other user as read.

---

#### 40. Message Handler (HTTP Bridge for WebSocket Messages)

**Endpoint:** `POST /api/messages/ws`  
**Access:** Protected (requires authentication + approved status + verified OTP)  
**Description:** HTTP POST endpoint that acts as a bridge/handler for WebSocket message operations. This endpoint processes message actions (send, mark as read, typing indicators) that are typically sent via WebSocket connections.

**Important:** This is an HTTP endpoint, NOT a WebSocket endpoint. Actual WebSocket connections are made to Laravel Reverb server (typically `ws://localhost:8080`). This HTTP endpoint can be used as a fallback or for testing purposes. WebSocket connections cannot be tested via Postman collections - use a WebSocket client (like `wscat` or a WebSocket testing tool) instead.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body - Send Message:**

```json
{
    "type": "send_message",
    "recipient_id": 3,
    "message_text": "Hello, I am interested in your apartment!"
}
```

**Request Body - Mark as Read:**

```json
{
    "type": "mark_read",
    "message_id": 1
}
```

**Request Body - Typing Indicator:**

```json
{
    "type": "typing",
    "recipient_id": 3
}
```

**Request Body - Stop Typing:**

```json
{
    "type": "stop_typing",
    "recipient_id": 3
}
```

**Request Fields:**

-   `type` (required, string): Action type - `send_message`, `mark_read`, `typing`, or `stop_typing`
-   `recipient_id` (required for `send_message`, `typing`, `stop_typing`, integer): The ID of the user to send message to
-   `message_text` (required for `send_message`, string): The message text (max 2000 characters)
-   `message_id` (required for `mark_read`, integer): The ID of the message to mark as read

**Response (Success - Send Message):**

```json
{
    "success": true,
    "message": "Message sent successfully",
    "type": "send_message",
    "data": {
        "message": {
            "id": 1,
            "sender_id": 2,
            "recipient_id": 3,
            "message_text": "Hello, I am interested in your apartment!",
            "attachment_path": null,
            "is_read": false,
            "created_at": "2025-12-25T15:25:00+00:00"
        }
    }
}
```

**Response (Success - Mark as Read):**

```json
{
    "success": true,
    "message": "Message marked as read",
    "type": "mark_read",
    "data": {
        "message_id": 1,
        "is_read": true
    }
}
```

**Response (Success - Typing):**

```json
{
    "success": true,
    "message": "Typing indicator sent",
    "type": "typing"
}
```

**Response (Error):**

```json
{
    "success": false,
    "message": "Error message here",
    "type": "error"
}
```

**Business Logic:**

-   **Send Message:** Prevents users from messaging themselves, creates message with `is_read = false`, broadcasts via WebSocket to recipient
-   **Mark as Read:** Only the recipient can mark a message as read, prevents marking already-read messages
-   **Typing Indicators:** Broadcasts typing status to recipient in real-time via WebSocket
-   Messages are automatically marked as read when viewing a conversation via `GET /api/messages/{user_id}`
-   Real-time delivery via Laravel Broadcasting (Redis + Reverb)
-   Push notifications sent via FCM when recipient is offline

**Scenario:** User sends a message via WebSocket. The message is stored in the database and broadcast in real-time to the recipient. If the recipient is online, they receive it via WebSocket. If offline, they receive a push notification via FCM.

---

#### 41. Upload Attachment

**Endpoint:** `POST /api/messages/upload-attachment`  
**Access:** Protected (requires authentication + approved status + verified OTP)  
**Description:** Uploads a file attachment for messaging. After upload, the file path can be used with the WebSocket send message endpoint.

**Request Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (multipart/form-data):**

```
attachment: [file]  // required, max 10MB, jpeg/jpg/png/pdf/doc/docx
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Attachment uploaded successfully",
    "data": {
        "file_path": "/storage/messages/attachments/...",
        "file_url": "http://localhost:8000/storage/messages/attachments/..."
    }
}
```

**Response (Error - Validation):**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "attachment": [
            "The attachment field is required",
            "The attachment must be a file of type: jpeg, jpg, png, pdf, doc, docx",
            "The attachment may not be greater than 10240 kilobytes"
        ]
    }
}
```

**Business Logic:**

-   File is stored in `storage/app/public/messages/attachments/`
-   Supported file types: images (jpeg, jpg, png), documents (pdf, doc, docx)
-   Maximum file size: 10MB
-   After upload, use the returned `file_path` in the WebSocket send message endpoint

**Scenario:** User wants to send a photo or document. They upload it via HTTP (easier for multipart/form-data), then send a message via WebSocket with the attachment path.

---

### WebSocket Endpoints

**Important:** These are actual WebSocket connections, not HTTP endpoints. They require a WebSocket client (cannot be tested in Postman). The WebSocket server is Laravel Reverb, typically running on `ws://localhost:8080`.

#### WebSocket Connection

**Connection URL:** `ws://localhost:8080/app/{app_key}`  
**Protocol:** WebSocket (WSS for production)  
**Authentication:** JWT token passed during connection handshake

**Connection Steps:**

1. **Get JWT Token:** Authenticate via `POST /api/auth/login` to receive JWT token
2. **Connect to WebSocket:** Connect to `ws://localhost:8080/app/{app_key}` with JWT token
3. **Subscribe to Channel:** Subscribe to private channel `private-user.{userId}` where `{userId}` is the authenticated user's ID
4. **Listen for Events:** Receive real-time events on subscribed channels

**Example Connection (JavaScript):**

```javascript
// Connect to Laravel Reverb WebSocket server
const ws = new WebSocket("ws://localhost:8080/app/your-app-key");

// Authenticate with JWT token
ws.onopen = () => {
    ws.send(
        JSON.stringify({
            event: "pusher:subscribe",
            data: {
                channel: "private-user.1", // User ID 1's private channel
                auth: "Bearer YOUR_JWT_TOKEN",
            },
        })
    );
};

// Listen for messages
ws.onmessage = (event) => {
    const data = JSON.parse(event.data);
    console.log("Received:", data);
};
```

---

#### 42. WebSocket Channel: Private User Channel

**Channel:** `private-user.{userId}`  
**Type:** Private Channel  
**Access:** Only the user with matching `userId` can subscribe to their own channel

**Channel Format:**

```
private-user.1    // User ID 1's private channel
private-user.2    // User ID 2's private channel
private-user.{userId}  // Dynamic channel based on authenticated user ID
```

**Authorization:**

-   User must be authenticated with valid JWT token
-   User can only subscribe to their own private channel (`user.{userId}` where `userId` matches authenticated user's ID)
-   Authorization is handled by Laravel Broadcasting via `routes/channels.php`

**Subscription Example:**

```javascript
// Subscribe to your own private channel
const channel = pusher.subscribe("private-user.1"); // Replace 1 with your user ID

channel.bind("pusher:subscription_succeeded", () => {
    console.log("Successfully subscribed to private-user.1");
});
```

---

#### 43. WebSocket Event: Message Sent

**Event Name:** `message.sent`  
**Channel:** `private-user.{recipientId}` (broadcasted to recipient)  
**Description:** Broadcasted when a new message is sent to the user

**Event Data:**

```json
{
    "id": 123,
    "sender_id": 2,
    "recipient_id": 1,
    "message_text": "Hello! I'm interested in your apartment.",
    "attachment_path": null,
    "is_read": false,
    "created_at": "2025-12-25T15:30:00+00:00",
    "sender": {
        "id": 2,
        "first_name": "John",
        "last_name": "Doe",
        "personal_photo": "/storage/users/photos/..."
    }
}
```

**Event Fields:**

-   `id` (integer): Message ID
-   `sender_id` (integer): ID of the user who sent the message
-   `recipient_id` (integer): ID of the user who receives the message
-   `message_text` (string): The message content
-   `attachment_path` (string|null): Path to attachment file if any
-   `is_read` (boolean): Whether the message has been read
-   `created_at` (string): ISO 8601 timestamp
-   `sender` (object): Sender's basic information (id, first_name, last_name, personal_photo)

**Listening Example:**

```javascript
channel.bind("message.sent", (data) => {
    console.log("New message received:", data);
    // Update UI with new message
    displayMessage(data);
});
```

**Scenario:** User A sends a message to User B. The `message.sent` event is broadcasted to User B's private channel (`private-user.{userB_id}`). User B receives the message in real-time via WebSocket.

---

#### 44. WebSocket Event: Message Read

**Event Name:** `message.read`  
**Channel:** `private-user.{senderId}` (broadcasted to sender)  
**Description:** Broadcasted when a message is marked as read by the recipient

**Event Data:**

```json
{
    "message_id": 123,
    "recipient_id": 1,
    "is_read": true,
    "read_at": "2025-12-25T15:35:00+00:00"
}
```

**Event Fields:**

-   `message_id` (integer): ID of the message that was read
-   `recipient_id` (integer): ID of the user who read the message
-   `is_read` (boolean): Always `true` for this event
-   `read_at` (string): ISO 8601 timestamp when message was marked as read

**Listening Example:**

```javascript
channel.bind("message.read", (data) => {
    console.log("Message read:", data);
    // Update UI to show message as read
    markMessageAsRead(data.message_id);
});
```

**Scenario:** User B reads a message from User A. The `message.read` event is broadcasted to User A's private channel (`private-user.{userA_id}`). User A sees in real-time that their message was read.

---

#### 45. WebSocket Event: User Typing

**Event Name:** `user.typing`  
**Channel:** `private-user.{recipientId}` (broadcasted to recipient)  
**Description:** Broadcasted when a user starts or stops typing

**Event Data:**

```json
{
    "sender_id": 2,
    "is_typing": true
}
```

**Event Fields:**

-   `sender_id` (integer): ID of the user who is typing
-   `is_typing` (boolean): `true` when user starts typing, `false` when user stops typing

**Listening Example:**

```javascript
channel.bind("user.typing", (data) => {
    if (data.is_typing) {
        console.log(`User ${data.sender_id} is typing...`);
        showTypingIndicator(data.sender_id);
    } else {
        console.log(`User ${data.sender_id} stopped typing`);
        hideTypingIndicator(data.sender_id);
    }
});
```

**Scenario:** User A starts typing a message to User B. The `user.typing` event with `is_typing: true` is broadcasted to User B's private channel. User B sees "User A is typing..." indicator. When User A stops typing, another event with `is_typing: false` is sent.

---

#### WebSocket Message Sending

**Note:** To send messages via WebSocket, clients typically send messages through the WebSocket connection to Laravel Reverb, which then processes them. However, the current implementation uses the HTTP bridge endpoint `POST /api/messages/ws` for sending messages.

For a fully WebSocket-based implementation, clients would send messages directly through the WebSocket connection, but this requires additional server-side WebSocket message handling setup.

**Current Architecture:**

-   **Sending Messages:** HTTP POST to `POST /api/messages/ws` (acts as bridge)
-   **Receiving Messages:** WebSocket events on `private-user.{userId}` channel
-   **Real-time Delivery:** Laravel Broadcasting + Redis + Reverb

**Future Enhancement:**

A fully WebSocket-based send implementation would allow clients to send messages directly through the WebSocket connection, eliminating the need for the HTTP bridge endpoint.

---
