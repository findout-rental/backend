# API Endpoints Summary

Quick reference guide for all API endpoints organized by user role.

## Shared Endpoints

Endpoints accessible by all authenticated users (Admin, Owner, Tenant).

### Authentication

-   `POST /api/auth/send-otp` - Send OTP code
-   `POST /api/auth/verify-otp` - Verify OTP code
-   `POST /api/auth/register` - Register new user
-   `POST /api/auth/login` - Login user
-   `POST /api/auth/logout` - Logout user
-   `GET /api/auth/me` - Get current user

### Profile Management

-   `GET /api/profile` - Get user profile
-   `PUT /api/profile` - Update profile
-   `POST /api/profile/upload-photo` - Upload profile photo
-   `PUT /api/profile/language` - Update language preference
-   `GET /api/users/{userId}` - View another user's profile (public information only)

### Messaging

-   `GET /api/messages` - List all conversations - with last message and unread count
-   `GET /api/messages/{user_id}` - Get conversation with specific user - auto-marks as read
-   `POST /api/messages/ws` - HTTP endpoint for WebSocket message handling (send message, mark as read, typing indicators)
-   `POST /api/messages/upload-attachment` - Upload file attachment (HTTP only)

**Note:** Actual WebSocket connections are made to Laravel Reverb server (typically `ws://localhost:8080`). The `POST /api/messages/ws` endpoint is an HTTP bridge/handler. WebSocket connections cannot be tested via Postman collections - use a WebSocket client instead.

### WebSocket Endpoints

**Connection:** `ws://localhost:8080/app/{app_key}` (WebSocket, not HTTP)

**Channels:**

-   `private-user.{userId}` - Private channel for each user (subscribe to receive real-time events)

**Events:**

-   `message.sent` - Broadcasted to recipient when a new message is received
-   `message.read` - Broadcasted to sender when their message is read
-   `user.typing` - Broadcasted to recipient when sender is typing/stopped typing

**Note:** See detailed WebSocket documentation in `endpoints.md` (sections 42-45) for connection examples, event data structures, and implementation details.

---

## Admin Endpoints

Endpoints accessible only by users with the `admin` role.

### User Management

-   `GET /api/admin/users` - List all users
-   `PUT /api/admin/registrations/{user_id}/approve` - Approve user registration

### Apartment Management

-   `GET /api/admin/apartments` - List all apartments - with filtering, searching, sorting, and pagination

### Booking Management

-   `GET /api/admin/bookings` - List all bookings - with filtering, searching, sorting, and pagination

---

## Owner Endpoints

Endpoints accessible only by users with the `owner` role.

### Apartment Management

-   `GET /api/owner/apartments` - List owner's apartments
-   `POST /api/owner/apartments` - Create apartment
-   `GET /api/owner/apartments/{apartment_id}` - Get apartment details
-   `PUT /api/owner/apartments/{apartment_id}` - Update apartment
-   `DELETE /api/owner/apartments/{apartment_id}` - Delete apartment
-   `POST /api/owner/apartments/upload-photo` - Upload apartment photo

### Booking Management

-   `GET /api/owner/bookings` - List bookings for owner's apartments - with status filtering
-   `GET /api/owner/bookings/{id}` - Get booking details
-   `PUT /api/owner/bookings/{id}/approve` - Approve booking request
-   `PUT /api/owner/bookings/{id}/reject` - Reject booking (with full refund)
-   `PUT /api/owner/bookings/{id}/approve-modification` - Approve modification request
-   `PUT /api/owner/bookings/{id}/reject-modification` - Reject modification request

---

## Tenant Endpoints

Endpoints accessible only by users with the `tenant` role.

### Apartment Browsing

-   `GET /api/apartments` - List all apartments (tenant view) - with filtering, searching, sorting, and pagination
-   `GET /api/apartments/{apartment_id}` - Get apartment details (tenant view) - includes owner info and reviews

### Booking Management

-   `GET /api/bookings` - List tenant bookings - with status filtering (current, past, cancelled)
-   `POST /api/bookings` - Create booking request - checks conflicts, calculates rent, processes payment
-   `GET /api/bookings/{id}` - Get booking details
-   `PUT /api/bookings/{id}` - Modify booking (creates modification request)
-   `POST /api/bookings/{id}/cancel` - Cancel booking (with 80% refund)

### Rating Management

-   `POST /api/ratings` - Create rating and review - only for completed bookings after check-out date

### Favorites Management

-   `GET /api/favorites` - List favorite apartments - with pagination
-   `POST /api/favorites` - Add apartment to favorites
-   `DELETE /api/favorites/{apartment_id}` - Remove apartment from favorites

---

## Endpoint Count Summary

-   **Shared Endpoints:** 15 (6 authentication, 5 profile, 4 messaging)
-   **Admin Endpoints:** 4 (2 user management, 1 apartment, 1 booking)
-   **Owner Endpoints:** 12 (6 apartment management, 6 booking management)
-   **Tenant Endpoints:** 11 (2 apartment browsing, 5 booking management, 1 rating, 3 favorites)
-   **Total Implemented:** 42
