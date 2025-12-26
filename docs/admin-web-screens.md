# Admin Web Application - Screen Specifications

## FindOut - Apartment Rental Application

**Version:** 1.0  
**Date:** December 2024  
**Platform:** Flutter Web (Browser-based)  
**Target Users:** System Administrators  
**Minimum Screen Width:** 1024px (Desktop/Laptop)

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Navigation Architecture](#2-navigation-architecture)
3. [Authentication](#3-authentication)
4. [Dashboard](#4-dashboard)
5. [Registration Management](#5-registration-management)
6. [User Management](#6-user-management)
7. [Content Overview](#7-content-overview)
8. [Settings](#8-settings)
9. [Common UI Patterns](#9-common-ui-patterns)

---

## 1. Introduction

### 1.1 Purpose

This document defines all screens, navigation flows, and UI components for the FindOut Admin Web Application. It serves as a comprehensive reference for:

- **Flutter Web developers** implementing the admin panel
- **Backend developers** understanding required admin API endpoints
- **UI/UX designers** creating visual designs for web interface
- **QA engineers** testing admin workflows
- **System administrators** understanding available features

### 1.2 Scope

The Admin Web Application provides system administrators with tools to:

- **Review and approve/reject user registrations** (tenants and owners)
- **Manage users** (view all users, delete users)
- **View system statistics** (dashboard with key metrics)
- **Monitor platform activity** (users, apartments, bookings)
- **Manage user balances** (add money to tenants, withdraw from owners)
- **View transaction history** (audit trail of all balance operations)
- **Configure settings** (language, theme)

**Access:** Desktop/laptop browsers only (minimum 1024px width)

### 1.3 Design Principles

- **Material Design** for web (Flutter Web)
- **Desktop-optimized layouts** (not mobile-responsive)
- **Professional admin interface** with data tables and forms
- **RTL/LTR support** for Arabic and English languages
- **Dark/Light theme** support throughout
- **Accessibility** considerations for keyboard navigation

### 1.4 Technology Stack

- **Frontend:** Flutter Web (Dart)
- **Browsers:** Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Authentication:** Email + Password with JWT tokens
- **API:** RESTful API (same Laravel backend as mobile app)
- **Minimum Resolution:** 1024x768px

### 1.5 Related Documents

- [SRS.md](SRS.md) - Software Requirements Specification
- [ERD.md](ERD.md) - Database Design
- [customer-app-screens.md](customer-app-screens.md) - Mobile App Screens

---

## 2. Navigation Architecture

### 2.1 Layout Structure

The admin web application uses a **sidebar navigation layout**:

```
┌─────────────────────────────────────────────────────┐
│  Top App Bar (Logo, Search, Notifications, Profile) │
├──────────┬──────────────────────────────────────────┤
│          │                                           │
│          │                                           │
│ Sidebar  │        Main Content Area                  │
│   Nav    │                                           │
│          │                                           │
│          │                                           │
└──────────┴──────────────────────────────────────────┘
```

### 2.2 Sidebar Navigation

**Left Sidebar (Collapsible):**

| Icon | Menu Item | Badge | Description |
|------|-----------|-------|-------------|
| 📊 | Dashboard | - | System overview and statistics |
| ⏳ | Pending Registrations | Red badge with count | User registrations awaiting approval |
| 👥 | All Users | - | View and manage all users |
| 🏠 | All Apartments | - | View all apartment listings |
| 📅 | All Bookings | - | View all bookings system-wide |
| ⚙️ | Settings | - | App settings (language, theme) |

**Sidebar Features:**
- Collapsible (toggle button at bottom)
- Active item highlighted
- Smooth expand/collapse animation
- Minimum width (collapsed): 60px
- Maximum width (expanded): 240px

### 2.3 Top App Bar

**Components (left to right):**
1. **Logo/Brand**: FindOut Admin (left)
2. **Search Bar**: Global search (center) - Future feature
3. **Notifications Bell**: Icon with badge count (right)
4. **Profile Menu**: Admin photo + name dropdown (right)
   - View Profile
   - Settings
   - Logout

### 2.4 Navigation Behavior

- **Single Page Application (SPA)**: Content area updates without full page reload
- **URL Routing**: Browser URL reflects current screen
- **Breadcrumbs**: On detail pages (e.g., Dashboard > Users > User Detail)
- **Back Button**: Browser back button works correctly
- **Keyboard Navigation**: Tab, Arrow keys, Enter/Space for actions

### 2.5 Responsive Behavior

**Desktop Only (1024px+ width):**
- Optimized for desktop/laptop screens
- Layout does not adapt below 1024px
- Mobile/tablet users see message: "This admin panel requires a desktop browser. Please access from a computer with minimum screen width of 1024px."

---

## 3. Authentication

### 3.1 Overview

Admin authentication uses **pre-created accounts** (no self-registration):
- Admin accounts are created manually by super admin or database
- Admins log in with **Mobile Number + Password**
- No OTP verification required
- JWT token-based session management

---

### 3.1.1 Login Screen

**Screen ID**: `ADMIN-AUTH-001`

#### Purpose
Allow system administrators to authenticate and access the admin panel.

#### User Entry Points
- Direct access via admin web URL
- After logout
- When session expires

#### UI Components

**Full-Screen Layout:**
- Split-screen design (50/50)

**Left Panel (Branding):**
- FindOut logo (large)
- Headline: "Admin Dashboard"
- Subheadline: "Manage your apartment rental platform"
- Background: Gradient or branded image
- App version number (bottom)

**Right Panel (Login Form):**
- Card container (centered, max-width 400px)
- **Headline**: "Welcome Back"
- **Subheadline**: "Sign in to your admin account"

**Login Form:**
- **Mobile Number Input**:
  - Label: "Mobile Number"
  - Type: Tel
  - Placeholder: "+201234567890"
  - Validation: Valid mobile number format
  - Auto-focus on page load
- **Password Input**:
  - Label: "Password"
  - Type: Password (obscured)
  - Show/Hide password toggle icon
  - Placeholder: "Enter your password"
- **Remember Me Checkbox** (optional):
  - ☐ "Keep me signed in"
- **Forgot Password Link**:
  - Text link: "Forgot your password?"
  - Positioned below password field

**Action Button:**
- Primary button: "Sign In" (full width)
- Enabled when both fields valid

**Footer:**
- Language selector: English | العربية
- Privacy Policy link
- Terms of Service link

#### User Actions
- Enter mobile number and password
- Tap "Sign In" →
  - Show loading state
  - Authenticate credentials
  - On success: Navigate to Dashboard
  - On error: Show error message
- Tap "Forgot Password" → Navigate to Password Reset (future feature)
- Change language → Interface updates to selected language
- Press Enter key → Submit form

#### Empty States
- N/A (login form)

#### Loading States
**Sign In Button:**
- Spinner replaces button text
- Button disabled during authentication

#### Error States
- **Invalid Credentials**: "Invalid mobile number or password. Please try again."
  - Display below form, red text
- **Account Locked**: "Your account has been locked. Please contact support."
- **Network Error**: "Unable to connect. Please check your internet connection."
- **Server Error**: "Something went wrong. Please try again later."

#### Validation Rules
- Mobile Number: Required, valid mobile number format
- Password: Required, not empty
- Both fields must be filled to enable Sign In button

#### Related Entities
- `users` table (authenticate admin user with role = 'admin')

#### API Endpoints
- `POST /api/admin/login`
  - Request:
    ```json
    {
      "mobile_number": "+201234567890",
      "password": "password123"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "token": "jwt_token_here",
      "admin": {
        "id": 1,
        "mobile_number": "+201234567890",
        "first_name": "Admin",
        "last_name": "User",
        "personal_photo": "url",
        "role": "admin",
        "language_preference": "en",
        "created_at": "2024-01-01T00:00:00Z"
      }
    }
    ```

#### Notes
- No self-registration - admins are created by system
- JWT token stored securely in browser (localStorage or sessionStorage)
- Session expires after 24 hours of inactivity
- "Remember Me" extends session to 30 days
- All admin API calls require valid JWT token
- Consider 2FA implementation in future for enhanced security

---

### 3.1.2 Pending Approval Screen (If Admin Account Needs Approval)

**Screen ID**: `ADMIN-AUTH-002`

#### Purpose
Inform newly created admin that their account is pending super admin approval (optional screen if multi-level admin system exists).

#### User Entry Points
- After successful login (if admin status = 'pending')

#### UI Components

**Full-Screen Centered Message:**
- Icon: ⏳ (large, centered)
- Headline: "Account Pending Approval"
- Message: "Your admin account is under review. You'll be notified when approved."
- Description: "Please contact the system administrator if you have questions."

**Action Button:**
- Primary button: "Logout"

#### User Actions
- Tap "Logout" → Return to Login Screen

#### Notes
- **Optional feature** if implementing hierarchical admin roles
- Most implementations will have all admins pre-approved

---

## 4. Dashboard

### 4.1.1 Dashboard Screen

**Screen ID**: `ADMIN-DASH-001`

#### Purpose
Provide overview of platform statistics and quick access to key metrics. First screen after login.

#### User Entry Points
- After successful login
- From Sidebar → Tap "Dashboard"
- From Top App Bar → Tap logo

#### UI Components

**Top App Bar:**
- Logo (left)
- Search bar (center) - placeholder for future
- Notifications bell icon with badge (right)
- Admin profile dropdown (right)

**Sidebar:**
- All menu items visible
- "Dashboard" highlighted/active

**Main Content Area:**

**Page Header:**
- Headline: "Dashboard"
- Subheadline: "Overview of your platform"
- Date/Time: "Last updated: Dec 15, 2024 - 14:30"

**Statistics Cards (Grid: 2x2):**

**Card 1: Total Users**
- Icon: 👥
- Large number: "1,234"
- Label: "Total Users"
- Breakdown: "850 Tenants | 384 Owners"

**Card 2: Total Apartments**
- Icon: 🏠
- Large number: "456"
- Label: "Total Apartments"
- Breakdown: "423 Active | 33 Inactive"

**Card 3: Pending Registrations**
- Icon: ⏳
- Large number: "12"
- Label: "Pending Registrations"
- Status badge: "Requires Action" (orange/red)
- Action link: "Review Now →"

**Card 4: Total Bookings**
- Icon: 📅
- Large number: "2,890"
- Label: "Total Bookings"
- Breakdown: "145 Active | 2,745 Completed"

**Quick Actions Section:**
- Section header: "Quick Actions"
- Button cards:
  - "Review Pending Registrations" (primary) → Navigate to Pending Registrations
  - "View All Users" → Navigate to All Users
  - "View All Apartments" → Navigate to All Apartments
  - "View All Bookings" → Navigate to All Bookings

**Recent Activity Section (Optional - Future):**
- Section header: "Recent Activity"
- Timeline/list of recent events:
  - "New user registered - John Doe (Tenant)"
  - "Apartment published - New Cairo Apartment"
  - "Booking completed - Booking #1234"
- "View All Activity" link

#### User Actions
- Tap any statistic card → Navigate to related screen
  - Total Users → All Users
  - Total Apartments → All Apartments
  - Pending Registrations → Pending Registrations (with count badge)
  - Total Bookings → All Bookings
- Tap "Review Now" on Pending card → Navigate to Pending Registrations
- Tap quick action buttons → Navigate to respective screens
- Tap notifications bell → Open notifications dropdown
- Tap profile menu → Show dropdown (Profile, Settings, Logout)

#### Empty States
**No Pending Registrations:**
- Card shows: "0 Pending Registrations"
- Message: "All registrations reviewed"
- No action link

#### Loading States
**Initial Load:**
- Skeleton loaders for all 4 statistic cards
- Shimmer effect

**Data Refresh:**
- Subtle spinner in page header while refreshing
- Auto-refresh every 60 seconds (optional)

#### Error States
- **Load Error**: "Unable to load dashboard data. Please try again."
  - Retry button shown
- Individual card errors: Show "N/A" with error icon

#### Related Entities
- `users` table (count users by role, count pending)
- `apartments` table (count apartments by status)
- `bookings` table (count bookings by status)

#### API Endpoints
- `GET /api/admin/dashboard/statistics`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "users": {
          "total": 1234,
          "tenants": 850,
          "owners": 384,
          "pending": 12
        },
        "apartments": {
          "total": 456,
          "active": 423,
          "inactive": 33
        },
        "bookings": {
          "total": 2890,
          "active": 145,
          "completed": 2745
        },
        "last_updated": "2024-12-15T14:30:00Z"
      }
    }
    ```

#### Notes
- Statistics update in real-time or refresh on page load
- Pending registrations badge prominently displayed
- Consider adding charts/graphs in future versions
- Dashboard is the default landing page after login

---

## 5. Registration Management

### 5.1 Overview

Registration management allows admins to review and approve/reject user registration requests. This is a critical workflow as users cannot access the app until approved.

**Key Requirements (from SRS.md REQ-ADM-001):**
- View all pending registration requests
- View user details before approving (personal info, ID photo)
- Approve registration requests
- Reject registration requests
- Send notification to user upon approval/rejection

---

### 5.1.1 Pending Registrations List Screen

**Screen ID**: `ADMIN-REG-001`

#### Purpose
Display all pending user registration requests awaiting admin approval in a sortable, filterable list.

#### User Entry Points
- From Sidebar → Tap "Pending Registrations"
- From Dashboard → Tap "Pending Registrations" card
- From Dashboard → Tap "Review Now" link on Pending card

#### UI Components

**Page Header:**
- Breadcrumb: Dashboard > Pending Registrations
- Headline: "Pending Registrations"
- Badge: Count of pending requests (e.g., "12 Pending")
- Description: "Review and approve user registration requests"

**Toolbar (Top of Content):**
- **Search Bar** (left):
  - Placeholder: "Search by name or mobile number..."
  - Icon: 🔍
  - Real-time search filter
- **Filter Dropdown** (center):
  - Label: "Filter by Role"
  - Options: All | Tenants | Owners
- **Sort Dropdown** (center-right):
  - Label: "Sort by"
  - Options: Newest First | Oldest First | Name (A-Z)
- **Refresh Button** (right):
  - Icon: ↻
  - Tooltip: "Refresh list"

**Data Table:**

**Table Columns:**
1. **Photo** - Circular avatar (40x40px)
2. **Name** - First and Last name
3. **Mobile Number** - Formatted phone number
4. **Role** - Badge: "Tenant" (blue) | "Owner" (green)
5. **Registration Date** - Formatted date (e.g., "Dec 15, 2024")
6. **Status** - Badge: "Pending" (orange)
7. **Actions** - Buttons

**Table Features:**
- **Sortable columns**: Click column header to sort
- **Hover effect**: Row highlights on hover
- **Row selection**: Click row to view details
- **Pagination**: 20 rows per page
  - Pagination controls at bottom: "Showing 1-20 of 45"
  - Navigation: First | Previous | 1 2 3 | Next | Last

**Action Buttons (per row):**
- **View Details** button (primary) → Opens detail page
- **Quick Approve** button (green icon) → Quick approval dialog
- **Quick Reject** button (red icon) → Quick rejection dialog

**Empty State (if no pending):**
- Icon: ✅ (checkmark)
- Message: "No Pending Registrations"
- Description: "All registration requests have been reviewed"

#### User Actions
- Type in search bar → Filter results in real-time
- Select role filter → Show only selected role
- Select sort option → Re-sort table
- Click refresh button → Reload data
- Click column header → Sort by that column (ascending/descending)
- Click table row → Navigate to Registration Detail Screen (ADMIN-REG-002)
- Click "View Details" → Navigate to Registration Detail Screen
- Click Quick Approve → Show quick approval confirmation dialog
- Click Quick Reject → Show quick rejection dialog with reason field
- Click pagination controls → Load next/previous page

#### Empty States
**No Pending Registrations:**
- Icon: ✅
- Message: "All caught up!"
- Description: "There are no pending registration requests at this time"

**No Search Results:**
- Icon: 🔍
- Message: "No results found"
- Description: "Try adjusting your search or filters"
- Action: "Clear Filters" button

#### Loading States
**Initial Load:**
- Skeleton loader for table rows (show 10 skeleton rows)
- Shimmer effect

**Search/Filter:**
- Brief spinner overlay on table while filtering

**Refresh:**
- Spinner icon replaces refresh button icon

#### Error States
- **Load Error**: "Unable to load pending registrations. Please try again."
  - Retry button shown above table
- **Action Error**: Toast notification "Unable to process request. Please try again."

#### Quick Approval Dialog
**When clicking Quick Approve:**
- Title: "Approve Registration?"
- User summary: Name, Role, Mobile
- Message: "The user will be notified and can access the app"
- Actions:
  - "Cancel" (secondary)
  - "Approve" (primary, green)

#### Quick Rejection Dialog
**When clicking Quick Reject:**
- Title: "Reject Registration?"
- User summary: Name, Role, Mobile
- Rejection reason field (optional):
  - Label: "Reason for rejection (optional but recommended)"
  - Placeholder: "Enter reason..."
  - Multi-line text area
  - Max 500 characters
- Message: "The user will be notified"
- Actions:
  - "Cancel" (secondary)
  - "Reject" (destructive, red)

#### Related Entities
- `users` table (fetch users with status = 'pending')

#### API Endpoints
- `GET /api/admin/registrations/pending`
  - Query params: 
    - `search` (string)
    - `role` (string: tenant, owner)
    - `sort` (string: newest, oldest, name)
    - `page` (number)
    - `per_page` (number, default 20)
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "registrations": [
          {
            "id": 10,
            "mobile_number": "+201234567890",
            "first_name": "John",
            "last_name": "Doe",
            "personal_photo": "url",
            "role": "tenant",
            "status": "pending",
            "created_at": "2024-12-15T10:00:00Z"
          }
        ],
        "pagination": {
          "current_page": 1,
          "total_pages": 3,
          "total_items": 45,
          "per_page": 20
        }
      }
    }
    ```

- `PUT /api/admin/registrations/{user_id}/approve`
  - Response:
    ```json
    {
      "success": true,
      "message": "User approved successfully"
    }
    ```

- `PUT /api/admin/registrations/{user_id}/reject`
  - Request:
    ```json
    {
      "rejection_reason": "Incomplete documentation"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "User rejected successfully"
    }
    ```

#### Notes
- Quick actions allow fast processing without leaving list
- Detailed review recommended for thorough verification
- Push notification sent to user upon approval/rejection
- Approved users can immediately log in
- Rejected users cannot access the app
- Consider adding bulk actions in future (approve/reject multiple)

---

### 5.1.2 Registration Detail Screen

**Screen ID**: `ADMIN-REG-002`

#### Purpose
Display comprehensive user registration details with large photo previews for thorough review before approval/rejection.

#### User Entry Points
- From Pending Registrations List → Click row or "View Details" button

#### UI Components

**Page Layout:**
- Full-page detail view (not modal)
- Back to list button (top-left)

**Page Header:**
- Breadcrumb: Dashboard > Pending Registrations > User Name
- Headline: User full name
- Status badge: "Pending Review" (orange)

**Main Content (Two-Column Layout):**

**Left Column (60%):**

**Section 1: Personal Information Card**
- **Personal Photo**:
  - Large display (200x200px)
  - Click to view full-screen
- **Personal Details**:
  - **Full Name**: John Doe
  - **Mobile Number**: +20 123 456 7890
  - **Date of Birth**: December 15, 1990
  - **Age**: 34 years old (calculated)
  - **Role**: Tenant/Owner (with badge)
  - **Registration Date**: December 15, 2024 at 10:30 AM

**Section 2: ID Verification Card**
- **ID Photo**:
  - Large display (400x300px or full width)
  - Click to view full-screen
  - Zoom controls overlay
- **Verification Checklist** (visual indicators):
  - ✓ Photo uploaded
  - ✓ Image quality sufficient
  - ⚠️ Manual review required

**Right Column (40%):**

**Admin Actions Panel (Sticky):**

**Review Guidelines Box:**
- Icon: ℹ️
- Title: "Review Guidelines"
- Checklist:
  - ☐ Verify personal photo matches ID photo
  - ☐ Check ID photo is clear and readable
  - ☐ Confirm user is 18+ years old
  - ☐ Ensure all information is complete

**Decision Buttons:**
- **Approve Button** (large, primary, green):
  - Text: "✓ Approve Registration"
  - Full width
- **Reject Button** (large, destructive, red):
  - Text: "✕ Reject Registration"
  - Full width
  - Margin top

**Additional Actions:**
- **Contact User** button (optional future feature)
- **Report Suspicious** button (optional)

**Activity Log (Optional):**
- Shows registration timeline:
  - "Registered on Dec 15, 2024 at 10:30 AM"
  - "Pending review"

**Photo Viewer Modal:**
- Full-screen overlay when clicking any photo
- Large photo display
- Zoom in/out controls
- Close button (X)
- Navigation arrows if multiple photos

#### User Actions
- View all user information
- Click personal photo → Open full-screen viewer
- Click ID photo → Open full-screen viewer
- Zoom in/out on photos → Inspect details
- Click "Approve" →
  - Show approval confirmation dialog
  - On confirm: Approve user, send notification
  - Navigate back to list with success message
- Click "Reject" →
  - Show rejection dialog with reason field
  - On confirm: Reject user, send notification
  - Navigate back to list with success message
- Click back button → Return to Pending Registrations List

#### Empty States
- N/A (always displays user data)

#### Loading States
**Initial Load:**
- Skeleton loader for entire page
- Photos load with blur-up effect

**Action Processing:**
- Full-screen overlay: "Processing..."
- Spinner with message

#### Error States
- **Load Error**: "Unable to load registration details"
  - Retry button
- **Photo Load Error**: Placeholder with "Failed to load image"
- **Approve Error**: Dialog "Unable to approve user. Please try again."
- **Reject Error**: Dialog "Unable to reject user. Please try again."

#### Approval Confirmation Dialog
- Title: "Approve This Registration?"
- User summary:
  - Photo, name, role, mobile
- Message: "This user will be able to access the application immediately"
- Checklist confirmation:
  - ☑ "I have verified all information is accurate"
- Actions:
  - "Cancel"
  - "Confirm Approval" (primary, green)

#### Rejection Dialog
- Title: "Reject This Registration?"
- User summary: Photo, name, role
- **Reason for Rejection** (required):
  - Multi-line text area
  - Placeholder: "Provide a reason (user will be notified)..."
  - Min 10 characters recommended
  - Max 500 characters
- Common reasons (quick select chips):
  - "Incomplete information"
  - "Invalid ID document"
  - "Age verification failed"
  - "Suspicious activity"
- Message: "The user will be notified of this decision"
- Actions:
  - "Cancel"
  - "Confirm Rejection" (destructive, red)

#### Related Entities
- `users` table (fetch user details, update status)
- `notifications` table (send notification to user)

#### API Endpoints
- `GET /api/admin/registrations/{user_id}`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "id": 10,
        "mobile_number": "+201234567890",
        "first_name": "John",
        "last_name": "Doe",
        "personal_photo": "url",
        "date_of_birth": "1990-12-15",
        "id_photo": "url",
        "role": "tenant",
        "status": "pending",
        "created_at": "2024-12-15T10:30:00Z"
      }
    }
    ```

- `PUT /api/admin/registrations/{user_id}/approve`
  - Same as list screen

- `PUT /api/admin/registrations/{user_id}/reject`
  - Same as list screen

#### Notes
- Large photo previews essential for verification
- ID photo must be clearly visible for thorough review
- Age verification calculated automatically from date of birth
- Approval is immediate - user can log in right away
- Rejection reason helps user understand decision
- Consider adding notes field for internal admin comments
- Photo zoom functionality improves verification accuracy

---

## 6. User Management

### 6.1 Overview

User management allows admins to view all registered users and delete users when necessary. Uses master-detail layout for efficient user management.

**Key Requirements (from SRS.md REQ-ADM-002):**
- View all users (approved, pending, rejected)
- View user details before deleting
- Delete users
- Prevent deletion of users with active bookings
- Display warning before deletion

---

### 6.1.1 All Users Screen (Master-Detail Layout)

**Screen ID**: `ADMIN-USER-001`

#### Purpose
Display all users in the system with master-detail layout for viewing and managing users.

#### User Entry Points
- From Sidebar → Tap "All Users"
- From Dashboard → Tap "Total Users" card or "View All Users" button

#### UI Components

**Page Layout: Master-Detail (Split View)**

```
┌────────────────────────────────────────────────────┐
│ Page Header (Full Width)                           │
├──────────────────┬─────────────────────────────────┤
│                  │                                  │
│  User List       │   User Detail Panel              │
│  (Master)        │   (Detail)                       │
│  40% width       │   60% width                      │
│                  │                                  │
│                  │                                  │
└──────────────────┴─────────────────────────────────┘
```

**Page Header (Full Width):**
- Breadcrumb: Dashboard > All Users
- Headline: "All Users"
- Statistics: "Total: 1,234 users (850 Tenants, 384 Owners, 12 Pending)"

**Left Panel - User List (Master):**

**Toolbar:**
- **Search Bar**:
  - Placeholder: "Search users..."
  - Icon: 🔍
- **Filter Tabs** (Horizontal):
  - All (1,234)
  - Approved (1,222)
  - Pending (12)
  - Rejected (0)
- **Role Filter Dropdown**:
  - Options: All Roles | Tenants | Owners
- **Sort Dropdown**:
  - Options: Name (A-Z) | Name (Z-A) | Newest | Oldest

**User List (Scrollable):**
- Vertical scrolling list
- Each user card (compact):
  - **Photo** (left, circular, 48x48px)
  - **Name** (bold)
  - **Role Badge**: Tenant/Owner
  - **Status Badge**: Approved/Pending/Rejected
  - **Mobile Number** (small text)
  - **Registration Date** (small text, gray)
  - **Active Indicator**: Selected user highlighted
- Load more on scroll (pagination: 50 per load)
- Click card → Load details in right panel

**Right Panel - User Detail:**

**Initial State (No User Selected):**
- Icon: 👤
- Message: "Select a user to view details"
- Description: "Choose a user from the list"

**User Selected State:**

**Detail Header:**
- Large profile photo (120x120px, centered)
- Full name (large heading)
- Role badge + Status badge
- Member since date

**Information Sections:**

**Section 1: Personal Information**
- Label: "Personal Information"
- **Mobile Number**: +20 123 456 7890
- **Date of Birth**: December 15, 1990 (34 years old)
- **Role**: Tenant/Owner
- **Status**: Approved/Pending/Rejected
- **Registration Date**: December 15, 2024

**Section 2: ID Verification**
- Label: "Identity Verification"
- **ID Photo**: Thumbnail with click to enlarge
- **Verification Status**: Verified by Admin on [date]

**Section 3: Account Balance**
- Label: "Account Balance"
- **Current Balance**: "EGP 2,500.00" (large, prominent display)
- **Balance Actions**:
  - **Add Money** button (for tenants) → Opens Add Money Dialog
  - **Withdraw Money** button (for owners) → Opens Withdraw Money Dialog
  - **View Transaction History** link → Opens Transaction History Panel

**Section 4: Activity Summary**
- Label: "Activity Summary"

**For Tenants:**
- Total Bookings: 12
- Active Bookings: 2
- Completed Bookings: 10
- Reviews Given: 8

**For Owners:**
- Total Apartments: 5
- Active Apartments: 4
- Total Bookings Received: 45
- Average Rating: ⭐ 4.5

**Section 5: Account Actions**

**Action Buttons:**
- **View Full Profile** (secondary) - Future feature
- **Send Message** (secondary) - Future feature
- **Delete User** (destructive, red)
  - Disabled if user has active bookings
  - Tooltip: "Cannot delete users with active bookings"

**Active Bookings Warning (if applicable):**
- ⚠️ "This user has 2 active bookings. Deletion is not allowed."

#### User Actions
- Type in search → Filter user list
- Select filter tab → Show users by status
- Select role filter → Show users by role
- Select sort → Re-sort list
- Click user card → Load details in right panel (highlight selected)
- Click ID photo thumbnail → Open full-screen viewer
- Click "Add Money" (tenant) → Opens Add Money Dialog
- Click "Withdraw Money" (owner) → Opens Withdraw Money Dialog
- Click "View Transaction History" → Opens Transaction History Panel
- Click "Delete User" →
  - If active bookings: Show error tooltip
  - If no active bookings: Show delete confirmation dialog
- Scroll user list → Load more users (pagination)

#### Empty States

**No Users (unlikely):**
- Icon: 👥
- Message: "No users found"
- Description: "No users have registered yet"

**No Search Results:**
- Icon: 🔍
- Message: "No users match your search"
- Action: "Clear search" link

**No User Selected:**
- Icon: 👤
- Message: "Select a user to view details"

#### Loading States
**Initial Load:**
- Skeleton loaders for user list (10 cards)
- Empty detail panel with message

**Loading Detail:**
- Skeleton loader in detail panel when selecting new user

**Delete Processing:**
- Full-screen overlay: "Deleting user..."

#### Error States
- **Load Error**: "Unable to load users. Please try again."
- **Detail Load Error**: "Unable to load user details"
- **Delete Error**: Dialog "Unable to delete user. Please try again."

#### Delete Confirmation Dialog
**When clicking Delete User:**
- Title: "Delete User Account?"
- User summary: Photo, name, role
- Warning: "⚠️ This action cannot be undone"
- Message: "All user data will be permanently deleted"
- **Confirmation Input**:
  - Label: "Type DELETE to confirm"
  - Input field (must match exactly)
- Actions:
  - "Cancel"
  - "Delete Permanently" (destructive, red, disabled until "DELETE" typed)

#### Related Entities
- `users` table (fetch all users, delete user)
- `bookings` table (check for active bookings)
- `apartments` table (count owner's apartments)

#### API Endpoints
- `GET /api/admin/users`
  - Query params:
    - `search` (string)
    - `status` (string: all, approved, pending, rejected)
    - `role` (string: all, tenant, owner)
    - `sort` (string: name_asc, name_desc, newest, oldest)
    - `page` (number)
    - `per_page` (number, default 50)
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "users": [
          {
            "id": 10,
            "mobile_number": "+201234567890",
            "first_name": "John",
            "last_name": "Doe",
            "personal_photo": "url",
            "role": "tenant",
            "status": "approved",
            "created_at": "2024-12-15T10:00:00Z"
          }
        ],
        "statistics": {
          "total": 1234,
          "approved": 1222,
          "pending": 12,
          "rejected": 0
        },
        "pagination": {
          "current_page": 1,
          "has_more": true
        }
      }
    }
    ```

- `GET /api/admin/users/{user_id}`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "user": {
          "id": 10,
          "mobile_number": "+201234567890",
          "first_name": "John",
          "last_name": "Doe",
          "personal_photo": "url",
          "date_of_birth": "1990-12-15",
          "id_photo": "url",
          "role": "tenant",
          "status": "approved",
          "created_at": "2024-12-15T10:00:00Z"
        },
        "balance": 2500.00,
        "statistics": {
          "total_bookings": 12,
          "active_bookings": 2,
          "completed_bookings": 10,
          "reviews_given": 8,
          "total_apartments": 0,
          "average_rating": null
        },
        "can_delete": false,
        "delete_blocked_reason": "User has active bookings"
      }
    }
    ```

- `DELETE /api/admin/users/{user_id}`
  - Response:
    ```json
    {
      "success": true,
      "message": "User deleted successfully"
    }
    ```
  - Error Response (if active bookings):
    ```json
    {
      "success": false,
      "error": "Cannot delete user with active bookings"
    }
    ```

#### Add Money Dialog

**When clicking "Add Money" (for tenants):**
- Modal dialog overlay
- Title: "Add Money to Tenant Balance"
- User info: Photo, name, current balance displayed
- **Amount Input**:
  - Label: "Amount to Add"
  - Currency: EGP
  - Number input (min: 0.01, max: 999999.99)
  - Placeholder: "0.00"
- **Description/Notes** (optional):
  - Text area
  - Label: "Notes (optional)"
  - Placeholder: "e.g., Cash deposit from tenant"
  - Max 255 characters
- **Preview**:
  - Current Balance: EGP 2,500.00
  - Amount to Add: EGP 500.00
  - New Balance: EGP 3,000.00
- Actions:
  - "Cancel" button (secondary)
  - "Add Money" button (primary, enabled when amount > 0)

**On Submit:**
- Show loading state
- API call to add money
- On success:
  - Close dialog
  - Update balance in detail panel
  - Show success toast: "EGP 500.00 added successfully"
  - Refresh user details
- On error:
  - Show error message in dialog

---

#### Withdraw Money Dialog

**When clicking "Withdraw Money" (for owners):**
- Modal dialog overlay
- Title: "Withdraw Money from Owner Balance"
- User info: Photo, name, current balance displayed prominently
- **Amount Input**:
  - Label: "Amount to Withdraw"
  - Currency: EGP
  - Number input (min: 0.01, max: current_balance)
  - Placeholder: "0.00"
  - Max value: Cannot exceed current balance
- **Description/Notes** (optional):
  - Text area
  - Label: "Notes (optional)"
  - Placeholder: "e.g., Cash withdrawal for owner"
  - Max 255 characters
- **Balance Validation**:
  - If amount > balance: Show error "Amount exceeds available balance"
  - Submit button disabled if insufficient balance
- **Preview**:
  - Current Balance: EGP 5,000.00
  - Amount to Withdraw: EGP 2,000.00
  - New Balance: EGP 3,000.00
- Actions:
  - "Cancel" button (secondary)
  - "Withdraw Money" button (primary, enabled when amount > 0 and <= balance)

**On Submit:**
- Show loading state
- API call to withdraw money
- On success:
  - Close dialog
  - Update balance in detail panel
  - Show success toast: "EGP 2,000.00 withdrawn successfully"
  - Refresh user details
- On error:
  - Show error message in dialog
  - If insufficient balance: "Owner does not have sufficient balance"

---

#### Transaction History Panel

**When clicking "View Transaction History":**
- Slide-out panel from right (500px width)
- Title: "Transaction History - [User Name]"
- Close button (X) in top-right

**Panel Content:**

**Current Balance Banner:**
- Large display: "Current Balance: EGP 2,500.00"
- Last updated timestamp

**Filter Bar:**
- Transaction Type filter: All | Deposits | Withdrawals | Rent Payments | Refunds | Cancellation Fees
- Date Range: All Time | This Month | Last Month | Last 3 Months

**Transaction List:**
- Table or card list format
- Columns/Fields:
  - Date & Time
  - Type (with icon)
  - Amount (color-coded: green for +, red for -)
  - Description
  - Related Booking (if applicable, tappable)
- Pagination at bottom (20 per page)

**Transaction Item Display:**
- Similar to mobile app transaction history
- Click transaction → Can view booking details if related

**Close:**
- Click X or click outside panel → Close panel

---

#### API Endpoints (Balance Management)

- `GET /api/admin/users/{user_id}/balance`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "balance": 2500.00,
        "updated_at": "2024-12-15T10:30:00Z"
      }
    }
    ```

- `POST /api/admin/users/{user_id}/deposit`
  - Request:
    ```json
    {
      "amount": 500.00,
      "description": "Cash deposit from tenant"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "Money added successfully",
      "data": {
        "new_balance": 3000.00,
        "transaction_id": 123
      }
    }
    ```

- `POST /api/admin/users/{user_id}/withdraw`
  - Request:
    ```json
    {
      "amount": 2000.00,
      "description": "Cash withdrawal for owner"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "Money withdrawn successfully",
      "data": {
        "new_balance": 500.00,
        "transaction_id": 124
      }
    }
    ```
  - Error Response (insufficient balance):
    ```json
    {
      "success": false,
      "error": "Insufficient balance. Available: EGP 500.00, Requested: EGP 2000.00"
    }
    ```

- `GET /api/admin/users/{user_id}/transactions`
  - Query params: `page`, `per_page`, `type`, `date_from`, `date_to`
  - Response: Same format as user transaction history API

---

#### Notes
- Master-detail layout provides efficient workflow
- User list stays in view while viewing details
- Cannot delete users with active bookings (enforced by backend)
- Delete confirmation requires typing "DELETE" for safety
- Consider adding user suspension feature (disable without delete)
- Statistics help admin understand user activity before deletion
- Role and status badges provide quick visual identification
- Balance management is available for both tenants (deposit) and owners (withdraw)
- All balance operations create transaction records for audit trail
- Transaction history panel provides full transparency
- Amount validation prevents invalid operations (negative amounts, exceeding balance)

---

## 7. Content Overview

### 7.1 Overview

Content overview screens allow admins to monitor platform activity by viewing all apartments and bookings system-wide. These are **read-only views** (no edit/delete capabilities in initial version).

---

### 7.1.1 All Apartments Screen

**Screen ID**: `ADMIN-CONTENT-001`

#### Purpose
Display all apartment listings in the system for monitoring purposes.

#### User Entry Points
- From Sidebar → Tap "All Apartments"
- From Dashboard → Tap "Total Apartments" card

#### UI Components

**Page Header:**
- Breadcrumb: Dashboard > All Apartments
- Headline: "All Apartments"
- Statistics: "Total: 456 apartments (423 Active, 33 Inactive)"

**Toolbar:**
- **Search Bar**:
  - Placeholder: "Search by address or owner name..."
- **Status Filter**:
  - Options: All | Active | Inactive
- **Location Filter**:
  - Governorate dropdown
  - City dropdown (dependent)
- **Sort Dropdown**:
  - Options: Newest | Oldest | Price (Low to High) | Price (High to Low) | Rating

**Data Table:**

**Table Columns:**
1. **Photo** - Thumbnail (60x40px)
2. **Address** - Full address
3. **Owner** - Owner name (clickable → user detail)
4. **Location** - City, Governorate
5. **Price** - Price/period
6. **Status** - Badge: Active/Inactive
7. **Rating** - Stars + count (e.g., "⭐ 4.5 (35)")
8. **Bookings** - Total booking count
9. **Created Date** - Registration date
10. **Actions** - View button

**Table Features:**
- Sortable columns
- Row hover effect
- Pagination: 25 rows per page
- Export to CSV button (optional)

**Action Buttons (per row):**
- **View Details** → Shows apartment detail modal/panel

#### User Actions
- Search apartments → Filter results
- Select filters → Apply filters
- Click column header → Sort
- Click owner name → Navigate to user detail
- Click "View Details" → Open apartment detail panel
- Click pagination → Load next/previous page

#### Empty States
**No Apartments:**
- Icon: 🏠
- Message: "No apartments in the system"
- Description: "Apartments will appear here once owners add listings"

#### Loading States
- Skeleton loader for table rows

#### Error States
- **Load Error**: "Unable to load apartments. Please try again."

#### Apartment Detail Panel (Slide-out/Modal)
**When clicking View Details:**
- **Layout**: Right-side slide-out panel (400px width)
- **Content**:
  - Photo gallery (carousel)
  - Full address
  - Owner info (name, photo, link to profile)
  - Price and specifications
  - Description
  - Amenities list
  - Status badge
  - Statistics:
    - Total bookings: [count]
    - Average rating: [rating]
    - Created date: [date]
- **Actions**:
  - "View Owner Profile" button
  - Close button (X)

#### Related Entities
- `apartments` table
- `users` table (owner info)
- `bookings` table (count)
- `ratings` table (average)

#### API Endpoints
- `GET /api/admin/apartments`
  - Query params: `search`, `status`, `governorate`, `city`, `sort`, `page`, `per_page`
  - Response: List of apartments with owner info and statistics

#### Notes
- Read-only in initial version (no edit/delete)
- Future: Add ability to deactivate apartments
- Future: Add apartment analytics and reports
- Helps admins monitor platform content quality

---

### 7.1.2 All Bookings Screen

**Screen ID**: `ADMIN-CONTENT-002`

#### Purpose
Display all bookings in the system for monitoring platform activity.

#### User Entry Points
- From Sidebar → Tap "All Bookings"
- From Dashboard → Tap "Total Bookings" card

#### UI Components

**Page Header:**
- Breadcrumb: Dashboard > All Bookings
- Headline: "All Bookings"
- Statistics: "Total: 2,890 bookings (145 Active, 2,745 Completed)"

**Toolbar:**
- **Search Bar**:
  - Placeholder: "Search by booking ID, tenant, or apartment..."
- **Status Filter Tabs**:
  - All | Pending | Approved | Active | Completed | Cancelled
- **Date Range Picker**:
  - Label: "Check-in Date Range"
  - From Date | To Date
- **Sort Dropdown**:
  - Options: Newest | Oldest | Check-in Date | Check-out Date

**Data Table:**

**Table Columns:**
1. **Booking ID** - #123456
2. **Tenant** - Tenant name (clickable)
3. **Apartment** - Address (truncated)
4. **Owner** - Owner name (clickable)
5. **Check-in** - Date
6. **Check-out** - Date
7. **Duration** - Nights/Days
8. **Price** - Total price
9. **Status** - Badge
10. **Booked Date** - Creation date
11. **Actions** - View button

**Status Badge Colors:**
- Pending: Orange
- Approved: Blue
- Active: Green (currently in stay period)
- Completed: Gray
- Cancelled: Red
- Rejected: Red

**Table Features:**
- Sortable columns
- Color-coded status badges
- Pagination: 25 rows per page
- Export to CSV button (optional)

**Action Buttons (per row):**
- **View Details** → Shows booking detail panel

#### User Actions
- Search bookings → Filter results
- Select status tab → Show bookings by status
- Select date range → Filter by check-in dates
- Select sort → Re-sort table
- Click tenant/owner name → Navigate to user detail
- Click "View Details" → Open booking detail panel
- Click pagination → Load next/previous page

#### Empty States
**No Bookings:**
- Icon: 📅
- Message: "No bookings yet"
- Description: "Bookings will appear here as users make reservations"

#### Loading States
- Skeleton loader for table rows

#### Error States
- **Load Error**: "Unable to load bookings. Please try again."

#### Booking Detail Panel (Slide-out/Modal)
**When clicking View Details:**
- **Layout**: Right-side slide-out panel
- **Content**:
  - Booking ID (large)
  - Status badge
  - **Tenant Info**: Photo, name, link to profile
  - **Owner Info**: Photo, name, link to profile
  - **Apartment Info**: Photo, address, link to apartment
  - **Booking Details**:
    - Check-in date/time
    - Check-out date/time
    - Duration
    - Number of guests
    - Payment method
    - Total price
  - **Dates**:
    - Booked on: [date]
    - Last updated: [date]
  - **Timeline** (if modifications):
    - Original booking
    - Modifications
    - Status changes
- **Actions**:
  - "View Tenant Profile" button
  - "View Owner Profile" button
  - "View Apartment Details" button
  - Close button (X)

#### Related Entities
- `bookings` table
- `users` table (tenant and owner)
- `apartments` table

#### API Endpoints
- `GET /api/admin/bookings`
  - Query params: `search`, `status`, `check_in_from`, `check_in_to`, `sort`, `page`, `per_page`
  - Response: List of bookings with tenant, owner, and apartment info

#### Notes
- Read-only monitoring view
- Helps admins track platform activity
- Future: Add ability to cancel bookings on behalf of users
- Future: Add dispute resolution features
- Statistics provide insights into booking patterns

---

## 8. Settings

### 8.1 Overview

Settings screens allow admins to configure their personal preferences and view account information.

---

### 8.1.1 Profile/Account Screen

**Screen ID**: `ADMIN-SETTINGS-001`

#### Purpose
Display admin account information and allow profile photo/name updates.

#### User Entry Points
- From Top App Bar → Profile dropdown → "View Profile"
- From Sidebar → Tap "Settings" → Profile tab

#### UI Components

**Page Header:**
- Breadcrumb: Dashboard > Settings > Profile
- Headline: "Profile Settings"

**Profile Section:**

**Profile Photo:**
- Large circular photo (150x150px)
- "Change Photo" button overlay
- Action sheet on click: Take Photo | Choose from Library | Remove Photo

**Personal Information:**
- **First Name**: Editable text input
- **Last Name**: Editable text input
- **Email**: Read-only (gray text)
  - Note: "Contact super admin to change email"
- **Role**: Read-only badge "Administrator"
- **Account Created**: Read-only date

**Action Buttons:**
- Primary button: "Save Changes" (enabled when changes made)
- Secondary button: "Cancel" (discards changes)

#### User Actions
- Click "Change Photo" → Upload/select new photo
- Edit name fields → Enable save button
- Click "Save Changes" →
  - Upload photo if changed
  - Update profile
  - Show success message
- Click "Cancel" → Discard unsaved changes

#### Empty States
- N/A (profile always has data)

#### Loading States
- Photo upload: Progress indicator
- Save changes: Spinner on button

#### Error States
- Photo upload error: Toast notification
- Update error: Error message below form

#### Related Entities
- `users` table (admin user)

#### API Endpoints
- `GET /api/admin/profile`
- `PUT /api/admin/profile` (name, photo)
- `POST /api/admin/profile/upload-photo`

#### Notes
- Only name and photo editable
- Email change requires super admin intervention
- Similar to customer app profile editing

---

### 8.1.2 Language Settings Screen

**Screen ID**: `ADMIN-SETTINGS-002`

#### Purpose
Allow admin to switch interface language between English and Arabic.

#### User Entry Points
- From Sidebar → Tap "Settings" → Language tab
- From Top App Bar → Profile dropdown → "Settings" → Language

#### UI Components

**Page Header:**
- Breadcrumb: Dashboard > Settings > Language
- Headline: "Language Settings"

**Language Selection:**
- Description: "Select your preferred language for the admin panel"
- Radio button list:
  - ○ **English**
    - Description: "English (United States)"
    - Preview sample text in English
  - ○ **العربية**
    - Description: "Arabic (العربية)"
    - Preview sample text in Arabic
- Current selection pre-selected

**Apply Button:**
- Primary button: "Apply Language" (enabled when selection changes)

#### User Actions
- Select language → Radio button selected
- Click "Apply Language" →
  - Show loading overlay
  - Update language preference
  - Reload interface with new language
  - Text direction switches (LTR ↔ RTL)
  - Show success message

#### Empty States
- N/A (options always visible)

#### Loading States
- Applying language: Full-screen overlay "Applying language..."

#### Error States
- Update error: Toast "Unable to change language"

#### Related Entities
- `users` table (update language_preference)

#### API Endpoints
- `PUT /api/admin/profile/language`

#### Notes
- Interface switches direction automatically (RTL for Arabic)
- All strings must be localized
- Sidebar, tables, forms adapt to text direction
- Same as customer app language settings

---

### 8.1.3 Theme Settings Screen

**Screen ID**: `ADMIN-SETTINGS-003`

#### Purpose
Allow admin to switch between light and dark theme modes.

#### User Entry Points
- From Sidebar → Tap "Settings" → Theme tab
- From Top App Bar → Profile dropdown → "Settings" → Theme

#### UI Components

**Page Header:**
- Breadcrumb: Dashboard > Settings > Theme
- Headline: "Theme Settings"

**Theme Selection:**
- Description: "Choose your preferred theme"
- Radio button options with preview:
  - ○ **Light Mode**
    - Color swatch preview
    - Description: "Classic bright theme"
  - ○ **Dark Mode**
    - Color swatch preview
    - Description: "Easy on the eyes, reduces eye strain"
  - ○ **System Default** (optional)
    - Description: "Follows your browser/OS theme"

**Live Preview Panel:**
- Shows sample dashboard cards in selected theme
- Updates in real-time as selection changes

**Apply Button:**
- Primary button: "Apply Theme" (enabled when selection changes)

#### User Actions
- Select theme → Preview updates
- Click "Apply Theme" →
  - Apply theme immediately
  - Save preference locally
  - Show brief success message

#### Empty States
- N/A (options always visible)

#### Loading States
- Theme transition: Smooth animation (no loading state)

#### Error States
- Save error: Toast notification

#### Related Entities
- Local storage only (browser preference)

#### API Endpoints
- None (stored locally in browser)

#### Notes
- Theme applies immediately without page reload
- All screens must support both themes
- Dark mode especially useful for long admin sessions
- Tables, forms, modals all adapt to theme

---

## 9. Common UI Patterns

### 9.1 Layout Components

**Top App Bar (Persistent):**
- Height: 64px
- Background: Primary color (light mode) / Dark surface (dark mode)
- Contains: Logo, Search (optional), Notifications, Profile
- Sticky/Fixed position

**Sidebar Navigation:**
- Width: 240px (expanded) / 60px (collapsed)
- Contains: Menu items with icons and labels
- Hover effects on menu items
- Active item highlighted
- Collapsible with animation

**Main Content Area:**
- Padding: 24px
- Max-width: 1400px (centered for very wide screens)
- Background: Surface color
- Scrollable vertically

### 9.2 Data Tables

**Standard Table Features:**
- Header row with column labels
- Sortable columns (click header)
- Row hover effect (light background)
- Zebra striping (optional, alternate row colors)
- Fixed header (when scrolling)
- Responsive text overflow (ellipsis)
- Pagination controls at bottom

**Table Actions:**
- Per-row action buttons (View, Edit, Delete)
- Bulk actions toolbar (optional, future)
- Export button (CSV/PDF)

**Empty Table State:**
- Centered message with icon
- Description text
- Action button (if applicable)

### 9.3 Modals and Dialogs

**Modal Structure:**
- Semi-transparent backdrop (overlay)
- Centered card container
- Header: Title + close button (X)
- Content: Scrollable if long
- Footer: Action buttons (Cancel left, Primary right)
- ESC key closes modal

**Dialog Types:**
- **Confirmation Dialog**: Yes/No or Cancel/Confirm
- **Form Dialog**: Input fields with submit
- **Detail Dialog**: Read-only information display

**Slide-out Panel:**
- Alternative to modal for details
- Slides in from right
- 400-600px width
- Close button (X) in header
- Overlay backdrop (click to close)

### 9.4 Loading States

**Page Load:**
- Skeleton loaders matching content structure
- Shimmer animation effect
- Preserve layout (no content shift)

**Button Loading:**
- Spinner replaces button text
- Button disabled during loading
- Original text restored after completion

**Table Loading:**
- Skeleton rows (5-10 rows)
- Column structure maintained

**Full-Screen Loading:**
- Overlay with centered spinner
- Optional message: "Loading..." or specific action
- Semi-transparent background

### 9.5 Error Handling

**Error Display Methods:**

**Page-Level Error:**
- Full content area
- Error icon (⚠️)
- Error message (bold)
- Description (helpful text)
- Retry button (primary)

**Inline Error:**
- Below form field
- Red text
- Error icon
- Specific validation message

**Toast Notification:**
- Bottom-right corner
- Auto-dismiss (3-5 seconds)
- Can be dismissed manually
- Color-coded: Red (error), Green (success), Blue (info)

**Error Dialog:**
- Modal popup
- Error icon
- Message and description
- "OK" or "Retry" button

### 9.6 Form Patterns

**Form Structure:**
- Clear labels above fields
- Placeholder text in inputs
- Validation on blur or submit
- Required field indicators (*)
- Help text below fields (if needed)

**Form Validation:**
- Real-time validation on blur
- Red border on invalid fields
- Error message below field
- Disable submit until valid

**Form Actions:**
- Primary: "Save", "Submit", "Apply"
- Secondary: "Cancel", "Reset"
- Buttons right-aligned
- Adequate spacing between buttons

### 9.7 Badges and Status Indicators

**Status Badges:**
- Small, rounded rectangles
- Color-coded by status:
  - Green: Active, Approved, Completed
  - Orange: Pending, Warning
  - Red: Rejected, Cancelled, Error
  - Gray: Inactive, Disabled
  - Blue: In Progress, Modified
- Consistent across all screens

**Count Badges:**
- Small circles with numbers
- Red background for alerts
- Positioned on icons (notifications, pending)

### 9.8 Responsive Behavior

**Desktop Only (1024px+ minimum):**
- Optimized for 1024px - 1920px width
- Centered content for ultra-wide (>1920px)
- Below 1024px: Show message
  - "This admin panel is optimized for desktop use"
  - "Please access from a device with minimum screen width of 1024px"
  - "For mobile management, use the FindOut mobile app"

**No Mobile/Tablet Adaptation:**
- Maintains desktop layout always
- No hamburger menus
- No stacked layouts
- Sidebar remains visible

### 9.9 Keyboard Navigation

**Accessibility:**
- Tab key: Navigate through interactive elements
- Enter/Space: Activate buttons/links
- Arrow keys: Navigate within tables/lists
- ESC key: Close modals/dialogs
- Focus indicators: Visible blue outline

**Keyboard Shortcuts (Optional Future):**
- Ctrl+K: Open search
- Ctrl+S: Save (where applicable)
- Ctrl+/: Show shortcuts help

### 9.10 Notifications

**In-App Notifications:**
- Bell icon in top app bar
- Badge shows unread count
- Click → Dropdown list of notifications
- Recent 10 notifications shown
- "View All" link → Full notifications page

**Notification Types:**
- New registration request
- User activity alerts
- System updates
- Action confirmations

**Notification Actions:**
- Mark as read
- Navigate to related content
- Dismiss

---

## 10. Appendix

### 10.1 Screen ID Reference

**Authentication (2 screens):**
- ADMIN-AUTH-001: Login
- ADMIN-AUTH-002: Pending Approval (optional)

**Dashboard (1 screen):**
- ADMIN-DASH-001: Dashboard

**Registration Management (2 screens):**
- ADMIN-REG-001: Pending Registrations List
- ADMIN-REG-002: Registration Detail

**User Management (1 screen):**
- ADMIN-USER-001: All Users (Master-Detail)

**Content Overview (2 screens):**
- ADMIN-CONTENT-001: All Apartments
- ADMIN-CONTENT-002: All Bookings

**Settings (3 screens):**
- ADMIN-SETTINGS-001: Profile
- ADMIN-SETTINGS-002: Language
- ADMIN-SETTINGS-003: Theme

**Total: 11 screens documented**

### 10.2 API Endpoint Summary

All admin API endpoints follow RESTful conventions:
- Base URL: `https://api.findout.com/api/v1/admin/`
- Authentication: Bearer token (JWT) in Authorization header
- Request format: JSON
- Response format: JSON
- Admin role required for all endpoints

### 10.3 Browser Compatibility

**Supported Browsers:**
- Chrome 90+
- Firefox 88+
- Safari 14+
- Microsoft Edge 90+

**Not Supported:**
- Internet Explorer (any version)
- Mobile browsers (by design)

### 10.4 Performance Guidelines

**Page Load Time:**
- Initial page load: < 2 seconds
- Navigation between pages: < 500ms (SPA)
- Table data load: < 1 second
- Image optimization: Compress and lazy-load

**Data Pagination:**
- Tables: 20-50 rows per page
- Lists: 50-100 items per page
- Infinite scroll where applicable

### 10.5 Security Considerations

**Authentication:**
- JWT token-based authentication
- Token expiration: 24 hours
- Secure token storage (httpOnly cookies or secure localStorage)
- Session timeout after inactivity

**Authorization:**
- All actions require admin role verification
- Backend validates admin permissions on every request
- No client-side only authorization

**Data Protection:**
- HTTPS only (enforce)
- No sensitive data in URLs
- Secure handling of user photos and personal information
- Audit logging of admin actions (optional future)

### 10.6 Localization

**Supported Languages:**
- English (en) - LTR
- Arabic (ar) - RTL

**RTL Considerations:**
- Complete layout mirroring for Arabic
- Text alignment switches automatically
- Icons and buttons flip position
- Tables and forms adapt direction
- Numbers and dates formatted per locale

### 10.7 Future Enhancements

**Phase 2 Features:**
- Advanced analytics dashboard with charts
- User suspension (disable without delete)
- Apartment content moderation
- Booking dispute resolution
- Bulk actions (approve multiple registrations)
- Export reports (PDF, Excel)
- Admin activity audit logs
- Role-based admin permissions (super admin, moderator)
- Email notifications to admins
- System configuration settings
- Platform announcements management

---

## 🎉 Documentation Complete

**Admin Web Application Screen Documentation is now complete!**

### Summary:

**Total Screens: 11 screens documented**

**Screen Breakdown:**
- ✅ **Authentication**: 2 screens (Login, Pending Approval)
- ✅ **Dashboard**: 1 screen (Statistics overview)
- ✅ **Registration Management**: 2 screens (List, Detail with approval)
- ✅ **User Management**: 1 screen (Master-Detail layout)
- ✅ **Content Overview**: 2 screens (Apartments, Bookings)
- ✅ **Settings**: 3 screens (Profile, Language, Theme)

### Each Screen Includes:
✅ Purpose and user entry points  
✅ Complete UI component specifications  
✅ User interaction flows  
✅ Empty, loading, and error states  
✅ Validation rules  
✅ API endpoints with request/response examples  
✅ Implementation notes  

### Additional Documentation:
✅ Navigation architecture (sidebar + top bar)  
✅ Master-detail layout patterns  
✅ Common UI patterns and components  
✅ Data table specifications  
✅ Modal and dialog standards  
✅ Desktop-only responsive behavior (1024px+)  
✅ Keyboard navigation and accessibility  
✅ Security and performance guidelines  
✅ Future enhancement roadmap  

**This documentation is ready for:**
- Flutter Web developers (implementation)
- Backend developers (admin API endpoints)
- UI/UX designers (visual design)
- QA engineers (testing)
- System administrators (training and usage)

---

## 📊 Complete Documentation Set

**Both documentation files are now complete:**

1. ✅ **customer-app-screens.md** - 35 screens (Mobile App)
2. ✅ **admin-web-screens.md** - 11 screens (Web Admin Panel)

**Total: 46 screens documented across both applications**

---

**Project documentation is complete and ready for development!** 🚀

