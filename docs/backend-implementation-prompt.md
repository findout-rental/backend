# Backend Implementation Prompt - FindOut Laravel API

## Project Context

This prompt is for implementing the **FindOut Apartment Rental Application Backend API** using Laravel. The backend serves both:
- **Customer Mobile App** (Flutter) - for tenants and apartment owners
- **Admin Web Application** (Flutter Web) - for system administrators

**Project Root Structure:**
```
/home/ace/Desktop/findout/backend/
├── app/              # Laravel application code
├── config/           # Configuration files
├── database/         # Migrations, seeders
├── routes/           # API routes
├── docs/             # Documentation files (SRS, ERD, screens, etc.)
└── ...               # Other Laravel directories
```

**Important:** When implementing, open Cursor from `/home/ace/Desktop/findout/backend/` directory. All documentation files are located in the `docs/` folder inside the backend directory.

---

## Documentation Reference

**All requirements, database schema, and API specifications are documented in the `docs/` folder:**
- `docs/SRS.md` - Complete Software Requirements Specification
- `docs/ERD.md` - Database design and relationships
- `docs/database.sql` - Ready-to-execute MySQL schema
- `docs/payment-system-implementation.md` - Payment system details
- `docs/customer-app-screens.md` - Mobile app API requirements
- `docs/admin-web-screens.md` - Admin web API requirements


**Required Documentation Files to Read:**
- `SRS.md`
- `ERD.md`
- `database.sql`
- `payment-system-implementation.md`
- `customer-app-screens.md`
- `admin-web-screens.md`

**DO NOT invent fields, endpoints, or relationships. Follow the documentation exactly.**

---

## Implementation Structure

### Allowed Laravel Components ONLY:

- ✅ **Controllers** - Handle HTTP requests
- ✅ **Models** - Eloquent models with relationships
- ✅ **Requests** - Form request validation classes
- ✅ **Middlewares** - Authentication, authorization, CORS
- ✅ **Services** - Business logic (e.g., `BookingService`, `PaymentService`)
- ✅ **Policies** - Authorization policies
- ✅ **Events** - Laravel events
- ✅ **Listeners** - Event listeners
- ✅ **Routes** - API routes (RESTful)
- ✅ **Migrations** - Database migrations (one per table)
- ✅ **Seeders** - Database seeders (admin users, test data)
- ✅ **config/** - Configuration files
- ✅ **.env** - Environment configuration

**DO NOT create:**
- ❌ Helper classes outside Laravel conventions
- ❌ Additional abstraction layers
- ❌ Custom framework components
- ❌ Repositories (unless explicitly needed)

---

## Implementation Order (Based on Dependencies)

Implement modules in this exact order:

1. **Foundation & Authentication**
   - Database migrations (all tables)
   - Models with relationships
   - Authentication system (OTP, JWT)
   - User registration & login

2. **User Management**
   - User profile management
   - Personal information handling
   - Language preference

3. **Apartment Management**
   - Apartment CRUD operations
   - File upload handling (photos)
   - Bilingual content support

4. **Apartment Browsing & Filtering**
   - Apartment listing endpoints
   - Search and filter functionality
   - Apartment details

5. **Booking System**
   - Booking creation with conflict detection
   - Payment processing integration
   - Booking status management

6. **Booking Management**
   - Booking modification
   - Booking cancellation
   - Booking history

7. **Rating System**
   - Rating creation (post-booking)
   - Average rating calculation

8. **Favorites Management**
   - Add/remove favorites
   - Favorites listing

9. **Messaging System**
   - Send/receive messages
   - Conversation management
   - File attachments

10. **Notifications System**
    - Notification creation
    - Push notification integration (FCM)
    - Notification history

11. **Payment/Balance System**
    - Balance management
    - Transaction tracking
    - Refund processing

12. **Admin Features**
    - Admin authentication
    - Registration approval/rejection
    - User management
    - Balance operations
    - Content overview

---

## API Structure Recommendations

### Route Grouping: Feature-Based

**Customer/Mobile App APIs:**
```
/api/auth/*              - Authentication (OTP, login, logout)
/api/user/*              - User profile, preferences
/api/apartments/*        - Apartment browsing, filtering, details
/api/bookings/*          - Booking operations
/api/ratings/*           - Rating operations
/api/favorites/*         - Favorites management
/api/messages/*          - Messaging system
/api/notifications/*     - Notifications
/api/balance/*          - Balance and transactions
```

**Admin APIs (All under `/api/admin/`):**
```
/api/admin/auth/*        - Admin authentication
/api/admin/dashboard/*   - Dashboard statistics
/api/admin/registrations/* - Registration management
/api/admin/users/*       - User management
/api/admin/apartments/*  - Apartment overview
/api/admin/bookings/*    - Booking overview
/api/admin/balance/*     - Balance operations
```

### Route Naming Convention

Use Laravel's default RESTful resource naming:
- `Route::resource('apartments', ApartmentController::class)`
- Generates: `apartments.index`, `apartments.store`, `apartments.show`, etc.

For custom actions, use standard Laravel patterns:
- `apartments.search` (GET)
- `bookings.cancel` (POST)
- `admin.registrations.approve` (PUT)

---

## Authentication & Authorization

### Authentication Method

**For Tenants/Owners:**
- Mobile number + Password (after OTP verification during registration)
- JWT token-based authentication
- Token expiration: 24 hours of inactivity

**For Admins:**
- Mobile number + Password (no OTP)
- JWT token-based authentication
- Token expiration: 24 hours of inactivity

### Middleware Recommendation: Single Role-Based Middleware

**Recommended Approach:** Create a single `RoleMiddleware` that checks user role from JWT token.

**Pros:**
- Single middleware handles all role checks
- Cleaner route definitions
- Easy to extend for new roles
- Consistent authorization logic

**Cons:**
- Requires role in JWT token (see below)

**Implementation:**
```php
// Middleware: app/Http/Middleware/RoleMiddleware.php
public function handle($request, Closure $next, ...$roles)
{
    $user = $request->user();
    if (!in_array($user->role, $roles)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    return $next($request);
}

// Usage in routes:
Route::middleware(['auth:api', 'role:owner'])->group(function () {
    // Owner-only routes
});
```

### JWT Token Role Storage: Include Role in Token

**Recommended Approach:** Include user role in JWT token claims.

**Pros:**
- Faster authorization (no database query per request)
- Reduced database load
- Token is self-contained
- Better performance for high-traffic scenarios

**Cons:**
- Role changes require user to re-login (acceptable for this use case)
- Token must be regenerated if role changes (rare scenario)

**Alternative (Not Recommended):** Fetch role from database on each request
- Pros: Always current role
- Cons: Extra database query on every request, slower performance

**Implementation:**
```php
// In AuthController, when generating token:
$token = auth()->claims(['role' => $user->role])->login($user);
```

---

## Business Logic Recommendations

### Rent Calculation: Service Class

**Recommended:** Create `BookingService` class for rent calculation logic.

**Why:**
- Business logic separation from controllers
- Reusable across different contexts
- Easier to test and maintain
- Follows Single Responsibility Principle

**Implementation:**
```php
// app/Services/BookingService.php
class BookingService
{
    public function calculateRent(Apartment $apartment, $checkInDate, $checkOutDate)
    {
        $nights = Carbon::parse($checkInDate)->diffInDays(Carbon::parse($checkOutDate));
        
        if ($nights <= 30) {
            return $apartment->nightly_price * $nights;
        }
        
        $dailyTotal = $apartment->nightly_price * $nights;
        $monthlyTotal = $apartment->monthly_price * ceil($nights / 30);
        
        return min($dailyTotal, $monthlyTotal);
    }
}
```

### Booking Modifications: Update Existing Record

**Recommended:** Update the existing booking record with status tracking.

**Why:**
- Maintains booking history in one place
- Status field tracks modification state (`modified_pending`, `modified_approved`, etc.)
- Simpler data model
- Easier to query and display

**Implementation:**
- When tenant requests modification: Update booking with new dates, set status to `modified_pending`
- When owner approves/rejects: Update status to `modified_approved` or `modified_rejected`
- Store original dates in transaction history or notifications if needed

### Transaction Amount Storage: Positive Values with Logic

**Recommended:** Store all transaction amounts as **positive values** in database. Application logic determines increase/decrease.

**Why:**
- Matches payment system documentation
- Clearer transaction records (all positive amounts)
- Easier to read transaction history
- Logic in one place (Service class)

**Implementation:**
```php
// Transaction amount always positive
// Balance update logic in PaymentService:
if ($transaction->type === 'deposit' || 
    ($transaction->type === 'rent_payment' && $transaction->user_id === $owner_id) ||
    ($transaction->type === 'refund' && $transaction->user_id === $tenant_id)) {
    $user->balance += $transaction->amount; // Increase
} else {
    $user->balance -= $transaction->amount; // Decrease
}
```

---

## Database & Migrations

### Migration Strategy: One Migration Per Table

**Recommended:** Create separate migration file for each table.

**Why:**
- Standard Laravel practice
- Easier to track changes
- Better version control
- Clearer migration history

**Order:**
1. `create_users_table`
2. `create_apartments_table`
3. `create_bookings_table`
4. `create_ratings_table`
5. `create_favorites_table`
6. `create_messages_table`
7. `create_notifications_table`
8. `create_otp_verifications_table`
9. `create_transactions_table`

### Seeders: Admin Users Required

**Create seeder for:**
- At least one admin user (for testing)
- Optional: Test tenant and owner users
- Optional: Sample apartments (for development)

**Implementation:**
```php
// database/seeders/AdminSeeder.php
Admin::create([
    'mobile_number' => '+201234567890',
    'password' => Hash::make('admin123'),
    'first_name' => 'Admin',
    'last_name' => 'User',
    'role' => 'admin',
    'status' => 'approved',
    // ... other required fields
]);
```

---

## Validation & Error Handling

### Validation: Form Request Classes

**Recommended:** Use Laravel Form Request classes for validation.

**Why:**
- Separation of concerns
- Reusable validation rules
- Cleaner controllers
- Laravel best practice

**Example:**
```php
// app/Http/Requests/StoreBookingRequest.php
class StoreBookingRequest extends FormRequest
{
    public function rules()
    {
        return [
            'apartment_id' => 'required|exists:apartments,id',
            'check_in_date' => 'required|date|after:today',
            'check_out_date' => 'required|date|after:check_in_date',
            // ...
        ];
    }
}
```

### Error Response Format: Consistent JSON Structure

**Required:** All error responses must follow this format:

```json
{
    "success": false,
    "message": "Error message here",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

**Success Response Format:**
```json
{
    "success": true,
    "message": "Success message (optional)",
    "data": {
        // Response data
    }
}
```

### Custom Exception Handlers: Worth It

**Recommended:** Create custom exception handler for consistent error responses.

**Why:**
- Consistent error format across all endpoints
- Centralized error handling
- Better API consumer experience
- Easier debugging

**Implementation:**
```php
// app/Exceptions/Handler.php
public function render($request, Throwable $exception)
{
    if ($request->expectsJson()) {
        return $this->handleApiException($request, $exception);
    }
    return parent::render($request, $exception);
}
```

---

## External Services Integration

### SMS Gateway: Tracker (Wait for User Input)

**Status:** Implementation will wait for user to provide SMS gateway details.

**Placeholder:**
- Create `SmsService` interface
- Implement mock service for development
- User will provide actual implementation later

### Push Notifications: FCM (Firebase Cloud Messaging)

**Required:** Integrate FCM for push notifications.

**Implementation:**
- Use `laravel-notification-channels/fcm` package or similar
- Create notification classes for each notification type
- Store FCM tokens in database (add `fcm_token` column to users table)

### File Storage: Local Storage

**For Development:** Use local storage (`storage/app/public`).

**Implementation:**
- Configure `config/filesystems.php` for local storage
- Use `Storage::disk('public')->put()` for file uploads
- Generate public URLs using `Storage::url()`

---

## Testing Strategy

### Unit/Feature Tests: Not Required

**Status:** No unit or feature tests required in initial implementation.

### API Testing: CURL Examples

**Included:** After implementing each endpoint, provide CURL command examples for testing.

**Format:**
```bash
# Example: Create Booking
curl -X POST http://localhost:8000/api/bookings \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "apartment_id": 1,
    "check_in_date": "2024-12-20",
    "check_out_date": "2024-12-25",
    "payment_method": "Cash"
  }'
```

---

## API Documentation: Optional

### Recommendation: Add Basic API Documentation

**Recommended:** Create a simple `API.md` file listing all endpoints with:
- HTTP method
- URL
- Request body (if any)
- Response format
- Authentication requirements

**Why:**
- Helps frontend developers
- Useful for testing
- No need for Swagger/OpenAPI initially (can add later)

---

## Implementation Workflow

### Module-by-Module Implementation

**CRITICAL:** Implement ONE module at a time. Do NOT start the next module until explicitly approved.

### Approval Process

**For Each Module:**
1. Before starting: Ask "Should I start implementing [Module Name]?"
2. Wait for explicit **YES** before proceeding
3. After completion: Ask "Module [Module Name] is complete. Should I proceed to [Next Module]?"
4. Wait for explicit **YES** before proceeding

**For Each Category of Files:**
After completing all files in a category (e.g., all Models, all Controllers, all Migrations), ask:

"Do you want me to commit all `<category>` files together now?"

Wait for explicit **YES** before committing.

### Commit Strategy: Grouped by Category

**Commit Categories:**
1. **Migrations** - All database migrations
2. **Models** - All Eloquent models
3. **Controllers** - All controllers
4. **Requests** - All form request classes
5. **Services** - All service classes
6. **Middlewares** - All middleware classes
7. **Policies** - All policy classes
8. **Events & Listeners** - All events and listeners
9. **Routes** - All route definitions
10. **Seeders** - All seeders
11. **Config** - Configuration files

**Commit Message Format:**
```
feat: Add [Category] for [Module Name]

- List of files added/modified
- Brief description of changes
```

---

## Error Handling Protocol

### If Any Error Occurs During Implementation:

1. **STOP immediately** - Do not continue
2. **Report the error:**
   - Exact error message
   - Stack trace (if available)
   - File and line number where error occurred
   - Context (what you were trying to do)
3. **Propose 2-3 fixing options** with pros/cons for each
4. **WAIT for explicit approval** before applying any fix

**Example Error Report:**
```
ERROR OCCURRED:
- File: app/Http/Controllers/BookingController.php
- Line: 45
- Error: "Call to undefined method BookingService::calculateRent()"
- Context: Trying to calculate rent for new booking

FIXING OPTIONS:
1. Create calculateRent() method in BookingService
   - Pros: Matches recommended approach
   - Cons: Need to implement calculation logic
2. Move calculation to controller
   - Pros: Quick fix
   - Cons: Violates separation of concerns
3. Use model accessor
   - Pros: Keeps logic with data
   - Cons: Less reusable

Which option should I proceed with?
```

---

## Absolute Rules

### DO NOT:
- ❌ Implement code without explicit approval
- ❌ Create files without being asked
- ❌ Modify the Laravel directory structure (except adding docs folder)
- ❌ Skip approval steps
- ❌ Invent fields, endpoints, or relationships
- ❌ Assume requirements not explicitly documented
- ❌ Create additional abstraction layers
- ❌ Skip error handling

### DO:
- ✅ Ask for approval before starting each module
- ✅ Ask for approval before committing
- ✅ Follow documentation exactly
- ✅ Report errors immediately
- ✅ Provide CURL examples for testing
- ✅ Use Laravel best practices
- ✅ Follow the implementation order
- ✅ Create clean, maintainable code

---

## Module Implementation Checklist

For each module, implement in this order:

1. **Migrations** (if new tables needed)
   - Create migration file
   - Define schema exactly as per ERD
   - Add indexes and foreign keys

2. **Models**
   - Create Eloquent model
   - Define relationships (hasMany, belongsTo, etc.)
   - Add fillable/guarded properties
   - Add casts (JSON, dates, etc.)

3. **Form Requests** (if needed)
   - Create request class
   - Define validation rules
   - Add custom validation messages

4. **Services** (if business logic needed)
   - Create service class
   - Implement business logic methods
   - Handle complex operations

5. **Policies** (if authorization needed)
   - Create policy class
   - Define authorization rules

6. **Controllers**
   - Create controller
   - Implement CRUD methods
   - Use Form Requests for validation
   - Use Services for business logic
   - Return consistent JSON responses

7. **Events & Listeners** (if needed)
   - Create event class
   - Create listener class
   - Register in EventServiceProvider

8. **Routes**
   - Define API routes
   - Apply middleware (auth, role)
   - Use resource routes where applicable

9. **Seeders** (if needed)
   - Create seeder class
   - Add test data

10. **Testing**
    - Provide CURL examples
    - Document endpoint usage

---

## Database Connection Setup

Before starting implementation, ensure:

1. **MySQL database created:**
   ```sql
   CREATE DATABASE findout_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Laravel .env configured:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=findout_db
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

3. **Run migrations when ready:**
   ```bash
   php artisan migrate
   ```

---

## Ready to Begin

**Before starting implementation, confirm:**

1. ✅ Laravel project initialized
2. ✅ `docs/` folder created in backend directory with all documentation files
3. ✅ Database created and configured
4. ✅ All documentation reviewed
5. ✅ Implementation order understood
6. ✅ Approval process understood

**First Step:**
Ask: "Should I start implementing Module 1: Foundation & Authentication (Migrations, Models, Auth System)?"

**Wait for explicit YES before proceeding.**

---

## Notes

- All API endpoints must return JSON
- All dates must be in ISO 8601 format (YYYY-MM-DD or YYYY-MM-DDTHH:mm:ssZ)
- All monetary values must use DECIMAL(10,2) and be in smallest currency unit
- File uploads must validate file type and size (max 5MB per image)
- All endpoints requiring authentication must validate JWT token
- All admin endpoints must verify admin role
- All user endpoints must verify user is approved (status = 'approved')

---

**End of Implementation Prompt**

**This prompt is complete and ready for use. Follow it exactly as written.**

