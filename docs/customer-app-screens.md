# Customer Mobile App - Screen Specifications

## FindOut - Apartment Rental Application

**Version:** 1.0  
**Date:** December 2024  
**Platform:** Flutter (Android & iOS)  
**Target Users:** Tenants and Apartment Owners

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Navigation Architecture](#2-navigation-architecture)
3. [Authentication & Onboarding](#3-authentication--onboarding)
4. [Tenant Screens](#4-tenant-screens)
5. [Owner Screens](#5-owner-screens)
6. [Shared Screens](#6-shared-screens)
7. [Common UI Patterns](#7-common-ui-patterns)

---

## 1. Introduction

### 1.1 Purpose

This document defines all screens, navigation flows, and UI components for the FindOut customer mobile application. It serves as a comprehensive reference for:

- **Flutter developers** implementing the mobile app
- **Backend developers** understanding required API endpoints
- **UI/UX designers** creating visual designs
- **QA engineers** testing application flows

### 1.2 Scope

The customer mobile app supports **two distinct user roles**:

- **Tenants**: Users who search for and book apartments
- **Owners**: Users who list and manage apartments

**Important**: A user's role is fixed at registration and cannot be changed. The app provides different navigation structures and features based on the user's role.

### 1.3 Design Principles

- **Material Design** for Android, **Cupertino** for iOS
- **Responsive layouts** supporting various screen sizes (phones and tablets)
- **RTL/LTR support** for Arabic and English languages
- **Dark/Light theme** support throughout the app
- **Accessibility** considerations for all interactive elements

### 1.4 Related Documents

- [SRS.md](SRS.md) - Software Requirements Specification
- [ERD.md](ERD.md) - Database Design
- [project-requirements.md](project-requirements.md) - Original Requirements

---

## 2. Navigation Architecture

### 2.1 Role-Based Navigation

The application uses **separate navigation structures** for Tenants and Owners, determined at login based on the user's role.

#### 2.1.1 Tenant Navigation Structure

**Bottom Navigation Bar** (5 tabs):

| Tab | Icon | Screen | Description |
|-----|------|--------|-------------|
| Home | 🏠 | Apartment Browse | Browse and search apartments |
| Bookings | 📅 | Booking History | View all bookings (current, past, cancelled) |
| Favorites | ❤️ | Favorites List | Saved apartments |
| Messages | 💬 | Message Conversations | Chat with apartment owners |
| Profile | 👤 | Profile | User profile and settings |

#### 2.1.2 Owner Navigation Structure

**Bottom Navigation Bar** (4 tabs):

| Tab | Icon | Screen | Description |
|-----|------|--------|-------------|
| My Apartments | 🏢 | Apartment Management | List and manage owned apartments |
| Bookings | 📋 | Booking Requests | View and approve/reject booking requests |
| Messages | 💬 | Message Conversations | Chat with tenants |
| Profile | 👤 | Profile | User profile and settings |

**Note**: Owners do NOT have a "Home" or "Favorites" tab since they cannot book apartments.

### 2.2 Navigation Behavior

- **Persistent Bottom Navigation**: Visible on all main screens
- **Stack Navigation**: Used for detail screens, forms, and modals
- **Deep Linking**: Support for push notification navigation
- **Back Button**: Standard Android back button behavior
- **iOS Swipe**: Support for iOS swipe-back gesture

### 2.3 Top App Bar Pattern

Most screens include a top app bar with:

- **Title**: Screen name or context
- **Left Action**: Back button (on detail screens) or menu icon (on main screens)
- **Right Actions**: Notifications bell icon (with unread badge), other contextual actions

---

## 3. Authentication & Onboarding

### 3.1 Overview

The authentication flow consists of:

1. **Splash Screen** → Initial app load
2. **Welcome Screen** → First-time user entry point
3. **Registration Flow** → Multi-step registration (4 screens)
4. **Login Screen** → Returning user authentication
5. **Pending Approval States** → Handling account approval status

---

### 3.1.1 Splash Screen

**Screen ID**: `AUTH-001`

#### Purpose
Initial loading screen displayed while the app:
- Checks authentication status (JWT token validity)
- Loads user preferences (language, theme)
- Determines navigation destination

#### UI Components
- FindOut logo (centered)
- App name
- Loading indicator
- App version (bottom)

#### Navigation Logic
```
If JWT token exists AND valid:
  → Check user role
    If Tenant → Navigate to Tenant Home
    If Owner → Navigate to Owner My Apartments
Else:
  → Navigate to Welcome Screen
```

#### Loading States
- **Initial Load**: 1-3 seconds maximum
- **No skeleton needed**: Static branded screen

#### Related Entities
- `users` table (authentication check)

---

### 3.1.2 Welcome Screen

**Screen ID**: `AUTH-002`

#### Purpose
First screen for unauthenticated users, introducing the app and providing entry points for registration or login.

#### User Entry Points
- From Splash Screen (if not authenticated)

#### UI Components

**Main Content:**
- App logo (top center)
- Welcome headline: "Find Your Perfect Stay"
- Subheadline: "Book apartments easily and quickly"
- Illustration/Hero image (apartment browsing visual)

**Action Buttons:**
- **Primary Button**: "Get Started" (leads to Registration Step 1)
- **Secondary Button/Link**: "Already have an account? Log In"

**Footer:**
- Language selector dropdown (English/العربية)
- Terms of Service link
- Privacy Policy link

#### User Actions
- Tap "Get Started" → Navigate to Registration Screen (Mobile Number)
- Tap "Log In" → Navigate to Login Screen
- Change language → App language updates, screen re-renders in selected language

#### Empty States
- N/A (static screen)

#### Loading States
- N/A (static screen)

#### Error States
- N/A (static screen)

#### Related Entities
- None (pre-authentication)

#### Notes
- Language selection persists even before login (stored locally)
- Theme follows system default on first launch

---

### 3.1.3 Registration Screen - Step 1: Mobile Number

**Screen ID**: `AUTH-003`

#### Purpose
Collect user's mobile number and send OTP for verification.

#### User Entry Points
- From Welcome Screen (tap "Get Started")

#### UI Components

**Top App Bar:**
- Back button (returns to Welcome Screen)
- Title: "Register"
- Progress indicator: Step 1 of 4

**Main Content:**
- Headline: "Enter Your Mobile Number"
- Description: "We'll send you a verification code"
- Mobile number input field
  - Country code selector (default: user's country)
  - Phone number input (numeric keyboard)
  - Format: +XXX XXX XXX XXXX
  - Validation: Real-time format validation

**Action Buttons:**
- Primary button: "Send Code" (disabled until valid number entered)

#### User Actions
- Enter mobile number → Validates format in real-time
- Tap "Send Code" → 
  - Show loading state on button
  - Send OTP via SMS (backend)
  - On success: Navigate to OTP Verification Screen
  - On error: Show error message (e.g., "Number already registered")

#### Empty States
- N/A (form screen)

#### Loading States
- **Button Loading**: "Send Code" button shows spinner while sending OTP

#### Error States
- **Invalid Format**: "Please enter a valid mobile number"
- **Number Already Exists**: "This number is already registered. Please log in."
- **SMS Service Error**: "Unable to send verification code. Please try again."

#### Validation Rules
- Mobile number must be 10-15 digits
- Must be valid format for selected country code
- Must not be already registered in system

#### Related Entities
- `users` table (check if mobile number exists)
- `otp_verifications` table (create OTP record)

#### API Endpoints
- `POST /api/auth/send-otp`
  - Request: `{ mobile_number: string }`
  - Response: `{ success: boolean, message: string }`

#### Notes
- OTP expires after 5 minutes
- User can request new OTP (implemented in next screen)

---

### 3.1.4 Registration Screen - Step 2: OTP Verification

**Screen ID**: `AUTH-004`

#### Purpose
Verify the OTP sent to user's mobile number.

#### User Entry Points
- From Registration Step 1 (after OTP sent)

#### UI Components

**Top App Bar:**
- Back button (returns to Step 1)
- Title: "Register"
- Progress indicator: Step 2 of 4

**Main Content:**
- Headline: "Enter Verification Code"
- Description: "Code sent to +XXX XXX XXX XXXX" (shows entered number)
- OTP input fields
  - 6 separate digit boxes (auto-focus next box)
  - Numeric keyboard
  - Auto-submit when all 6 digits entered
- Timer: "Code expires in 04:32" (countdown from 5:00)
- Resend link: "Didn't receive code? Resend" (enabled after 30 seconds)

**Action Buttons:**
- Primary button: "Verify" (enabled when 6 digits entered)

#### User Actions
- Enter OTP → Auto-submits or tap "Verify"
  - Show loading state
  - Verify OTP (backend)
  - On success: Navigate to Registration Step 3 (Password Creation)
  - On error: Show error message, clear OTP input
- Tap "Resend" → 
  - Send new OTP
  - Reset timer to 5:00
  - Show success toast: "New code sent"
- Timer expires → Show warning: "Code expired. Please request a new code."

#### Empty States
- N/A (form screen)

#### Loading States
- **Verification Loading**: Show spinner overlay while verifying OTP
- **Resend Loading**: "Resend" link shows spinner while sending new code

#### Error States
- **Invalid OTP**: "Invalid verification code. Please try again."
- **Expired OTP**: "Verification code has expired. Please request a new code."
- **Max Attempts**: "Too many failed attempts. Please try again later."

#### Validation Rules
- OTP must be exactly 6 digits
- Must match OTP sent to mobile number
- Must not be expired (5 minutes)

#### Related Entities
- `otp_verifications` table (verify OTP)

#### API Endpoints
- `POST /api/auth/verify-otp`
  - Request: `{ mobile_number: string, otp_code: string }`
  - Response: `{ success: boolean, message: string, temp_token: string }`
- `POST /api/auth/resend-otp`
  - Request: `{ mobile_number: string }`
  - Response: `{ success: boolean, message: string }`

#### Notes
- Temporary token issued after OTP verification (valid for 30 minutes to complete registration)
- OTP verification is MANDATORY - users cannot proceed without it

---

### 3.1.5 Registration Screen - Step 3: Create Password

**Screen ID**: `AUTH-005`

#### Purpose
User creates a password for future logins (no OTP required for login after registration).

#### User Entry Points
- From Registration Step 2 (after successful OTP verification)

#### UI Components

**Top App Bar:**
- Back button (returns to Step 2)
- Title: "Register"
- Progress indicator: Step 3 of 4

**Main Content:**
- Headline: "Create Your Password"
- Description: "Use this password to log in to your account"
- Password input field
  - Label: "Password"
  - Type: Password (obscured)
  - Show/Hide password toggle icon
  - Validation indicators below field:
    - ✅ At least 8 characters
    - ✅ Contains uppercase letter
    - ✅ Contains lowercase letter
    - ✅ Contains number
    - ✅ Contains special character
- Confirm password input field
  - Label: "Confirm Password"
  - Type: Password (obscured)
  - Show/Hide password toggle icon

**Action Buttons:**
- Primary button: "Continue" (enabled when both passwords valid and match)

#### User Actions
- Enter password → Real-time validation, show indicators
- Enter confirm password → Check if matches
- Tap "Continue" →
  - Show loading state
  - Store password securely (hashed)
  - Navigate to Registration Step 4 (Role Selection)

#### Empty States
- N/A (form screen)

#### Loading States
- **Button Loading**: "Continue" button shows spinner while processing

#### Error States
- **Passwords Don't Match**: "Passwords do not match"
- **Weak Password**: "Password must meet all requirements"

#### Validation Rules
- Password must be at least 8 characters
- Must contain at least one uppercase letter
- Must contain at least one lowercase letter
- Must contain at least one number
- Must contain at least one special character (@$!%*?&#)
- Both password fields must match exactly

#### Related Entities
- None (stored in session temporarily until registration completes)

#### API Endpoints
- None (password stored temporarily, sent in final registration request)

#### Notes
- Password is not sent to backend until final registration submission
- Password validation follows security best practices
- Consider password strength indicator (weak/medium/strong)

---

### 3.1.6 Registration Screen - Step 4: Role Selection

**Screen ID**: `AUTH-006`

#### Purpose
User selects their role (Tenant or Owner). This role is fixed and cannot be changed later.

#### User Entry Points
- From Registration Step 3 (after password creation)

#### UI Components

**Top App Bar:**
- Back button (returns to Step 3)
- Title: "Register"
- Progress indicator: Step 4 of 4

**Main Content:**
- Headline: "Choose Your Role"
- Description: "Select how you'll use FindOut"
- Role selection cards (2 options):
  
  **Tenant Card:**
  - Icon: 🔍 (search/browse icon)
  - Title: "I'm looking to rent"
  - Description: "Search and book apartments"
  - Radio button or selectable card
  
  **Owner Card:**
  - Icon: 🏠 (house/building icon)
  - Title: "I own apartments"
  - Description: "List and manage your properties"
  - Radio button or selectable card

- Info box (bottom):
  - ℹ️ "Your role cannot be changed after registration"

**Action Buttons:**
- Primary button: "Continue" (enabled when one role selected)

#### User Actions
- Select Tenant or Owner → Card highlights, radio button selected
- Tap "Continue" →
  - Show loading state
  - Navigate to Registration Step 5 (Personal Information)

#### Empty States
- N/A (selection screen)

#### Loading States
- **Navigation Loading**: Brief transition, no explicit loading state needed

#### Error States
- **No Selection**: "Please select your role to continue"

#### Validation Rules
- Exactly one role must be selected
- Role selection is mandatory

#### Related Entities
- `users.role` field (to be saved in final registration)

#### API Endpoints
- None (role stored temporarily until final registration)

#### Notes
- **CRITICAL**: Once registered, user role CANNOT be changed
- If user needs both roles, they must create two separate accounts
- Role determines which navigation structure user sees after login

---

### 3.1.7 Registration Screen - Step 5: Personal Information

**Screen ID**: `AUTH-007`

#### Purpose
Collect all required personal information before submitting registration for admin approval.

#### User Entry Points
- From Registration Step 4 (after role selection)

#### UI Components

**Top App Bar:**
- Back button (returns to Step 4)
- Title: "Personal Information"
- Close button (X) - shows confirmation dialog before canceling registration

**Main Content:**
- Headline: "Complete Your Profile"
- Description: "All fields are required for verification"
- Scrollable form with fields:

  **1. First Name**
  - Text input field
  - Placeholder: "Enter your first name"
  - Max length: 100 characters
  
  **2. Last Name**
  - Text input field
  - Placeholder: "Enter your last name"
  - Max length: 100 characters
  
  **3. Date of Birth**
  - Date picker field
  - Placeholder: "Select your date of birth"
  - Format: DD/MM/YYYY (or MM/DD/YYYY based on locale)
  - Max date: 18 years ago (user must be 18+)
  
  **4. Personal Photo**
  - Photo upload component
  - Circular avatar placeholder with camera icon
  - "Upload Photo" button
  - Options: Take Photo | Choose from Gallery
  - Preview uploaded photo
  - Max size: 5MB
  - Formats: JPG, PNG
  
  **5. ID Photo**
  - Photo upload component
  - Rectangle placeholder with ID card icon
  - "Upload ID Photo" button
  - Options: Take Photo | Choose from Gallery
  - Preview uploaded photo
  - Max size: 5MB
  - Formats: JPG, PNG

- Info box (bottom):
  - ℹ️ "Your information will be reviewed by our admin team"

**Action Buttons:**
- Primary button: "Submit Registration" (enabled when all fields completed and valid)

#### User Actions
- Fill in text fields → Real-time validation
- Tap date picker → Opens date selection dialog
- Tap "Upload Photo" → Shows action sheet (Camera | Gallery)
  - Select option → Opens camera or gallery
  - After photo selected → Shows preview, allows retry
- Tap "Submit Registration" →
  - Show loading overlay
  - Upload photos to server
  - Submit registration data (all steps combined)
  - On success: Navigate to Pending Approval Screen
  - On error: Show error message

#### Empty States
- **Initial State**: All fields empty with placeholders
- **Photo Placeholders**: Show upload icons until photos selected

#### Loading States
- **Photo Upload**: Show progress indicator while uploading photos
- **Form Submission**: Full-screen loading overlay with message "Submitting your registration..."

#### Error States
- **Required Field Missing**: Highlight field in red, show "This field is required"
- **Invalid Name**: "Name must contain only letters and spaces"
- **Invalid Date**: "You must be at least 18 years old"
- **Photo Too Large**: "Photo size must be less than 5MB"
- **Invalid Photo Format**: "Only JPG and PNG formats are supported"
- **Upload Failed**: "Failed to upload photo. Please try again."
- **Submission Failed**: "Unable to submit registration. Please check your connection and try again."

#### Validation Rules
- **First Name**: Required, max 100 chars, letters and spaces only
- **Last Name**: Required, max 100 chars, letters and spaces only
- **Date of Birth**: Required, user must be 18+ years old
- **Personal Photo**: Required, max 5MB, JPG/PNG only
- **ID Photo**: Required, max 5MB, JPG/PNG only
- All fields must be completed before submission

#### Related Entities
- `users` table (create new user record)
- `otp_verifications` table (mark as verified)

#### API Endpoints
- `POST /api/auth/upload-photo`
  - Request: multipart/form-data with image file
  - Response: `{ success: boolean, file_path: string }`
- `POST /api/auth/register`
  - Request: 
    ```json
    {
      "mobile_number": "string",
      "password": "string",
      "role": "tenant|owner",
      "first_name": "string",
      "last_name": "string",
      "date_of_birth": "YYYY-MM-DD",
      "personal_photo": "string (path)",
      "id_photo": "string (path)"
    }
    ```
  - Response: 
    ```json
    {
      "success": boolean,
      "message": "string",
      "user_id": number
    }
    ```

#### Notes
- All data from previous steps is collected and sent in final registration request
- Photos should be compressed before upload to reduce bandwidth
- User status set to "pending" after registration
- Admin notification triggered upon successful registration

---

### 3.1.8 Pending Approval Screen

**Screen ID**: `AUTH-008`

#### Purpose
Inform user that their registration is complete but requires admin approval before they can access the app.

#### User Entry Points
- From Registration Step 5 (after successful submission)

#### UI Components

**Main Content:**
- Success icon: ✅ (large, centered)
- Headline: "Registration Submitted!"
- Message: "Your account is under review. You'll be notified once approved."
- Illustration (person waiting/clock icon)
- Additional info:
  - "This usually takes 24-48 hours"
  - "You'll receive a notification when your account is ready"

**Action Buttons:**
- Primary button: "Done"

#### User Actions
- Tap "Done" → Navigate to Login Screen
- Press back button → Exits to Login Screen

#### Empty States
- N/A (success screen)

#### Loading States
- N/A (static screen)

#### Error States
- N/A (success screen)

#### Related Entities
- `users` table (status = "pending")

#### Notes
- User cannot proceed to app until admin approves
- Push notification sent when admin approves/rejects
- User must log in after approval to access app

---

### 3.1.9 Login Screen

**Screen ID**: `AUTH-009`

#### Purpose
Allow registered and approved users to authenticate and access the application.

#### User Entry Points
- From Welcome Screen (tap "Log In")
- From Pending Approval Screen (tap "Done")
- From Splash Screen (if session expired)
- After logout

#### UI Components

**Main Content:**
- App logo (top center)
- Headline: "Welcome Back"
- Mobile number input field
  - Label: "Mobile Number"
  - Country code selector (default: last used or user's country)
  - Phone number input
  - Format: +XXX XXX XXX XXXX
- Password input field
  - Label: "Password"
  - Type: Password (obscured)
  - Show/Hide password toggle icon
- "Forgot Password?" link (right-aligned)

**Action Buttons:**
- Primary button: "Log In"
- Secondary button/link: "Don't have an account? Register"

**Footer:**
- Language selector dropdown (English/العربية)

#### User Actions
- Enter mobile number and password
- Tap "Log In" →
  - Show loading state on button
  - Authenticate credentials
  - Check user status:
    - **If status = "approved"**: 
      - Issue JWT token
      - Navigate to role-specific home screen
        - Tenant → Apartment Browse Screen
        - Owner → My Apartments Screen
    - **If status = "pending"**: 
      - Show dialog: "Your account is pending approval. Please wait."
      - Stay on login screen
    - **If status = "rejected"**: 
      - Show dialog: "Your account was not approved. Please contact support."
      - Stay on login screen
    - **If credentials invalid**:
      - Show error: "Invalid mobile number or password"
- Tap "Forgot Password?" → Navigate to Password Reset Flow (future feature)
- Tap "Register" → Navigate to Registration Step 1

#### Empty States
- N/A (form screen)

#### Loading States
- **Login Button Loading**: Shows spinner on button while authenticating

#### Error States
- **Invalid Credentials**: "Invalid mobile number or password"
- **Account Pending**: Dialog - "Your account is pending approval. You'll be notified once approved. This usually takes 24-48 hours."
- **Account Rejected**: Dialog - "Your registration was not approved. Please contact support for more information."
- **Network Error**: "Unable to connect. Please check your internet connection."
- **Server Error**: "Something went wrong. Please try again later."

#### Validation Rules
- Mobile number must be valid format
- Password must not be empty
- Both fields required

#### Related Entities
- `users` table (authenticate credentials, check status)

#### API Endpoints
- `POST /api/auth/login`
  - Request: 
    ```json
    {
      "mobile_number": "string",
      "password": "string"
    }
    ```
  - Response: 
    ```json
    {
      "success": boolean,
      "message": "string",
      "token": "string (JWT)",
      "user": {
        "id": number,
        "mobile_number": "string",
        "first_name": "string",
        "last_name": "string",
        "personal_photo": "string",
        "role": "tenant|owner|admin",
        "status": "approved",
        "language_preference": "ar|en"
      }
    }
    ```

#### Notes
- JWT token stored securely (Keychain on iOS, Keystore on Android)
- Token includes user ID, role, and expiration
- Session expires after 24 hours of inactivity
- Token automatically refreshed for active users
- Biometric authentication can be added in future (Face ID/Fingerprint)

---

## 4. Tenant Screens

### 4.1 Overview

Tenant screens are accessible only to users with `role = 'tenant'`. These screens enable tenants to:
- Browse and search for apartments
- View detailed apartment information
- Book apartments for specific periods
- Manage bookings (view, modify, cancel)
- Rate apartments after stays
- Save favorite apartments
- Communicate with apartment owners

**Tenant Bottom Navigation (5 tabs):**
1. **Home** - Browse apartments
2. **Bookings** - View booking history
3. **Favorites** - Saved apartments
4. **Messages** - Chat with owners
5. **Profile** - User settings

---

### 4.1.1 Tenant Home - Apartment Browse Screen

**Screen ID**: `TENANT-001`

#### Purpose
Main landing screen for tenants to browse available apartments. First screen shown after login.

#### User Entry Points
- From Login Screen (after successful authentication)
- From Bottom Navigation → Home tab
- From Splash Screen (if already authenticated)

#### UI Components

**Top App Bar:**
- Title: "FindOut" or "Browse Apartments"
- Right actions:
  - Notification bell icon (with badge if unread notifications)

**Main Content:**

**Filter Bar (Sticky at top):**
- "Filters" button with icon (opens filter bottom sheet)
- Active filter chips (if filters applied):
  - Example: "Cairo" ✕ | "2+ Bedrooms" ✕ | "WiFi" ✕
  - Tap ✕ to remove individual filter
  - "Clear All" link (if any filters active)

**Apartment List:**
- Vertical scrolling list of apartment cards
- Each card shows:
  - **Photo**: Apartment main photo (horizontal scrollable if multiple)
    - Photo indicators (dots): 1/5
    - Favorite heart icon (top-right corner, filled if favorited)
  - **Location**: City, Governorate
  - **Price**: "EGP 500/night or EGP 8,000/month" (displays both nightly and monthly rates)
  - **Rating**: ⭐ 4.5 (35 reviews) - or "New" if no ratings
  - **Specs**: 2 🛏️ | 1 🛁 | 1 🛋️ | 80 m²
  - **Amenities chips**: WiFi, Parking, A/C (max 3 visible)
- Pagination: Load more as user scrolls (infinite scroll)
- Pull-to-refresh: Reload apartment list

**Floating Action Button (FAB):**
- None on this screen

#### User Actions
- Scroll to view more apartments → Loads next page
- Pull down → Refreshes apartment list
- Tap "Filters" button → Opens Filter Bottom Sheet (TENANT-002)
- Tap filter chip ✕ → Removes that filter, refreshes list
- Tap "Clear All" → Removes all filters, refreshes list
- Tap apartment card → Navigate to Apartment Detail Screen (TENANT-003)
- Tap favorite heart icon → 
  - If not favorited: Add to favorites, heart fills, show toast "Added to favorites"
  - If favorited: Remove from favorites, heart empties, show toast "Removed from favorites"
- Tap notification bell → Navigate to Notifications Screen (SHARED-003)

#### Empty States
**No Apartments Found:**
- Icon: 🔍 (search icon)
- Message: "No apartments found"
- Description: "Try adjusting your filters or check back later"
- Action button: "Clear Filters" (if filters applied)

**No Internet Connection:**
- Icon: 📡 (connection icon)
- Message: "No internet connection"
- Description: "Please check your connection and try again"
- Action button: "Retry"

#### Loading States
**Initial Load:**
- Skeleton loaders for 5 apartment cards
- Shimmer effect on each card component

**Pagination Loading:**
- Spinner at bottom of list while loading next page

**Pull-to-Refresh:**
- Standard pull-to-refresh indicator at top

**Filter Application:**
- Brief overlay with spinner while applying filters

#### Error States
- **Load Error**: "Unable to load apartments. Please try again."
  - Retry button shown
- **Filter Error**: Toast message "Unable to apply filters"
- **Favorite Error**: Toast message "Unable to update favorites"

#### Related Entities
- `apartments` table (fetch active apartments)
- `favorites` table (check if apartment is favorited)
- `ratings` table (calculate average rating)

#### API Endpoints
- `GET /api/tenant/apartments`
  - Query params: 
    - `page` (number)
    - `per_page` (number, default 20)
    - `governorate` (string, optional)
    - `city` (string, optional)
    - `min_price` (number, optional)
    - `max_price` (number, optional)
    - `bedrooms` (number, optional)
    - `bathrooms` (number, optional)
    - `amenities` (array, optional)
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "apartments": [
          {
            "id": 1,
            "photos": ["url1", "url2"],
            "governorate": "Cairo",
            "city": "New Cairo",
            "nightly_price": 500.00,
            "monthly_price": 8000.00,
            "bedrooms": 2,
            "bathrooms": 1,
            "living_rooms": 1,
            "size": 80.00,
            "amenities": ["WiFi", "Parking", "A/C"],
            "average_rating": 4.5,
            "rating_count": 35,
            "is_favorited": false
          }
        ],
        "pagination": {
          "current_page": 1,
          "total_pages": 10,
          "total_items": 195
        }
      }
    }
    ```

- `POST /api/tenant/favorites/{apartment_id}`
  - Request: None
  - Response: `{ "success": true, "favorited": true }`

- `DELETE /api/tenant/favorites/{apartment_id}`
  - Request: None
  - Response: `{ "success": true, "favorited": false }`

#### Notes
- Default sort: Most recent apartments first
- Only apartments with status = "active" are shown
- Apartment cards should have smooth animations on favorite toggle
- Images should be cached for performance
- Consider adding search bar in future versions

---

### 4.1.2 Tenant Home - Filter Bottom Sheet

**Screen ID**: `TENANT-002`

#### Purpose
Allow tenants to filter apartments by location, price, and specifications. Opens as a modal bottom sheet from the Home screen.

#### User Entry Points
- From Tenant Home Screen → Tap "Filters" button

#### UI Components

**Bottom Sheet Header:**
- Title: "Filters"
- Close button (✕) - top right
- "Clear All" link - top left (enabled if any filters applied)

**Scrollable Content:**

**1. Location Section:**
- Section header: "Location"
- **Governorate Dropdown:**
  - Label: "Governorate"
  - Placeholder: "Select governorate"
  - Options: List of all governorates in system
- **City Dropdown:**
  - Label: "City"
  - Placeholder: "Select city"
  - Dependent on governorate selection
  - Disabled until governorate selected

**2. Price Range Section:**
- Section header: "Price Range"
- **Min Price Input:**
  - Label: "Minimum Price"
  - Placeholder: "0"
  - Numeric keyboard
- **Max Price Input:**
  - Label: "Maximum Price"
  - Placeholder: "Any"
  - Numeric keyboard
- **Price Range Note:**
  - Info: "Price filtering uses nightly rates. System automatically applies best rate (nightly or monthly) based on booking duration."

**3. Specifications Section:**
- Section header: "Specifications"
- **Bedrooms:**
  - Chip selector: Any | 1+ | 2+ | 3+ | 4+
  - Multi-select allowed (minimum selection logic)
- **Bathrooms:**
  - Chip selector: Any | 1+ | 2+ | 3+
- **Living Rooms:**
  - Chip selector: Any | 1+ | 2+

**4. Amenities Section:**
- Section header: "Amenities"
- Multi-select checkboxes:
  - ☐ WiFi
  - ☐ Parking
  - ☐ Air Conditioning
  - ☐ Heating
  - ☐ Pool
  - ☐ Gym
  - ☐ Elevator
  - ☐ Security
  - (Dynamic list based on available amenities)

**Bottom Action Bar (Sticky):**
- Secondary button: "Reset" (clears current filter selections)
- Primary button: "Show Results" or "Show X Apartments" (where X is count)

#### User Actions
- Select filters in any section
- Tap "Clear All" → Resets all filters to default
- Tap "Reset" → Clears current unsaved selections
- Tap "Show Results" →
  - Close bottom sheet
  - Apply filters to apartment list
  - Display active filter chips on home screen
  - Scroll to top of apartment list
- Tap Close (✕) → Closes bottom sheet without applying filters
- Swipe down → Closes bottom sheet

#### Empty States
- N/A (filter UI always shows options)

#### Loading States
**City Loading:**
- Show spinner in city dropdown while loading cities for selected governorate

**Results Count Loading:**
- "Show Results" button shows spinner while counting matching apartments

#### Error States
- **City Load Error**: "Unable to load cities" - toast message
- **No Results**: "Show Results" button shows "No apartments found" when tapped

#### Validation Rules
- Min price must be less than max price (if both specified)
- At least one filter must be changed to enable "Show Results"
- Governorate must be selected before city

#### Related Entities
- `apartments` table (filter criteria)

#### API Endpoints
- `GET /api/location/cities?governorate={governorate}`
  - Response: `{ "success": true, "cities": ["New Cairo", "Nasr City", ...] }`

- `GET /api/tenant/apartments/count?filters={...}`
  - Query params: Same as apartment list filters
  - Response: `{ "success": true, "count": 45 }`

#### Notes
- Filter state persists during session (not between app restarts)
- Multiple amenities use AND logic (apartment must have all selected amenities)
- Consider adding "Save Search" feature in future
- Filter combinations should update results count in real-time

---

### 4.1.3 Apartment Detail Screen

**Screen ID**: `TENANT-003`

#### Purpose
Display comprehensive information about a specific apartment, including photos, specifications, location, amenities, availability, ratings, and booking action.

#### User Entry Points
- From Tenant Home Screen → Tap apartment card
- From Favorites Screen → Tap apartment card
- From Booking History → Tap apartment name
- From Notification → Tap apartment-related notification

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: Hidden initially, appears on scroll with apartment address
- Right actions:
  - Share button (share apartment link)
  - Favorite heart icon (filled if favorited)

**Scrollable Content:**

**1. Photo Gallery Section:**
- Horizontal scrollable photo carousel
- Full-width photos
- Swipe to view all photos
- Photo indicators (dots): 1/5
- No full-screen gallery (per your choice)

**2. Header Information:**
- **Title**: Full address
- **Location**: City, Governorate (with map pin icon)
- **Rating**: ⭐ 4.5 (35 reviews) | "New listing" if no ratings
  - Tap → Navigate to Reviews List (TENANT-004)

**3. Price & Basic Info Card:**
- **Price**: "EGP 500 / night" (large, bold)
- **Specs Row**: 
  - 2 🛏️ Bedrooms | 1 🛁 Bathroom | 1 🛋️ Living Room
  - 80 m² (size)

**4. Owner Information:**
- Small card with:
  - Owner photo (circular)
  - Owner name
  - "Contact Owner" button → Opens chat (SHARED-002)

**5. Description Section:**
- Section header: "Description"
- Full apartment description text
- "Read more"/"Read less" if text is long (>3 lines)

**6. Amenities Section:**
- Section header: "Amenities"
- Grid of amenity chips with icons:
  - ✓ WiFi
  - ✓ Parking
  - ✓ Air Conditioning
  - ✓ Heating
  - (Show all available amenities)

**7. Location Section:**
- Section header: "Location"
- Address: Full address text
- Map preview (static or interactive mini-map)
- "View on Map" button (optional - opens full map)

**8. Reviews Section:**
- Section header: "Reviews (35)"
- Average rating display: ⭐ 4.5 out of 5
- Preview of top 2-3 reviews:
  - Reviewer photo, name
  - Rating (stars)
  - Review text (truncated)
  - Date
- "See All Reviews" button → Navigate to Reviews List (TENANT-004)

**Sticky Bottom Bar:**
- **Price**: "EGP 500/night" (left side)
- **Primary Button**: "Book Now" (right side)

#### User Actions
- Scroll to view all sections
- Swipe photos left/right → View all photos
- Tap favorite heart → Toggle favorite status, update icon
- Tap share button → Opens system share sheet
- Tap rating/reviews → Navigate to Reviews List Screen
- Tap "Contact Owner" → Navigate to Chat Screen with owner
- Tap "See All Reviews" → Navigate to Reviews List Screen
- Tap "Book Now" → Navigate to Booking Date Selection Screen (TENANT-005)

#### Empty States
**No Reviews:**
- Message: "No reviews yet"
- Description: "Be the first to book and review this apartment"

**No Amenities:**
- Message: "No amenities listed"

#### Loading States
**Initial Load:**
- Skeleton loader for entire screen layout
- Shimmer effect on main content sections

**Favorite Toggle:**
- Brief spinner on heart icon while updating

#### Error States
- **Load Error**: Full-screen error with retry button
  - "Unable to load apartment details"
- **Favorite Error**: Toast "Unable to update favorites"
- **Share Error**: Toast "Unable to share apartment"

#### Related Entities
- `apartments` table (fetch apartment details)
- `users` table (fetch owner information)
- `ratings` table (fetch reviews and average rating)
- `favorites` table (check favorite status)

#### API Endpoints
- `GET /api/tenant/apartments/{apartment_id}`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "id": 1,
        "owner": {
          "id": 5,
          "first_name": "John",
          "last_name": "Doe",
          "personal_photo": "url"
        },
        "governorate": "Cairo",
        "city": "New Cairo",
        "address": "Full address text",
        "nightly_price": 500.00,
        "monthly_price": 8000.00,
        "bedrooms": 2,
        "bathrooms": 1,
        "living_rooms": 1,
        "size": 80.00,
        "description": "Beautiful apartment...",
        "photos": ["url1", "url2", "url3"],
        "amenities": ["WiFi", "Parking", "A/C"],
        "average_rating": 4.5,
        "rating_count": 35,
        "is_favorited": true,
        "recent_reviews": [
          {
            "id": 10,
            "tenant_name": "Jane Smith",
            "tenant_photo": "url",
            "rating": 5,
            "review_text": "Great place!",
            "created_at": "2024-12-01"
          }
        ]
      }
    }
    ```

#### Notes
- Apartment photos should be optimized/compressed
- Consider adding availability calendar in future
- Share functionality should generate deep link to apartment
- Owner contact should be available even before booking
- Consider adding "Similar Apartments" section in future

---

### 4.1.4 Reviews List Screen

**Screen ID**: `TENANT-004`

#### Purpose
Display all ratings and reviews for a specific apartment.

#### User Entry Points
- From Apartment Detail Screen → Tap "See All Reviews" or rating section

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Reviews"

**Header Section:**
- **Overall Rating Card:**
  - Large rating number: "4.5"
  - Star visualization: ⭐⭐⭐⭐⭐ (4.5/5)
  - Total count: "Based on 35 reviews"
  - Rating distribution bars:
    - 5 ⭐ ▓▓▓▓▓▓▓▓▓░ 25
    - 4 ⭐ ▓▓▓▓░░░░░░ 8
    - 3 ⭐ ▓░░░░░░░░░ 2
    - 2 ⭐ ░░░░░░░░░░ 0
    - 1 ⭐ ░░░░░░░░░░ 0

**Reviews List:**
- Vertical scrolling list
- Each review card shows:
  - Reviewer photo (circular)
  - Reviewer name
  - Rating (stars): ⭐⭐⭐⭐⭐
  - Review date: "2 weeks ago"
  - Review text (full text, expandable if long)
- Sorted by: Most recent first

#### User Actions
- Scroll to view all reviews
- Pull-to-refresh → Reload reviews

#### Empty States
**No Reviews:**
- Icon: ⭐ (star icon)
- Message: "No reviews yet"
- Description: "This apartment hasn't been reviewed. Book now and be the first to leave a review!"

#### Loading States
**Initial Load:**
- Skeleton loaders for 5 review cards
- Shimmer effect

**Pagination:**
- Spinner at bottom while loading more reviews

#### Error States
- **Load Error**: "Unable to load reviews. Please try again."
  - Retry button

#### Related Entities
- `ratings` table (fetch all ratings for apartment)
- `users` table (fetch reviewer information)

#### API Endpoints
- `GET /api/apartments/{apartment_id}/reviews`
  - Query params: `page`, `per_page`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "average_rating": 4.5,
        "rating_count": 35,
        "rating_distribution": {
          "5": 25,
          "4": 8,
          "3": 2,
          "2": 0,
          "1": 0
        },
        "reviews": [
          {
            "id": 10,
            "tenant_name": "Jane Smith",
            "tenant_photo": "url",
            "rating": 5,
            "review_text": "Great apartment!",
            "created_at": "2024-12-01T10:30:00Z"
          }
        ]
      }
    }
    ```

#### Notes
- Consider adding filter/sort options (most recent, highest rated, lowest rated)
- Review text should support multi-line display
- Consider adding helpful/not helpful voting in future

---

### 4.1.5 Booking Date Selection Screen

**Screen ID**: `TENANT-005`

#### Purpose
Allow tenant to select check-in and check-out dates for booking, showing apartment availability.

#### User Entry Points
- From Apartment Detail Screen → Tap "Book Now"

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Select Dates"

**Main Content:**

**Apartment Summary Card:**
- Apartment photo (small thumbnail)
- Apartment address
- Price: "EGP 500/night"

**Calendar Section:**
- **Date Range Calendar:**
  - Month/Year header with navigation arrows
  - Calendar grid showing current and next months
  - Blocked dates (existing approved bookings):
    - Shown in gray/disabled
    - Not selectable
  - Selected range:
    - Check-in date: Green circle
    - Check-out date: Green circle
    - Dates in between: Light green background
  - Today: Outlined
  - Past dates: Disabled (gray)

**Selection Summary:**
- "Check-in": Selected date or "Select date"
- "Check-out": Selected date or "Select date"
- "Number of nights": Auto-calculated
- "Total Price": Auto-calculated (nights × price)

**Sticky Bottom Bar:**
- Primary button: "Continue" (enabled when both dates selected)

#### User Actions
- Tap calendar arrows → Navigate to different months
- Tap date → 
  - If no check-in selected: Set as check-in date
  - If check-in selected but no check-out: Set as check-out date
  - If both selected: Clear selection and set as new check-in
- Validation: Check-out must be after check-in
- Blocked dates: Cannot be selected (show toast if tapped)
- Tap "Continue" → Navigate to Booking Form Screen (TENANT-006)

#### Empty States
- N/A (calendar always shows dates)

#### Loading States
**Initial Load:**
- Skeleton loader for calendar while fetching blocked dates

**Month Navigation:**
- Brief spinner while loading availability for new month

#### Error States
- **Availability Load Error**: "Unable to load availability. Please try again."
- **Invalid Date Selection**: Toast "Selected dates are not available"
- **Date Range Error**: Toast "Check-out must be after check-in"

#### Validation Rules
- Check-in date must be today or in future
- Check-out date must be after check-in date
- Selected range cannot include any blocked dates
- Minimum stay: 1 night

#### Related Entities
- `bookings` table (fetch blocked dates for apartment)
- `apartments` table (fetch apartment info and price)

#### API Endpoints
- `GET /api/apartments/{apartment_id}/availability`
  - Query params: `start_date`, `end_date` (date range to check)
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "blocked_dates": [
          "2024-12-20",
          "2024-12-21",
          "2024-12-22",
          "2024-12-25",
          "2024-12-26"
        ]
      }
    }
    ```

- `POST /api/tenant/bookings/validate-dates`
  - Request: `{ "apartment_id": 1, "check_in": "2024-12-15", "check_out": "2024-12-18" }`
  - Response: `{ "success": true, "available": true, "total_price": 1500.00, "nights": 3 }`

#### Notes
- Blocked dates include all approved and pending bookings
- Calendar should load 3 months ahead initially
- Consider showing minimum/maximum stay requirements if applicable
- Price calculation uses nightly_price and monthly_price, automatically selecting the better rate based on booking duration

---

### 4.1.6 Booking Form Screen

**Screen ID**: `TENANT-006`

#### Purpose
Collect additional booking information (number of guests, payment method) before submitting booking request.

#### User Entry Points
- From Booking Date Selection Screen → Tap "Continue"

#### UI Components

**Top App Bar:**
- Back button (left) → Returns to date selection
- Title: "Booking Details"

**Main Content:**

**Booking Summary Card:**
- Apartment photo, name, address
- Check-in: [Selected Date]
- Check-out: [Selected Date]
- Duration: "3 nights"

**Booking Information Form:**

**1. Number of Guests (Optional):**
- Label: "Number of Guests"
- Number input field with +/- buttons
- Min: 1, Max: 10 (or apartment-specific limit)
- Default: 1

**2. Payment Method (Required):**
- Label: "Payment Method"
- Radio button options:
  - ○ Cash
  - ○ Credit Card
  - ○ Bank Transfer
  - ○ Online Payment
- Note: "No actual payment required. This is for confirmation only."

**Price Breakdown Card:**
- Nightly rate: "EGP 500/night"
- Monthly rate: "EGP 8,000/month"
- Booking duration: "3 nights"
- Calculation: "EGP 500 × 3 nights = EGP 1,500" (for short stays) or shows monthly rate calculation if applicable
- Total: "EGP 1,500" (large, bold)
- Info: "System automatically uses the best rate for your booking duration"

**Balance & Payment Info Card:**
- Current Balance: "EGP 2,000.00" (displayed prominently)
- Required Amount: "EGP 1,500.00"
- Balance Status:
  - If sufficient: ✓ "Sufficient balance" (green)
  - If insufficient: ⚠️ "Insufficient balance" (red, with amount needed)
- Info text: "Rent will be deducted from your balance when booking is submitted"
- If insufficient: "Top up your balance" button → Navigate to profile/balance screen

**Terms & Conditions:**
- Checkbox: "I agree to the Terms & Conditions and Cancellation Policy"
- Links: Tappable "Terms & Conditions" and "Cancellation Policy"

**Sticky Bottom Bar:**
- Primary button: "Submit Booking Request" (enabled when payment method selected and terms accepted)

#### User Actions
- Tap +/- buttons → Adjust guest count
- Select payment method → Radio button selected
- Tap terms checkbox → Toggle checkbox
- Tap "Terms & Conditions" link → Opens terms modal/screen
- Tap "Cancellation Policy" link → Opens policy modal/screen
- Tap "Submit Booking Request" →
  - Show loading overlay
  - Submit booking to backend
  - On success: Navigate to Booking Confirmation Screen (TENANT-007)
  - On error: Show error message

#### Empty States
- N/A (form screen)

#### Loading States
**Form Submission:**
- Full-screen overlay with spinner
- Message: "Submitting your booking request..."

#### Error States
- **Insufficient Balance**: 
  - Red banner: "Insufficient balance. Required: EGP 1,500.00, Available: EGP 500.00"
  - "Top up Balance" button to navigate to profile
  - Submit button disabled
- **Payment Method Required**: "Please select a payment method"
- **Terms Not Accepted**: "Please accept Terms & Conditions to continue"
- **Submission Error**: "Unable to submit booking. Please try again."
- **Date Conflict**: "Selected dates are no longer available. Please select different dates."
  - Redirect back to date selection screen

#### Validation Rules
- Tenant balance must be sufficient (balance >= total_rent)
- Payment method must be selected
- Terms & Conditions must be accepted
- Number of guests must be at least 1
- Selected dates must still be available (revalidate on submission)

#### Related Entities
- `bookings` table (create new booking)
- `apartments` table (reference apartment)
- `users` table (tenant, check balance)
- `transactions` table (create rent_payment transactions)

#### API Endpoints
- `POST /api/tenant/bookings`
  - Request:
    ```json
    {
      "apartment_id": 1,
      "check_in_date": "2024-12-15",
      "check_out_date": "2024-12-18",
      "number_of_guests": 2,
      "payment_method": "Cash"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "Booking request submitted successfully",
      "data": {
        "booking_id": 123,
        "status": "pending"
      }
    }
    ```

#### Notes
- System checks tenant balance before allowing submission
- Rent is deducted from tenant balance and added to owner balance immediately when booking is created
- Transaction records are created for both tenant and owner
- Booking is created with status = "pending" and total_rent stored
- Owner receives notification immediately
- Consider adding special requests text field in future

---

### 4.1.7 Booking Confirmation Screen

**Screen ID**: `TENANT-007`

#### Purpose
Confirm successful booking submission and inform tenant about next steps.

#### User Entry Points
- From Booking Form Screen → After successful submission

#### UI Components

**Main Content:**
- Success icon: ✅ (large, centered, animated)
- Headline: "Booking Request Sent!"
- Message: "Your booking request has been sent to the apartment owner for approval."
- Illustration (person waiting/pending icon)

**Booking Summary Card:**
- Apartment photo, name
- Check-in: [Date]
- Check-out: [Date]
- Duration: "3 nights"
- Status badge: "Pending Approval" (yellow/orange)

**Payment Confirmation Card:**
- ✓ "Payment Processed"
- Amount Paid: "EGP 1,500.00"
- Remaining Balance: "EGP 500.00"
- Info: "Rent has been deducted from your balance. If booking is rejected, full refund will be processed automatically."

**Information Box:**
- "What happens next?"
  - ✓ "Rent has been deducted from your balance"
  - ✓ "The owner will review your request"
  - ✓ "You'll be notified of their decision"
  - ✓ "If rejected, full refund will be processed automatically"
  - ✓ "This usually takes 24-48 hours"

**Action Buttons:**
- Primary button: "View My Bookings"
- Secondary button/link: "Back to Home"

#### User Actions
- Tap "View My Bookings" → Navigate to Booking History Screen (TENANT-008)
- Tap "Back to Home" → Navigate to Tenant Home Screen
- Press back button → Navigate to Home Screen

#### Empty States
- N/A (confirmation screen)

#### Loading States
- N/A (static screen)

#### Error States
- N/A (success screen)

#### Related Entities
- `bookings` table (created booking with total_rent)
- `users` table (updated tenant and owner balances)
- `transactions` table (rent_payment transactions created)
- `notifications` table (notification sent to owner)

#### Notes
- Push notification sent to apartment owner
- Tenant receives notification when owner approves/rejects
- Booking can be viewed in "Bookings" tab immediately with "pending" status

---

### 4.1.8 Booking History Screen

**Screen ID**: `TENANT-008`

#### Purpose
Display all tenant's bookings organized by status (current, past, cancelled).

#### User Entry Points
- From Bottom Navigation → Bookings tab
- From Booking Confirmation Screen → Tap "View My Bookings"
- From Notification → Tap booking-related notification

#### UI Components

**Top App Bar:**
- Title: "My Bookings"
- Right action: Notification bell icon

**Tab Bar (Horizontal):**
- Tab 1: "Current" (active and upcoming bookings)
- Tab 2: "Past" (completed bookings)
- Tab 3: "Cancelled" (cancelled and rejected bookings)
- Badge on "Current" tab if pending requests exist

**Tab Content - Scrollable List:**

**Current Tab:**
- Bookings with status: pending, approved, modified_pending
- Sorted by: Check-in date (nearest first)

**Past Tab:**
- Bookings with status: completed
- Sorted by: Check-out date (most recent first)

**Cancelled Tab:**
- Bookings with status: cancelled, rejected
- Sorted by: Updated date (most recent first)

**Booking Card (for each booking):**
- Apartment photo (left side)
- Apartment name/address
- Check-in: [Date] | Check-out: [Date]
- Status badge:
  - "Pending Approval" (yellow) - for pending
  - "Confirmed" (green) - for approved
  - "Modification Pending" (orange) - for modified_pending
  - "Completed" (gray) - for completed
  - "Cancelled" (red) - for cancelled
  - "Rejected" (red) - for rejected
- Action buttons (vary by status):
  - Pending: "View Details" | "Cancel Request"
  - Approved: "View Details" | "Modify" | "Cancel"
  - Completed: "View Details" | "Rate Apartment" (if not rated)
  - Cancelled/Rejected: "View Details"

**Pull-to-Refresh:**
- Reload bookings list

#### User Actions
- Switch tabs → Load bookings for selected tab
- Pull down → Refresh booking list
- Tap booking card → Navigate to Booking Detail Screen (TENANT-009)
- Tap "Cancel Request" → Show cancel confirmation dialog
- Tap "Modify" → Navigate to Modify Booking Screen (TENANT-010)
- Tap "Rate Apartment" → Navigate to Rating Screen (TENANT-011)
- Tap notification bell → Navigate to Notifications Screen

#### Empty States

**Current Tab - No Bookings:**
- Icon: 📅 (calendar icon)
- Message: "No current bookings"
- Description: "Start exploring and book your perfect apartment"
- Action button: "Browse Apartments"

**Past Tab - No Bookings:**
- Icon: 🏠 (house icon)
- Message: "No past bookings"
- Description: "Your completed stays will appear here"

**Cancelled Tab - No Bookings:**
- Icon: ✓ (checkmark icon)
- Message: "No cancelled bookings"
- Description: "You haven't cancelled any bookings"

#### Loading States
**Initial Load:**
- Skeleton loaders for 3-5 booking cards per tab
- Shimmer effect

**Tab Switch:**
- Brief spinner while loading tab data

**Pull-to-Refresh:**
- Standard refresh indicator

#### Error States
- **Load Error**: "Unable to load bookings. Please try again."
  - Retry button shown

#### Related Entities
- `bookings` table (fetch all tenant's bookings)
- `apartments` table (fetch apartment details)
- `ratings` table (check if booking has been rated)

#### API Endpoints
- `GET /api/tenant/bookings`
  - Query params: 
    - `status` (string: current, past, cancelled)
    - `page` (number)
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "bookings": [
          {
            "id": 123,
            "apartment": {
              "id": 1,
              "name": "Apartment Name",
              "address": "Address",
              "photos": ["url"]
            },
            "check_in_date": "2024-12-15",
            "check_out_date": "2024-12-18",
            "status": "pending",
            "number_of_guests": 2,
            "payment_method": "Cash",
            "can_cancel": true,
            "can_modify": false,
            "can_rate": false,
            "created_at": "2024-12-10T10:00:00Z"
          }
        ]
      }
    }
    ```

#### Notes
- Status determines available actions on each booking
- Cancellation only allowed 24+ hours before check-in
- Modification requires owner approval
- Consider adding search/filter for bookings in future

---

### 4.1.9 Booking Detail Screen

**Screen ID**: `TENANT-009`

#### Purpose
Display comprehensive details of a specific booking with status-appropriate actions.

#### User Entry Points
- From Booking History Screen → Tap booking card
- From Notification → Tap booking notification

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Booking Details"

**Scrollable Content:**

**Status Banner (Top):**
- Large status badge with icon:
  - Pending: ⏳ "Waiting for Owner Approval"
  - Approved: ✅ "Confirmed"
  - Modified Pending: ⏳ "Modification Pending"
  - Completed: ✓ "Completed"
  - Cancelled: ✕ "Cancelled"
  - Rejected: ✕ "Rejected"
- Status-specific message below badge

**Booking Information Card:**
- **Booking ID**: #123456
- **Check-in**: Thursday, Dec 15, 2024 (2:00 PM)
- **Check-out**: Sunday, Dec 18, 2024 (12:00 PM)
- **Duration**: 3 nights
- **Number of Guests**: 2 people
- **Payment Method**: Cash
- **Total Rent Paid**: EGP 1,500
- **Booked on**: Dec 10, 2024

**Payment Information Card:**
- **Payment Status**: 
  - If pending/approved: ✓ "Paid - EGP 1,500.00"
  - If rejected: "Refunded - EGP 1,500.00" (green, with refund icon)
  - If cancelled: "Partial Refund - EGP 1,200.00" (80% refunded) + "Cancellation Fee - EGP 300.00" (20%)
- **Payment Date**: Dec 10, 2024
- If refunded: **Refund Date**: [Date when refund processed]
- **View Transaction Details** link → Navigate to Transaction History filtered by this booking

**Apartment Information Card:**
- Apartment photo
- Apartment name/address
- Rating: ⭐ 4.5 (35 reviews)
- Specs: 2 🛏️ | 1 🛁 | 1 🛋️
- "View Apartment" button → Navigate to Apartment Detail

**Owner Information Card:**
- Owner photo, name
- "Contact Owner" button → Navigate to Chat

**Cancellation Policy Card:**
- Policy text
- Cancellation deadline (if applicable)

**Action Buttons (Status-dependent):**

**If Status = Pending:**
- Secondary button: "Cancel Request"

**If Status = Approved:**
- Primary button: "Modify Booking"
- Secondary button: "Cancel Booking"

**If Status = Completed (and not rated):**
- Primary button: "Rate This Apartment"

**If Status = Modified Pending:**
- Info: "Waiting for owner to approve your modification"
- View modification details

**If Status = Cancelled/Rejected:**
- No action buttons (view only)

#### User Actions
- Tap "View Apartment" → Navigate to Apartment Detail Screen
- Tap "Contact Owner" → Navigate to Chat Screen
- Tap "Cancel Request" (Pending) → Show cancel confirmation dialog
- Tap "Modify Booking" (Approved) → Navigate to Modify Booking Screen (TENANT-010)
- Tap "Cancel Booking" (Approved) → Show cancel confirmation dialog (TENANT-012)
- Tap "Rate This Apartment" (Completed) → Navigate to Rating Screen (TENANT-011)

#### Empty States
- N/A (always displays booking data)

#### Loading States
**Initial Load:**
- Skeleton loader for entire screen
- Shimmer effect on cards

#### Error States
- **Load Error**: "Unable to load booking details"
  - Retry button

#### Related Entities
- `bookings` table (fetch booking details with total_rent)
- `apartments` table (fetch apartment details)
- `users` table (fetch owner details)
- `transactions` table (fetch payment/refund transactions related to booking)

#### API Endpoints
- `GET /api/tenant/bookings/{booking_id}`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "id": 123,
        "apartment": { /* apartment details */ },
        "owner": { /* owner details */ },
        "check_in_date": "2024-12-15",
        "check_out_date": "2024-12-18",
        "number_of_guests": 2,
        "payment_method": "Cash",
        "status": "approved",
        "total_price": 1500.00,
        "can_cancel": true,
        "can_modify": true,
        "cancellation_deadline": "2024-12-14T14:00:00Z",
        "created_at": "2024-12-10T10:00:00Z"
      }
    }
    ```

#### Notes
- Actions displayed based on booking status and timing
- Cancellation deadline calculated as 24 hours before check-in
- Consider adding QR code for check-in in future

---

### 4.1.10 Modify Booking Screen

**Screen ID**: `TENANT-010`

#### Purpose
Allow tenant to modify booking dates or guest count (requires owner approval).

#### User Entry Points
- From Booking Detail Screen → Tap "Modify Booking" (if status = approved)

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Modify Booking"

**Warning Banner:**
- ⚠️ "Modification requires owner approval"

**Current Booking Summary:**
- Section header: "Current Booking"
- Check-in: [Current Date]
- Check-out: [Current Date]
- Guests: [Current Count]
- Editable: No (shown for reference)

**Modification Form:**
- Section header: "New Details"

**1. New Dates:**
- "Change Dates" button → Opens calendar (similar to TENANT-005)
- Shows selected new dates once chosen
- Calendar shows blocked dates (existing bookings)

**2. Number of Guests:**
- Number input with +/- buttons
- Current value pre-filled

**Price Update:**
- "New Total": Auto-calculated based on changes
- Shows difference: "+EGP 500" or "-EGP 500"

**Sticky Bottom Bar:**
- Primary button: "Submit Modification Request"

#### User Actions
- Tap "Change Dates" → Opens date selection calendar
- Adjust guest count → Updates price
- Tap "Submit Modification Request" →
  - Show loading state
  - Submit modification
  - On success: Navigate back to Booking Detail with updated status
  - On error: Show error message

#### Empty States
- N/A (form screen)

#### Loading States
**Date Availability:**
- Spinner while loading available dates

**Submission:**
- Full-screen overlay: "Submitting modification request..."

#### Error States
- **Invalid Dates**: "Selected dates are not available"
- **No Changes**: "Please make changes before submitting"
- **Submission Error**: "Unable to submit modification"
- **Date Conflict**: "Selected dates are no longer available"

#### Validation Rules
- At least one field must be changed
- New dates must be available (not blocked)
- Check-out must be after check-in
- Guest count must be >= 1

#### Related Entities
- `bookings` table (update booking status to modified_pending)
- `apartments` table (check date availability)

#### API Endpoints
- `PUT /api/tenant/bookings/{booking_id}/modify`
  - Request:
    ```json
    {
      "check_in_date": "2024-12-16",
      "check_out_date": "2024-12-19",
      "number_of_guests": 3
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "Modification request sent to owner",
      "data": {
        "booking_id": 123,
        "status": "modified_pending"
      }
    }
    ```

#### Notes
- Booking status changes to "modified_pending"
- Owner receives notification
- Original booking details preserved until owner approves
- If owner rejects, booking reverts to original details

---

### 4.1.11 Cancel Booking Confirmation Dialog

**Screen ID**: `TENANT-012`

#### Purpose
Confirm booking cancellation with cancellation policy warning.

#### User Entry Points
- From Booking Detail Screen → Tap "Cancel Booking"
- From Booking History → Tap "Cancel Request"

#### UI Components

**Dialog/Modal:**
- Title: "Cancel Booking?"
- Message: "Are you sure you want to cancel this booking?"

**Cancellation Info:**
- Check-in: [Date]
- Check-out: [Date]
- Refund policy: "According to cancellation policy..." (if applicable)

**Warning (if within 24h):**
- ⚠️ "Cancellation less than 24 hours before check-in may result in penalties"

**Action Buttons:**
- Secondary button: "Keep Booking"
- Primary button: "Yes, Cancel" (destructive style - red)

#### User Actions
- Tap "Keep Booking" → Close dialog, no action
- Tap "Yes, Cancel" →
  - Check if allowed (24h rule)
  - If allowed:
    - Show loading
    - Cancel booking
    - Update status to "cancelled"
    - Show success toast: "Booking cancelled"
    - Navigate back to Booking History
  - If not allowed:
    - Show error: "Cannot cancel within 24 hours of check-in"

#### Empty States
- N/A (dialog)

#### Loading States
**Cancellation Processing:**
- Spinner on "Yes, Cancel" button

#### Error States
- **Too Late**: "Cannot cancel within 24 hours of check-in"
- **Already Checked In**: "Cannot cancel after check-in"
- **Cancellation Failed**: "Unable to cancel booking. Please try again."

#### Validation Rules
- Can only cancel if check-in is more than 24 hours away
- Cannot cancel if booking already started

#### Related Entities
- `bookings` table (update status to "cancelled")
- `notifications` table (notify owner)

#### API Endpoints
- `DELETE /api/tenant/bookings/{booking_id}`
  - Response:
    ```json
    {
      "success": true,
      "message": "Booking cancelled successfully"
    }
    ```

#### Notes
- Notification sent to owner when booking cancelled
- Consider implementing refund logic in future
- Cancelled bookings appear in "Cancelled" tab

---

### 4.1.12 Rating & Review Submission Screen

**Screen ID**: `TENANT-011`

#### Purpose
Allow tenant to rate and review an apartment after completed stay.

#### User Entry Points
- From Booking History → Tap "Rate Apartment" (for completed bookings)
- From Booking Detail → Tap "Rate This Apartment"

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Rate Your Stay"

**Main Content:**

**Apartment Summary:**
- Apartment photo, name
- Check-in/out dates (past booking)

**Rating Section:**
- Label: "How was your stay?"
- Star selector: ☆☆☆☆☆ (tap to select 1-5 stars)
- Large, interactive stars
- Selected stars fill with color: ★★★★★

**Review Section:**
- Label: "Share your experience (optional)"
- Multi-line text input
- Placeholder: "Tell others about your stay..."
- Character count: 0/500
- Max length: 500 characters

**Sticky Bottom Bar:**
- Primary button: "Submit Review" (enabled when at least rating selected)

#### User Actions
- Tap stars → Select rating (1-5)
- Type in text field → Enter review text
- Tap "Submit Review" →
  - Show loading state
  - Submit rating and review
  - On success: Show success toast, navigate back to Booking History
  - On error: Show error message

#### Empty States
- N/A (form screen)

#### Loading States
**Submission:**
- Spinner on submit button
- Overlay: "Submitting your review..."

#### Error States
- **No Rating**: "Please select a rating"
- **Submission Error**: "Unable to submit review. Please try again."
- **Already Rated**: "You have already rated this booking"

#### Validation Rules
- Rating (1-5 stars) is required
- Review text is optional
- Review text max 500 characters
- Can only rate once per booking
- Can only rate after check-out date has passed

#### Related Entities
- `ratings` table (create new rating)
- `bookings` table (link rating to booking)
- `apartments` table (update average rating)

#### API Endpoints
- `POST /api/tenant/bookings/{booking_id}/rate`
  - Request:
    ```json
    {
      "rating": 5,
      "review_text": "Great apartment! Highly recommended."
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "Thank you for your review!",
      "data": {
        "rating_id": 45
      }
    }
    ```

#### Notes
- Rating immediately visible on apartment detail page
- Average rating recalculated automatically
- Consider allowing photo uploads with reviews in future
- Cannot edit rating after submission (for now)

---

### 4.1.13 Favorites List Screen

**Screen ID**: `TENANT-013`

#### Purpose
Display all apartments saved by the tenant as favorites for quick access and booking.

#### User Entry Points
- From Bottom Navigation → Favorites tab

#### UI Components

**Top App Bar:**
- Title: "My Favorites"
- Right action: Notification bell icon

**Main Content:**
- Vertical scrolling list of favorited apartments
- Each apartment card identical to browse screen (TENANT-001):
  - Photo, location, price, rating, specs, amenities
  - Heart icon (filled, since it's in favorites)
  - Tap heart → Remove from favorites
- Pull-to-refresh

#### User Actions
- Scroll to view all favorites
- Pull down → Refresh favorites list
- Tap apartment card → Navigate to Apartment Detail Screen (TENANT-003)
- Tap heart icon → 
  - Remove from favorites
  - Card animates out of list
  - Show toast: "Removed from favorites"
  - Undo option in toast (optional)

#### Empty States
**No Favorites:**
- Icon: ❤️ (heart icon)
- Message: "No favorites yet"
- Description: "Save apartments you like to easily find them later"
- Action button: "Browse Apartments" → Navigate to Home

#### Loading States
**Initial Load:**
- Skeleton loaders for 5 apartment cards
- Shimmer effect

**Pull-to-Refresh:**
- Standard refresh indicator

#### Error States
- **Load Error**: "Unable to load favorites. Please try again."
  - Retry button
- **Remove Error**: Toast "Unable to remove from favorites"

#### Related Entities
- `favorites` table (fetch favorited apartments)
- `apartments` table (fetch apartment details)
- `ratings` table (fetch ratings)

#### API Endpoints
- `GET /api/tenant/favorites`
  - Query params: `page`, `per_page`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "apartments": [
          {
            "id": 1,
            "photos": ["url1"],
            "governorate": "Cairo",
            "city": "New Cairo",
            "nightly_price": 500.00,
            "monthly_price": 8000.00,
            "bedrooms": 2,
            "bathrooms": 1,
            "living_rooms": 1,
            "size": 80.00,
            "amenities": ["WiFi", "Parking"],
            "average_rating": 4.5,
            "rating_count": 35,
            "favorited_at": "2024-12-01T10:00:00Z"
          }
        ]
      }
    }
    ```

#### Notes
- Favorites persist across sessions
- Can book directly from favorites (same flow as browse)
- Consider adding sort options (recently added, price, rating)
- Undo functionality improves UX if user removes favorite accidentally

---

## 5. Owner Screens

### 5.1 Overview

Owner screens are accessible only to users with `role = 'owner'`. These screens enable owners to:
- Add and manage their apartment listings
- View booking requests for their apartments
- Approve or reject booking requests
- Approve or reject modification requests
- View apartment statistics and performance
- Communicate with tenants

**Owner Bottom Navigation (4 tabs):**
1. **My Apartments** - Manage apartment listings
2. **Bookings** - View and manage booking requests
3. **Messages** - Chat with tenants
4. **Profile** - User settings

**Key Differences from Tenant:**
- Owners do NOT have "Home" or "Favorites" tabs
- Owners CANNOT book apartments
- Owners focus on managing their properties and bookings

---

### 5.1.1 Owner Home - My Apartments Screen

**Screen ID**: `OWNER-001`

#### Purpose
Main landing screen for owners displaying all their apartment listings. First screen shown after login.

#### User Entry Points
- From Login Screen (after successful authentication)
- From Bottom Navigation → My Apartments tab
- From Splash Screen (if already authenticated)

#### UI Components

**Top App Bar:**
- Title: "My Apartments"
- Right actions:
  - Notification bell icon (with badge if unread)

**Main Content:**

**Statistics Card (Top):**
- Total Apartments: [Count]
- Active Listings: [Count]
- Pending Bookings: [Count] (with badge)
- Tap card → View detailed statistics (optional future feature)

**Apartment List:**
- Vertical scrolling list of owner's apartments
- Each apartment card shows:
  - **Photo**: Main apartment photo
  - **Address**: Full address
  - **Status Badge**: 
    - "Active" (green) - visible and bookable
    - "Inactive" (gray) - not visible to tenants
  - **Price**: "EGP 500/night"
  - **Rating**: ⭐ 4.5 (35 reviews) or "No reviews yet"
  - **Specs**: 2 🛏️ | 1 🛁 | 1 🛋️ | 80 m²
  - **Quick Stats**: 
    - "3 pending requests" (if any)
    - "12 total bookings"
  - **Action Buttons Row**:
    - Edit icon button
    - Stats/View icon button
    - Delete icon button (with confirmation)
- Pull-to-refresh

**Floating Action Button (FAB):**
- "+" icon button (bottom right)
- Label: "Add Apartment"
- Primary color

#### User Actions
- Scroll to view all apartments
- Pull down → Refresh apartment list
- Tap apartment card → Navigate to Apartment Detail (Owner View) (OWNER-004)
- Tap Edit icon → Navigate to Edit Apartment Screen (OWNER-003)
- Tap Stats icon → Navigate to Apartment Detail (Owner View)
- Tap Delete icon → Show delete confirmation dialog
- Tap FAB "+" → Navigate to Add Apartment Screen (OWNER-002)
- Tap statistics card → View detailed statistics (future)
- Tap notification bell → Navigate to Notifications Screen

#### Empty States
**No Apartments:**
- Icon: 🏠 (house icon)
- Message: "No apartments yet"
- Description: "Start by adding your first apartment listing"
- Action button: "Add Your First Apartment"

#### Loading States
**Initial Load:**
- Skeleton loaders for statistics card and 3 apartment cards
- Shimmer effect

**Pull-to-Refresh:**
- Standard refresh indicator

#### Error States
- **Load Error**: "Unable to load apartments. Please try again."
  - Retry button
- **Delete Error**: Toast "Unable to delete apartment"

#### Delete Confirmation Dialog
**When tapping Delete icon:**
- Title: "Delete Apartment?"
- Message: "Are you sure you want to delete this apartment?"
- Warning: "⚠️ Cannot delete if there are active bookings"
- Actions:
  - "Cancel" (secondary)
  - "Delete" (destructive/red)

#### Related Entities
- `apartments` table (fetch owner's apartments)
- `bookings` table (count pending requests, total bookings)
- `ratings` table (fetch average ratings)

#### API Endpoints
- `GET /api/owner/apartments`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "statistics": {
          "total_apartments": 5,
          "active_apartments": 4,
          "pending_bookings": 3
        },
        "apartments": [
          {
            "id": 1,
            "photos": ["url1"],
            "address": "Full address",
            "governorate": "Cairo",
            "city": "New Cairo",
            "nightly_price": 500.00,
            "monthly_price": 8000.00,
            "status": "active",
            "bedrooms": 2,
            "bathrooms": 1,
            "living_rooms": 1,
            "size": 80.00,
            "average_rating": 4.5,
            "rating_count": 35,
            "pending_requests_count": 3,
            "total_bookings_count": 12
          }
        ]
      }
    }
    ```

- `DELETE /api/owner/apartments/{apartment_id}`
  - Response:
    ```json
    {
      "success": true,
      "message": "Apartment deleted successfully"
    }
    ```

#### Notes
- Only apartments owned by logged-in user are shown
- Cannot delete apartments with active/pending bookings
- Consider adding status toggle (Active/Inactive) directly on card
- Statistics provide quick overview of owner's portfolio

---

### 5.1.2 Add Apartment Screen

**Screen ID**: `OWNER-002`

#### Purpose
Allow owners to create a new apartment listing with all required information.

#### User Entry Points
- From My Apartments Screen → Tap FAB "+"
- From My Apartments Empty State → Tap "Add Your First Apartment"

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Add Apartment"
- Close button (X) - shows discard confirmation if form has data

**Scrollable Form:**

**1. Photos Section (Required):**
- Label: "Apartment Photos (Required)"
- Photo grid (2 columns)
- Upload placeholders with + icons
- Minimum: 1 photo required
- Maximum: 10 photos
- Options: Take Photo | Choose from Gallery
- First photo automatically set as main photo
- Drag to reorder photos
- Tap photo → View/Delete options

**2. Location Section (Required):**
- Label: "Location"
- **Governorate Dropdown** (required)
  - Placeholder: "Select governorate"
- **City Dropdown** (required)
  - Placeholder: "Select city"
  - Dependent on governorate
- **Address Text Area** (required)
  - Placeholder: "Enter full address"
  - Multi-line (3 rows)
  - Max length: 500 characters

**3. Price Section (Required):**
- Label: "Rental Prices"
- **Nightly Price** (required)
  - Label: "Price per Night"
  - Placeholder: "0.00"
  - Numeric keyboard
  - Currency: EGP
  - Helper text: "Price for short-term bookings (per night)"
- **Monthly Price** (required)
  - Label: "Price per Month"
  - Placeholder: "0.00"
  - Numeric keyboard
  - Currency: EGP
  - Helper text: "Price for long-term bookings (per month, 30 days)"
- Info: "System automatically uses the best rate based on booking duration. For bookings over 30 days, the cheaper option (daily vs monthly) will be used."

**4. Specifications Section (Required):**
- Label: "Apartment Specifications"
- **Bedrooms** (required)
  - Number input with +/- buttons
  - Min: 0, Max: 10
- **Bathrooms** (required)
  - Number input with +/- buttons
  - Min: 1, Max: 10
- **Living Rooms** (required)
  - Number input with +/- buttons
  - Min: 0, Max: 5
- **Size (m²)** (required)
  - Numeric input
  - Placeholder: "e.g., 80"

**5. Description Section (Required):**
- Label: "Description"
- Multi-line text area
- Placeholder: "Describe your apartment, highlight special features..."
- Min length: 50 characters
- Max length: 2000 characters
- Character counter: 0/2000

**6. Amenities Section (Optional):**
- Label: "Amenities"
- Multi-select checkboxes in grid layout:
  - ☐ WiFi
  - ☐ Parking
  - ☐ Air Conditioning
  - ☐ Heating
  - ☐ Pool
  - ☐ Gym
  - ☐ Elevator
  - ☐ Security
  - ☐ Balcony
  - ☐ Garden
  - ☐ Furnished
  - ☐ Kitchen Appliances

**Form Validation Indicator:**
- Show checkmarks next to completed sections
- Highlight incomplete required sections

**Sticky Bottom Bar:**
- Secondary button: "Save as Draft" (future feature)
- Primary button: "Publish Apartment" (enabled when all required fields valid)

#### User Actions
- Upload photos → Opens camera/gallery picker
- Tap uploaded photo → Show options (View, Delete, Set as Main)
- Drag photos → Reorder photo sequence
- Select dropdowns → Choose governorate/city
- Enter text fields → Real-time validation
- Adjust numbers → +/- buttons or direct input
- Select amenities → Toggle checkboxes
- Tap "Publish Apartment" →
  - Validate all fields
  - Show loading overlay
  - Upload photos
  - Create apartment listing
  - On success: Navigate back to My Apartments with success message
  - On error: Show error message

#### Empty States
- **Photos Empty**: Upload placeholder with + icon
- **No Amenities Selected**: Checkbox list with none checked

#### Loading States
**Photo Upload:**
- Progress indicator on each photo being uploaded
- Percentage: "Uploading... 45%"

**Form Submission:**
- Full-screen overlay with spinner
- Message: "Publishing your apartment..."

#### Error States
- **Required Field Missing**: Highlight field in red, show error below
  - "This field is required"
- **Photos Required**: "Please add at least one photo"
- **Photo Upload Failed**: "Failed to upload [photo name]. Please try again."
- **Invalid Price**: "Price must be greater than 0"
- **Invalid Size**: "Size must be greater than 0"
- **Description Too Short**: "Description must be at least 50 characters"
- **Submission Failed**: "Unable to publish apartment. Please try again."

#### Validation Rules
- **Photos**: At least 1, max 10, each max 5MB, JPG/PNG only
- **Governorate**: Required
- **City**: Required (dependent on governorate)
- **Address**: Required, min 10 chars, max 500 chars
- **Nightly Price**: Required, must be > 0
- **Monthly Price**: Required, must be > 0
- **Bedrooms**: Required, min 0, max 10
- **Bathrooms**: Required, min 1, max 10
- **Living Rooms**: Required, min 0, max 5
- **Size**: Required, must be > 0
- **Description**: Required, min 50 chars, max 2000 chars
- **Amenities**: Optional

#### Related Entities
- `apartments` table (create new apartment)
- `users` table (owner_id)

#### API Endpoints
- `POST /api/owner/apartments/upload-photo`
  - Request: multipart/form-data
  - Response: `{ "success": true, "file_path": "string" }`

- `POST /api/owner/apartments`
  - Request:
    ```json
    {
      "photos": ["path1", "path2"],
      "governorate": "Cairo",
      "city": "New Cairo",
      "address": "Full address text",
      "price": 500.00,
      "price_period": "night",
      "bedrooms": 2,
      "bathrooms": 1,
      "living_rooms": 1,
      "size": 80.00,
      "description": "Description text...",
      "amenities": ["WiFi", "Parking", "A/C"]
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "Apartment published successfully",
      "data": {
        "apartment_id": 1
      }
    }
    ```

#### Notes
- Photos should be compressed before upload
- New apartments automatically set to status = "active"
- Consider adding "Save as Draft" feature to continue editing later
- Validation should be comprehensive to ensure quality listings

---

### 5.1.3 Edit Apartment Screen

**Screen ID**: `OWNER-003`

#### Purpose
Allow owners to edit existing apartment listing information.

#### User Entry Points
- From My Apartments Screen → Tap Edit icon on apartment card

#### UI Components

**Identical to Add Apartment Screen (OWNER-002) except:**

**Top App Bar:**
- Title: "Edit Apartment"
- "Save" button in header (instead of bottom bar)

**Form:**
- All fields pre-filled with existing data
- Photos pre-loaded and displayed
- Same validation and structure as Add screen

**Additional Option:**
- **Status Toggle** (at top):
  - Switch: Active/Inactive
  - Active: Visible to tenants, bookable
  - Inactive: Hidden from tenants, not bookable

**Action Buttons:**
- Primary button: "Save Changes"
- Secondary button: "Cancel"

#### User Actions
- Modify any field → Changes tracked
- Toggle status → Enable/disable apartment visibility
- Add/remove photos → Update photo gallery
- Tap "Save Changes" →
  - Validate changes
  - Upload new photos (if any)
  - Update apartment
  - On success: Navigate back with success message
  - On error: Show error message
- Tap "Cancel" → Discard changes confirmation if form modified

#### Empty States
- N/A (form pre-filled with existing data)

#### Loading States
**Initial Load:**
- Skeleton loader while fetching apartment data

**Photo Upload:**
- Progress indicator for new photos

**Save Changes:**
- Spinner on "Save" button

#### Error States
- **Load Error**: "Unable to load apartment details"
- **Update Error**: "Unable to save changes. Please try again."
- **Photo Upload Error**: "Failed to upload photo"
- Same validation errors as Add screen

#### Validation Rules
- Same as Add Apartment Screen (OWNER-002)
- Cannot set to inactive if there are pending/approved bookings

#### Related Entities
- `apartments` table (update apartment)
- `bookings` table (check for active bookings before status change)

#### API Endpoints
- `GET /api/owner/apartments/{apartment_id}`
  - Response: Full apartment details

- `PUT /api/owner/apartments/{apartment_id}`
  - Request: Same as create apartment
  - Response:
    ```json
    {
      "success": true,
      "message": "Apartment updated successfully"
    }
    ```

#### Notes
- Changes are saved immediately (no draft mode)
- Consider showing change history in future
- Status change to inactive should warn about existing bookings

---

### 5.1.4 Apartment Detail (Owner View) Screen

**Screen ID**: `OWNER-004`

#### Purpose
Display comprehensive apartment statistics, booking history, and performance for the owner.

#### User Entry Points
- From My Apartments Screen → Tap apartment card or Stats icon

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Apartment Details"
- Right actions:
  - Edit icon → Navigate to Edit Screen
  - More menu (⋮):
    - "View as Tenant" (preview how tenants see it)
    - "Share Link"
    - "Delete"

**Scrollable Content:**

**1. Apartment Header:**
- Photo carousel (swipeable)
- Address (large)
- Status badge: Active/Inactive
- Quick edit button

**2. Performance Statistics Card:**
- **Total Bookings**: [Count]
- **Total Revenue**: EGP [Amount]
- **Average Rating**: ⭐ 4.5 (35 reviews)
- **Occupancy Rate**: 75% (last 30 days)
- Tap card → View detailed analytics (future)

**3. Apartment Information:**
- Price: EGP 500/night
- Specs: 2 🛏️ | 1 🛁 | 1 🛋️ | 80 m²
- Description (expandable)
- Amenities (grid)

**4. Booking Requests Section:**
- Section header: "Recent Booking Requests"
- List of recent requests (max 5):
  - Tenant name, photo
  - Dates
  - Status badge
  - Tap → View request detail
- "See All Requests" button → Filter bookings by apartment

**5. Reviews Section:**
- Section header: "Reviews (35)"
- Average rating with distribution
- Recent reviews (top 3)
- "See All Reviews" button

**6. Booking Calendar (Optional):**
- Monthly calendar view
- Shows booked dates in color
- Quick overview of availability

#### User Actions
- Swipe photos → View all photos
- Tap Edit icon → Navigate to Edit Screen
- Tap "View as Tenant" → Opens apartment detail in tenant view
- Tap "Share Link" → Share apartment link
- Tap "Delete" → Show delete confirmation
- Tap statistics card → View detailed analytics
- Tap booking request → Navigate to Booking Request Detail
- Tap "See All Requests" → Navigate to filtered bookings
- Tap "See All Reviews" → Navigate to Reviews List

#### Empty States
**No Bookings:**
- Message: "No bookings yet"
- Description: "Your apartment is active and visible to tenants"

**No Reviews:**
- Message: "No reviews yet"
- Description: "Reviews will appear after tenants complete their stays"

#### Loading States
**Initial Load:**
- Skeleton loaders for all sections

#### Error States
- **Load Error**: "Unable to load apartment details"
- **Delete Error**: "Cannot delete apartment with active bookings"

#### Related Entities
- `apartments` table (apartment details)
- `bookings` table (booking statistics)
- `ratings` table (reviews and ratings)

#### API Endpoints
- `GET /api/owner/apartments/{apartment_id}/details`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "apartment": { /* full apartment data */ },
        "statistics": {
          "total_bookings": 25,
          "total_revenue": 12500.00,
          "average_rating": 4.5,
          "occupancy_rate": 75
        },
        "recent_requests": [ /* booking requests */ ],
        "recent_reviews": [ /* reviews */ ],
        "booked_dates": ["2024-12-15", "2024-12-16"]
      }
    }
    ```

#### Notes
- Owner-specific view with business insights
- Consider adding earnings charts/graphs
- Calendar helps owners visualize availability
- "View as Tenant" helps owners preview their listing

---

### 5.1.5 Owner Bookings - Requests Management Screen

**Screen ID**: `OWNER-005`

#### Purpose
Display all booking requests for owner's apartments, organized by status for easy management.

#### User Entry Points
- From Bottom Navigation → Bookings tab
- From Notification → Tap booking request notification

#### UI Components

**Top App Bar:**
- Title: "Booking Requests"
- Right action: Notification bell icon

**Tab Bar (Horizontal):**
- Tab 1: "Pending" (requests requiring action)
  - Badge showing count of pending requests
- Tab 2: "Approved" (confirmed bookings)
- Tab 3: "History" (past, cancelled, rejected)

**Tab Content - Scrollable List:**

**Pending Tab:**
- Requests with status: pending, modified_pending
- Sorted by: Creation date (oldest first - urgent on top)
- Highlight new requests with "NEW" badge

**Approved Tab:**
- Bookings with status: approved
- Sorted by: Check-in date (nearest first)

**History Tab:**
- Bookings with status: completed, cancelled, rejected, modified_rejected
- Sorted by: Updated date (most recent first)

**Booking Request Card:**
- **Tenant Info:**
  - Tenant photo, name
  - "View Profile" link (optional)
- **Apartment Info:**
  - Apartment name/address (if owner has multiple)
  - Small photo thumbnail
- **Booking Details:**
  - Check-in: [Date]
  - Check-out: [Date]
  - Guests: [Count]
  - Payment Method: [Method]
  - Total Price: EGP [Amount]
- **Status Badge** (varies by tab)
- **Action Buttons** (vary by status):
  - **Pending**: "Approve" (green) | "Reject" (red)
  - **Modified Pending**: "Approve Changes" | "Reject Changes"
  - **Approved**: "View Details" | "Contact Tenant"
  - **History**: "View Details"
- **Request Date**: "Requested 2 hours ago"

**Pull-to-Refresh:**
- Reload booking requests

#### User Actions
- Switch tabs → Load requests for selected tab
- Pull down → Refresh requests list
- Tap request card → Navigate to Booking Request Detail (OWNER-006)
- Tap "Approve" → Show approval confirmation
- Tap "Reject" → Show rejection confirmation with optional reason
- Tap "Contact Tenant" → Navigate to Chat Screen
- Tap notification bell → Navigate to Notifications Screen

#### Empty States

**Pending Tab - No Requests:**
- Icon: 📋 (clipboard icon)
- Message: "No pending requests"
- Description: "New booking requests will appear here"

**Approved Tab - No Bookings:**
- Icon: ✓ (checkmark icon)
- Message: "No approved bookings"
- Description: "Approved bookings will appear here"

**History Tab - No History:**
- Icon: 📅 (calendar icon)
- Message: "No booking history"
- Description: "Past bookings will appear here"

#### Loading States
**Initial Load:**
- Skeleton loaders for 3-5 request cards per tab

**Tab Switch:**
- Brief spinner while loading tab data

**Pull-to-Refresh:**
- Standard refresh indicator

**Action Processing:**
- Spinner on action button while processing approve/reject

#### Error States
- **Load Error**: "Unable to load booking requests. Please try again."
- **Action Error**: Toast "Unable to process request. Please try again."

#### Quick Approval/Rejection Dialogs

**Approve Confirmation:**
- Title: "Approve Booking?"
- Details: Show booking summary
- Message: "The tenant will be notified immediately"
- Actions: "Cancel" | "Confirm Approval"

**Reject Confirmation:**
- Title: "Reject Booking?"
- Reason field (optional): "Reason for rejection (optional)"
- Message: "The tenant will be notified"
- Actions: "Cancel" | "Confirm Rejection"

#### Related Entities
- `bookings` table (fetch bookings for owner's apartments)
- `apartments` table (apartment details)
- `users` table (tenant details)

#### API Endpoints
- `GET /api/owner/bookings`
  - Query params: 
    - `status` (string: pending, approved, history)
    - `page` (number)
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "pending_count": 5,
        "bookings": [
          {
            "id": 123,
            "tenant": {
              "id": 10,
              "first_name": "John",
              "last_name": "Doe",
              "personal_photo": "url"
            },
            "apartment": {
              "id": 1,
              "address": "Address",
              "photos": ["url"]
            },
            "check_in_date": "2024-12-15",
            "check_out_date": "2024-12-18",
            "number_of_guests": 2,
            "payment_method": "Cash",
            "status": "pending",
            "total_price": 1500.00,
            "created_at": "2024-12-10T10:00:00Z"
          }
        ]
      }
    }
    ```

#### Notes
- Pending requests should be prioritized (notification badge)
- Quick actions allow approving/rejecting without opening detail
- Consider adding filters (by apartment, by date range)
- Push notification sent when new request received

---

### 5.1.6 Booking Request Detail (Owner View) Screen

**Screen ID**: `OWNER-006`

#### Purpose
Display comprehensive details of a booking request with approve/reject actions.

#### User Entry Points
- From Booking Requests Screen → Tap request card
- From Notification → Tap booking notification

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Booking Request"

**Scrollable Content:**

**Status Banner (if applicable):**
- Pending: ⏳ "Waiting for Your Approval"
- Approved: ✅ "Booking Confirmed"
- Rejected: ✕ "Booking Rejected"

**Tenant Information Card:**
- Tenant photo, name
- Member since: [Date]
- "View Full Profile" button (optional)
- "Contact Tenant" button → Opens chat

**Booking Details Card:**
- **Booking ID**: #123456
- **Check-in**: Thursday, Dec 15, 2024 (2:00 PM)
- **Check-out**: Sunday, Dec 18, 2024 (12:00 PM)
- **Duration**: 3 nights
- **Number of Guests**: 2 people
- **Payment Method**: Cash
- **Total Amount**: EGP 1,500
- **Requested on**: Dec 10, 2024

**Apartment Information Card:**
- Apartment photo, address
- Price breakdown: "EGP 500 × 3 nights"
- "View Apartment Details" button

**Modification History (if modified_pending):**
- Shows original booking details
- Shows requested changes (highlighted)
- Comparison view

**Action Buttons (Status-dependent):**

**If Status = Pending:**
- Primary button: "Approve Booking" (green)
- Secondary button: "Reject Booking" (red)

**If Status = Modified Pending:**
- Primary button: "Approve Changes"
- Secondary button: "Reject Changes"
- Info: Shows original vs new details

**If Status = Approved:**
- "Contact Tenant" button
- "View Calendar" button

**If Status = Completed/Cancelled/Rejected:**
- View only (no actions)

#### User Actions
- Tap "View Full Profile" → View tenant profile
- Tap "Contact Tenant" → Navigate to Chat Screen
- Tap "View Apartment Details" → Navigate to Apartment Detail (Owner View)
- Tap "Approve Booking" →
  - Show confirmation dialog
  - On confirm: Approve booking, send notification
  - Navigate back with success message
- Tap "Reject Booking" →
  - Show rejection dialog with reason field
  - On confirm: Reject booking, send notification
  - Navigate back with success message

#### Empty States
- N/A (always displays booking data)

#### Loading States
**Initial Load:**
- Skeleton loader for entire screen

**Action Processing:**
- Full-screen overlay: "Processing..." while approving/rejecting

#### Error States
- **Load Error**: "Unable to load booking details"
- **Approve Error**: "Unable to approve booking. Please try again."
- **Reject Error**: "Unable to reject booking. Please try again."

#### Approval Confirmation Dialog
- Title: "Approve This Booking?"
- Summary: Tenant name, dates, amount
- Message: "The tenant will be notified and can proceed with their stay"
- Actions: "Cancel" | "Confirm Approval"

#### Rejection Dialog
- Title: "Reject This Booking?"
- Reason field: "Reason for rejection (optional but recommended)"
  - Multi-line text input
  - Max 500 characters
- Message: "The tenant will be notified"
- Actions: "Cancel" | "Confirm Rejection"

#### Related Entities
- `bookings` table (booking details, update status)
- `apartments` table (apartment details)
- `users` table (tenant details)
- `notifications` table (send notification to tenant)

#### API Endpoints
- `GET /api/owner/bookings/{booking_id}`
  - Response: Full booking details

- `PUT /api/owner/bookings/{booking_id}/approve`
  - Response:
    ```json
    {
      "success": true,
      "message": "Booking approved successfully"
    }
    ```

- `PUT /api/owner/bookings/{booking_id}/reject`
  - Request:
    ```json
    {
      "rejection_reason": "Apartment not available due to maintenance"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "Booking rejected"
    }
    ```

#### Notes
- Tenant receives push notification immediately upon approval/rejection
- Approved bookings block dates for other tenants
- Rejection reason helps improve communication
- Consider adding calendar integration for approved bookings

---

## 6. Shared Screens

### 6.1 Overview

Shared screens are accessible by both tenants and owners through the Profile tab in bottom navigation.

Shared screens are accessible to **both Tenants and Owners** with identical or slightly adapted functionality. These screens provide:
- In-app messaging between users
- Notification management
- User profile viewing and editing
- App settings (language, theme)

**Shared Navigation:**
- Both Tenants and Owners have **Messages** and **Profile** tabs in their bottom navigation
- Notification bell icon appears in top app bar across most screens

---

### 6.1.1 Messages - Conversation List Screen

**Screen ID**: `SHARED-001`

#### Purpose
Display all message conversations between the user and other users (tenants and owners).

#### User Entry Points
- From Bottom Navigation → Messages tab (both Tenant and Owner)
- From Apartment Detail → Tap "Contact Owner" (creates/opens conversation)
- From Booking Detail → Tap "Contact Owner/Tenant" (creates/opens conversation)
- From Notification → Tap new message notification

#### UI Components

**Top App Bar:**
- Title: "Messages"
- Right action: Notification bell icon

**Main Content:**

**Conversation List:**
- Vertical scrolling list of conversations
- Sorted by: Most recent message first
- Each conversation card shows:
  - **User Photo**: Circular avatar of other user
  - **User Name**: First and last name
  - **User Role Badge**: "Owner" or "Tenant" (small badge)
  - **Last Message**: Preview text (1 line, truncated)
  - **Timestamp**: "2 hours ago" or "Dec 10" for older
  - **Unread Badge**: Red dot or count badge (e.g., "3") if unread messages
  - **Avatar Indicator**: Green dot if user is online (optional future feature)
- Swipe actions (optional):
  - Swipe left → "Delete" conversation
  - Swipe right → "Mark as Read/Unread"
- Pull-to-refresh

#### User Actions
- Scroll to view all conversations
- Pull down → Refresh conversation list
- Tap conversation → Navigate to Chat Screen (SHARED-002)
- Swipe conversation → Quick actions (delete, mark read)
- Long press conversation → Show context menu (delete, mark read, block user)
- Tap notification bell → Navigate to Notifications Screen

#### Empty States
**No Messages:**
- Icon: 💬 (chat bubble icon)
- Message: "No messages yet"
- Description: "Start a conversation with apartment owners or tenants"
- **For Tenants**: "Find an apartment and contact the owner"
- **For Owners**: "Messages from tenants will appear here"

#### Loading States
**Initial Load:**
- Skeleton loaders for 5-7 conversation cards
- Shimmer effect

**Pull-to-Refresh:**
- Standard refresh indicator

#### Error States
- **Load Error**: "Unable to load messages. Please try again."
  - Retry button
- **Delete Error**: Toast "Unable to delete conversation"

#### Delete Confirmation Dialog
**When deleting conversation:**
- Title: "Delete Conversation?"
- Message: "This will delete all messages with [User Name]"
- Warning: "This action cannot be undone"
- Actions: "Cancel" | "Delete" (destructive/red)

#### Related Entities
- `messages` table (fetch conversations)
- `users` table (fetch user information)

#### API Endpoints
- `GET /api/messages`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "conversations": [
          {
            "user": {
              "id": 5,
              "first_name": "John",
              "last_name": "Doe",
              "personal_photo": "url",
              "role": "owner"
            },
            "last_message": {
              "id": 123,
              "message_text": "Sure, when would you like to visit?",
              "sender_id": 5,
              "is_read": false,
              "created_at": "2024-12-10T14:30:00Z"
            },
            "unread_count": 2,
            "last_message_at": "2024-12-10T14:30:00Z"
          }
        ]
      }
    }
    ```

#### Notes
- Conversations auto-created when users first message each other
- Unread count badge prominently displayed
- Real-time updates via WebSocket or polling (implementation detail)
- Consider adding search conversations feature in future

---

### 6.1.2 Chat Screen

**Screen ID**: `SHARED-002`

#### Purpose
Display message history and enable real-time messaging between two users (WhatsApp-style interface).

#### User Entry Points
- From Conversation List → Tap conversation
- From Apartment Detail → Tap "Contact Owner"
- From Booking Detail → Tap "Contact Owner/Tenant"

#### UI Components

**Top App Bar:**
- Back button (left)
- **User Info** (center):
  - User photo (small, circular)
  - User name
  - User role badge: "Owner" or "Tenant"
  - Online status: "Online" / "Last seen..." (optional)
- Right actions:
  - More menu (⋮):
    - "View Profile"
    - "Block User"
    - "Report User"
    - "Delete Conversation"

**Messages Area (Scrollable):**
- Chat bubble layout
- **Sent Messages** (right-aligned):
  - Background: Primary color (blue/green)
  - Text: White
  - Alignment: Right
  - Tail pointing right
- **Received Messages** (left-aligned):
  - Background: Light gray
  - Text: Dark
  - Alignment: Left
  - Tail pointing left
- **Each Message Shows**:
  - Message text
  - Attachment image/file (if any)
  - Timestamp: "14:30" (below message)
  - Read status (for sent messages):
    - ✓ Sent
    - ✓✓ Delivered
    - ✓✓ Read (blue/colored)
- **Date Separators**: "Today", "Yesterday", "Dec 10, 2024"
- **System Messages** (centered):
  - "Booking request sent"
  - "Booking approved"
- Auto-scroll to bottom on new message
- Load more history on scroll up

**Message Input Bar (Bottom, Sticky):**
- **Attachment Button** (left):
  - Camera icon
  - Opens action sheet: "Camera" | "Photo Library"
- **Text Input Field** (center):
  - Placeholder: "Type a message..."
  - Multi-line (expands up to 4 lines)
  - Auto-focus on screen open
- **Send Button** (right):
  - Paper plane icon
  - Primary color
  - Disabled when text empty
  - Animated on tap

#### User Actions
- Scroll up → Load older messages (pagination)
- Tap attachment button → Open action sheet
  - Select "Camera" → Opens camera
  - Select "Photo Library" → Opens gallery
  - After selection → Preview and confirm
- Type message → Enables send button
- Tap send button →
  - Send message
  - Clear input field
  - Scroll to bottom
  - Show sending indicator
- Long press message →
  - Show context menu: "Copy" | "Delete" (own messages only)
- Tap image attachment → Open full-screen image viewer
- Tap "View Profile" → Navigate to user profile (future)
- Tap "Delete Conversation" → Show confirmation, delete all messages

#### Empty States
**No Messages Yet:**
- Icon: 💬 (chat icon)
- Message: "No messages yet"
- Description: "Start the conversation"
- Center of screen, above input bar

#### Loading States
**Initial Load:**
- Skeleton loaders for message bubbles

**Loading Older Messages:**
- Spinner at top while scrolling up

**Sending Message:**
- Message appears immediately with "sending" indicator
- Changes to "sent" when confirmed

**Uploading Attachment:**
- Progress bar on image while uploading
- Percentage: "Uploading... 45%"

#### Error States
- **Load Error**: "Unable to load messages. Tap to retry."
- **Send Error**: Red indicator on message, "Tap to retry"
- **Attachment Error**: Toast "Failed to upload attachment"
- **Network Error**: Banner at top "No internet connection. Messages will send when connected."

#### Validation Rules
- Message text max 2000 characters
- Attachment max 5MB
- Supported formats: JPG, PNG for images
- Cannot send empty messages (only whitespace)

#### Related Entities
- `messages` table (fetch and create messages)
- `users` table (fetch recipient information)

#### API Endpoints
- `GET /api/messages/{user_id}`
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "conversation_with": {
          "id": 5,
          "first_name": "John",
          "last_name": "Doe",
          "personal_photo": "url",
          "role": "owner"
        },
        "messages": [
          {
            "id": 123,
            "sender": {
              "id": 10,
              "first_name": "Jane",
              "last_name": "Smith",
              "personal_photo": "url"
            },
            "recipient": {
              "id": 5,
              "first_name": "John",
              "last_name": "Doe",
              "personal_photo": "url"
            },
            "message_text": "Hello, is the apartment available?",
            "attachment_path": null,
            "is_read": true,
            "created_at": "2024-12-10T14:30:00Z"
          }
        ]
      }
    }
    ```
  - Note: Automatically marks all messages from the conversation partner as read

- `POST /api/messages/ws` (WebSocket - Send Message)
  - Request:
    ```json
    {
      "type": "send_message",
      "recipient_id": 5,
      "message_text": "Sure, when would you like to visit?"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "Message sent successfully",
      "type": "send_message",
      "data": {
        "message": {
          "id": 124,
          "sender_id": 10,
          "recipient_id": 5,
          "message_text": "Sure, when would you like to visit?",
          "attachment_path": null,
          "is_read": false,
          "created_at": "2024-12-10T14:35:00Z"
        }
      }
    }
    ```

- `POST /api/messages/ws` (WebSocket - Mark as Read)
  - Request:
    ```json
    {
      "type": "mark_read",
      "message_id": 123
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "Message marked as read",
      "type": "mark_read",
      "data": {
        "message_id": 123,
        "is_read": true
      }
    }
    ```

- `POST /api/messages/upload-attachment` (HTTP - File Upload)
  - Request: multipart/form-data
  - Response: `{ "success": true, "data": { "file_path": "string", "file_url": "string" } }`

#### Notes
- Messages automatically marked as read when screen is open
- Real-time messaging via WebSocket or polling
- Typing indicator can be added in future ("John is typing...")
- Consider adding voice messages in future
- Encryption for messages recommended for privacy

---

### 6.1.3 Notifications Screen

**Screen ID**: `SHARED-003`

#### Purpose
Display all system notifications for the user, organized by date.

#### User Entry Points
- From any screen → Tap notification bell icon in top app bar
- From push notification → Opens app to this screen

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Notifications"
- Right action: "Mark All as Read" link

**Main Content:**

**Notifications List:**
- Vertical scrolling list grouped by date
- Date separators: "Today", "Yesterday", "This Week", "Earlier"
- Each notification card shows:
  - **Icon** (left): Based on notification type
    - 📅 Booking-related (new request, approval, rejection)
    - ✏️ Modification-related
    - ⭐ Review-related
    - 💬 Message-related
    - ✅ Account approval
  - **Title**: Bold notification headline
  - **Message**: Description text (2 lines max)
  - **Timestamp**: "2 hours ago"
  - **Read/Unread Indicator**: 
    - Unread: Blue dot (left) + bold text
    - Read: No dot + regular text
  - **Thumbnail**: Related image (apartment photo, user photo)
- Swipe actions:
  - Swipe left → "Delete"
  - Swipe right → "Mark as Read/Unread"
- Pull-to-refresh

**Notification Types:**

**For Tenants:**
- Booking approved
- Booking rejected
- Modification approved
- Modification rejected
- New message from owner
- Account approved by admin

**For Owners:**
- New booking request
- Booking cancelled by tenant
- Booking modified by tenant
- New message from tenant
- New review received
- Account approved by admin

#### User Actions
- Scroll to view all notifications
- Pull down → Refresh notifications
- Tap notification → 
  - Mark as read
  - Navigate to related screen:
    - Booking notification → Booking Detail
    - Message notification → Chat Screen
    - Review notification → Reviews List
    - Account notification → No action (just display)
- Swipe notification → Quick actions
- Tap "Mark All as Read" → Mark all notifications as read
- Long press notification → Context menu (Delete, Mark as Read)

#### Empty States
**No Notifications:**
- Icon: 🔔 (bell icon)
- Message: "No notifications yet"
- Description: "You'll be notified about bookings, messages, and updates"

#### Loading States
**Initial Load:**
- Skeleton loaders for 5-7 notification cards
- Shimmer effect

**Pull-to-Refresh:**
- Standard refresh indicator

#### Error States
- **Load Error**: "Unable to load notifications. Please try again."
  - Retry button
- **Delete Error**: Toast "Unable to delete notification"

#### Delete Confirmation Dialog
**When deleting notification:**
- Title: "Delete Notification?"
- Message: "This will permanently delete this notification"
- Actions: "Cancel" | "Delete"

#### Related Entities
- `notifications` table (fetch notifications)
- `bookings` table (link to related bookings)

#### API Endpoints
- `GET /api/notifications`
  - Query params: `page`, `per_page`, `unread_only` (boolean)
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "unread_count": 5,
        "notifications": [
          {
            "id": 45,
            "type": "booking_approved",
            "title": "Booking Approved!",
            "message": "Your booking for New Cairo Apartment has been approved",
            "booking_id": 123,
            "is_read": false,
            "created_at": "2024-12-10T14:30:00Z"
          }
        ]
      }
    }
    ```

- `PUT /api/notifications/{notification_id}/read`
  - Response: `{ "success": true }`

- `PUT /api/notifications/mark-all-read`
  - Response: `{ "success": true, "message": "All notifications marked as read" }`

- `DELETE /api/notifications/{notification_id}`
  - Response: `{ "success": true }`

#### Notes
- Unread count shown as badge on bell icon
- Notifications retained for 30 days (configurable)
- Push notifications trigger these in-app notifications
- Consider adding notification preferences in settings

---

### 6.1.4 Profile Screen

**Screen ID**: `SHARED-004`

#### Purpose
Display user profile information and provide access to settings and app features.

#### User Entry Points
- From Bottom Navigation → Profile tab (both Tenant and Owner)

#### UI Components

**Scrollable Content:**

**1. Profile Header:**
- Background gradient or image
- **User Photo** (large, circular, center)
- **User Name** (large, below photo)
- **Role Badge**: "Tenant" or "Owner"
- **Member Since**: "Member since Dec 2024"
- **Edit Profile** button (pencil icon, top-right of header)

**2. Account Balance Section:**
- Section header: "Account Balance"
- **Current Balance**: "EGP 2,500.00" (large, prominent display)
- **Balance Status**: 
  - Positive: Green display with currency symbol
  - Zero: Gray display
  - Negative: Red display (if allowed, unlikely)
- **View Transaction History** button → Navigate to Transaction History Screen (SHARED-008)
- Info text: "Contact admin to add money to your balance"

**3. Account Information Section:**
- Section header: "Account"
- **Mobile Number**: +20 XXX XXX XXXX
- **Email**: email@example.com (if collected)
- **Date of Birth**: Dec 15, 1990
- Non-editable (display only)
- Info: "Contact support to change account information"

**4. App Settings Section:**
- Section header: "Settings"
- **Language** →
  - Current: "English" or "العربية"
  - Tap → Navigate to Language Settings
- **Theme** →
  - Current: "Light Mode" or "Dark Mode"
  - Tap → Navigate to Theme Settings
- **Notifications** →
  - Tap → Navigate to Notification Settings (future)

**5. Statistics Section (Role-specific):**

**For Owners:**
- Section header: "My Statistics"
- Total Apartments: [Count]
- Total Bookings: [Count]
- Average Rating: ⭐ [Rating]
- Tap → View detailed statistics (future)

**For Tenants:**
- Section header: "My Activity"
- Total Bookings: [Count]
- Reviews Given: [Count]
- Member Since: [Date]

**6. Support & Legal Section:**
- Section header: "Support"
- **Help Center** → Opens help/FAQ
- **Contact Support** → Opens support chat/email
- **Terms & Conditions** → Opens terms page
- **Privacy Policy** → Opens privacy page
- **About** → App version, credits

**7. Logout Section:**
- **Logout** button (red text, centered)

#### User Actions
- Tap "Edit Profile" → Navigate to Edit Profile Screen (SHARED-005)
- Tap "View Transaction History" → Navigate to Transaction History Screen (SHARED-008)
- Tap Language → Navigate to Language Settings (SHARED-006)
- Tap Theme → Navigate to Theme Settings (SHARED-007)
- Tap Notifications → Navigate to Notification Settings
- Tap statistics → View detailed analytics
- Tap Help/Support → Open respective pages
- Tap Logout → Show logout confirmation

#### Empty States
- N/A (profile always has data)

#### Loading States
**Initial Load:**
- Skeleton loader for profile header and sections

#### Error States
- **Load Error**: "Unable to load profile. Please try again."

#### Logout Confirmation Dialog
- Title: "Logout"
- Message: "Are you sure you want to logout?"
- Actions: "Cancel" | "Logout"

#### Related Entities
- `users` table (fetch user information including balance)
- `apartments` table (count owner's apartments)
- `bookings` table (count bookings)
- `ratings` table (count reviews, calculate average)

#### API Endpoints
- `GET /api/profile`
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
          "role": "tenant",
          "language_preference": "en",
          "created_at": "2024-12-01T10:00:00Z"
        },
        "statistics": {
          "total_apartments": 5,
          "total_bookings": 12,
          "average_rating": 4.5,
          "reviews_given": 8
        },
        "balance": 2500.00
      }
    }
    ```

- `POST /api/auth/logout`
  - Response: `{ "success": true, "message": "Logged out successfully" }`

#### Notes
- Theme preference applies immediately when changed
- Language preference applies after confirmation
- Logout clears JWT token and returns to login screen
- Consider adding profile completion percentage
- Balance updates in real-time when transactions occur

---

### 6.1.5 Transaction History Screen

**Screen ID**: `SHARED-008`

#### Purpose
Display user's complete transaction history including deposits, withdrawals, rent payments, refunds, and cancellation fees.

#### User Entry Points
- From Profile Screen → Tap "View Transaction History"
- From Booking Detail Screen → Tap "View Transaction Details"

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Transaction History"
- Filter icon (right) → Opens filter dialog

**Current Balance Banner:**
- **Current Balance**: "EGP 2,500.00" (large, prominent)
- Update timestamp: "Updated: Dec 15, 2024, 10:30 AM"

**Filter Bar (Optional, below banner):**
- **Filter by Type**: All | Deposits | Withdrawals | Rent Payments | Refunds | Cancellation Fees
- **Date Range**: This Month | Last Month | Last 3 Months | All Time
- **Sort**: Newest First | Oldest First

**Transaction List:**

**Transaction Item (for each transaction):**

**For Deposit:**
- Icon: 💰 (green)
- Type: "Deposit"
- Amount: "+EGP 500.00" (green, positive)
- Description: "Cash deposit from admin" or admin's notes
- Date: "Dec 15, 2024, 10:30 AM"
- Related: None (or "Admin Deposit")

**For Withdrawal:**
- Icon: 💸 (orange)
- Type: "Withdrawal"
- Amount: "-EGP 200.00" (orange, negative)
- Description: "Cash withdrawal by admin" or admin's notes
- Date: "Dec 14, 2024, 2:15 PM"
- Related: None (or "Admin Withdrawal")

**For Rent Payment:**
- Icon: 🏠 (blue)
- Type: "Rent Payment"
- Amount: "-EGP 1,500.00" (red, negative)
- Description: "Rent payment for booking #12345"
- Apartment: "Apartment Name, Address" (tappable → navigate to apartment)
- Booking: "Booking #12345" (tappable → navigate to booking detail)
- Date: "Dec 10, 2024, 9:00 AM"

**For Refund:**
- Icon: 🔄 (green)
- Type: "Refund"
- Amount: "+EGP 1,500.00" (green, positive)
- Description: "Full refund - Booking #12345 rejected by owner"
- Or: "Partial refund - Booking #12345 cancelled (80%)"
- Booking: "Booking #12345" (tappable → navigate to booking detail)
- Date: "Dec 12, 2024, 3:20 PM"

**For Cancellation Fee:**
- Icon: ⚠️ (orange)
- Type: "Cancellation Fee"
- Amount: "EGP 300.00" (gray, informational only - no balance change)
- Description: "Cancellation fee - Owner keeps 20% for booking #12345"
- Booking: "Booking #12345" (tappable → navigate to booking detail)
- Date: "Dec 12, 2024, 3:20 PM"
- Note: "This fee was deducted from your refund"

**Transaction Grouping (Optional):**
- Group by date: "Today", "Yesterday", "This Week", "This Month", "[Date]"

**Empty State:**
- Icon: 📊
- Message: "No transactions yet"
- Description: "Your transaction history will appear here"

**Load More:**
- Pagination or "Load More" button at bottom

#### User Actions
- Tap transaction item → If related to booking, navigate to Booking Detail Screen
- Tap apartment link → Navigate to Apartment Detail Screen
- Tap booking link → Navigate to Booking Detail Screen
- Tap filter icon → Open filter dialog
- Select filter → Apply filter and reload transactions
- Scroll to bottom → Load more transactions (pagination)
- Pull to refresh → Reload transaction list

#### Empty States
**No Transactions:**
- Icon: 📊
- Message: "No transactions yet"
- Description: "Your transaction history will appear here once you make bookings or admin adds money to your balance"

**No Filter Results:**
- Icon: 🔍
- Message: "No transactions match your filter"
- Action: "Clear Filters" button

#### Loading States
**Initial Load:**
- Skeleton loaders for transaction items (5-10 items)

**Loading More:**
- Loading indicator at bottom of list

#### Error States
- **Load Error**: "Unable to load transactions. Please try again."
  - Retry button

#### Filter Dialog

**When tapping filter icon:**
- Modal bottom sheet or dialog
- **Transaction Type** (multi-select checkboxes):
  - ☐ All Types
  - ☐ Deposits
  - ☐ Withdrawals
  - ☐ Rent Payments
  - ☐ Refunds
  - ☐ Cancellation Fees
- **Date Range** (radio buttons):
  - ○ All Time
  - ○ This Month
  - ○ Last Month
  - ○ Last 3 Months
  - ○ Custom Range (date pickers)
- **Sort Order** (radio buttons):
  - ○ Newest First
  - ○ Oldest First
- Actions:
  - "Clear Filters" button
  - "Apply Filters" button (primary)

#### Related Entities
- `transactions` table (fetch user's transactions)
- `users` table (fetch current balance)
- `bookings` table (if transaction related to booking)
- `apartments` table (if transaction related to booking)

#### API Endpoints
- `GET /api/user/transactions`
  - Query params:
    - `page` (number, default 1)
    - `per_page` (number, default 20)
    - `type` (string, optional: deposit, withdrawal, rent_payment, refund, cancellation_fee)
    - `date_from` (date, optional)
    - `date_to` (date, optional)
    - `sort` (string: newest, oldest)
  - Response:
    ```json
    {
      "success": true,
      "data": {
        "balance": 2500.00,
        "transactions": [
          {
            "id": 1,
            "type": "rent_payment",
            "amount": 1500.00,
            "description": "Rent payment for booking #12345",
            "related_booking_id": 12345,
            "related_user_id": 5,
            "created_at": "2024-12-10T09:00:00Z",
            "booking": {
              "id": 12345,
              "apartment": {
                "id": 10,
                "name": "Cozy Downtown Apartment",
                "address": "123 Main St, Cairo"
              }
            }
          },
          {
            "id": 2,
            "type": "deposit",
            "amount": 500.00,
            "description": "Cash deposit from admin",
            "created_at": "2024-12-15T10:30:00Z"
          }
        ],
        "pagination": {
          "current_page": 1,
          "total_pages": 5,
          "has_more": true
        }
      }
    }
    ```

#### Notes
- Transactions are displayed in chronological order (newest first by default)
- Amount display: Green for increases (+), Red/Orange for decreases (-)
- Transaction types have distinct icons for quick visual identification
- Transactions related to bookings are tappable to view booking details
- Balance displayed at top updates when transactions are loaded
- Consider adding export to PDF/CSV feature in future

---

### 6.1.6 Edit Profile Screen

**Screen ID**: `SHARED-005`

#### Purpose
Allow users to update their profile photo and name.

#### User Entry Points
- From Profile Screen → Tap "Edit Profile" button

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Edit Profile"
- "Save" button (right) - enabled when changes made

**Scrollable Form:**

**1. Profile Photo Section:**
- Current photo displayed (large, circular, center)
- "Change Photo" button below photo
- Tap → Action sheet: "Take Photo" | "Choose from Gallery" | "Remove Photo"

**2. Personal Information:**
- **First Name** (editable)
  - Text input
  - Current value pre-filled
  - Max 100 characters
- **Last Name** (editable)
  - Text input
  - Current value pre-filled
  - Max 100 characters

**3. Read-Only Information:**
- **Mobile Number**: +20 XXX XXX XXXX (gray, non-editable)
- **Date of Birth**: Dec 15, 1990 (gray, non-editable)
- **Role**: Tenant/Owner (gray, non-editable)
- Info box: "Contact support to change mobile number or role"

**Action Buttons:**
- Primary button: "Save Changes" (bottom, sticky)
- Enabled only when changes detected

#### User Actions
- Tap "Change Photo" → Select new photo
  - Opens camera or gallery
  - Preview selected photo
  - Confirm or cancel
- Edit name fields → Real-time validation
- Tap "Save" →
  - Show loading state
  - Upload new photo (if changed)
  - Update profile
  - On success: Navigate back with success toast
  - On error: Show error message
- Tap back button → 
  - If changes made: Show discard confirmation
  - If no changes: Navigate back

#### Empty States
- N/A (form pre-filled with current data)

#### Loading States
**Initial Load:**
- Skeleton loader while fetching current data

**Photo Upload:**
- Progress indicator on photo: "Uploading... 45%"

**Save Changes:**
- Spinner on "Save" button

#### Error States
- **Load Error**: "Unable to load profile data"
- **Invalid Name**: "Name must contain only letters and spaces"
- **Name Too Short**: "Name must be at least 2 characters"
- **Photo Upload Error**: "Failed to upload photo. Please try again."
- **Update Error**: "Unable to save changes. Please try again."

#### Discard Changes Dialog
**When navigating back with unsaved changes:**
- Title: "Discard Changes?"
- Message: "You have unsaved changes. Do you want to discard them?"
- Actions: "Cancel" | "Discard"

#### Validation Rules
- First Name: Required, 2-100 characters, letters and spaces only
- Last Name: Required, 2-100 characters, letters and spaces only
- Photo: Optional, max 5MB, JPG/PNG only

#### Related Entities
- `users` table (update user information)

#### API Endpoints
- `POST /api/profile/upload-photo`
  - Request: multipart/form-data
  - Response: `{ "success": true, "file_path": "string" }`

- `PUT /api/profile`
  - Request:
    ```json
    {
      "first_name": "John",
      "last_name": "Doe",
      "personal_photo": "new_path (if changed)"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "Profile updated successfully"
    }
    ```

#### Notes
- Only editable fields: First name, Last name, Photo
- Mobile number and role cannot be changed (contact support)
- Date of birth cannot be changed (verification purposes)
- Photo compression recommended before upload

---

### 6.1.6 Language Settings Screen

**Screen ID**: `SHARED-006`

#### Purpose
Allow users to change app language between Arabic and English.

#### User Entry Points
- From Profile Screen → Tap "Language"

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Language" (or "اللغة" if currently Arabic)

**Main Content:**
- Section description: "Select your preferred language"
- Radio button list:
  - ○ **English**
    - Description: "English (United States)"
  - ○ **العربية**
    - Description: "Arabic (العربية)"
- Current selection pre-selected

**Sticky Bottom Bar:**
- Primary button: "Apply" (enabled when selection changes)

#### User Actions
- Tap language option → Select radio button
- Tap "Apply" →
  - Show loading overlay
  - Update language preference
  - Restart app or reload UI with new language
  - Update text direction (LTR for English, RTL for Arabic)
  - Show success message in new language

#### Empty States
- N/A (options always visible)

#### Loading States
**Applying Language:**
- Full-screen overlay: "Applying language..."
- Brief transition animation

#### Error States
- **Update Error**: "Unable to change language. Please try again."

#### Related Entities
- `users` table (update language_preference)

#### API Endpoints
- `PUT /api/profile/language`
  - Request:
    ```json
    {
      "language_preference": "ar"
    }
    ```
  - Response:
    ```json
    {
      "success": true,
      "message": "Language updated successfully"
    }
    ```

#### Notes
- Language change applies app-wide immediately
- Text direction switches automatically (RTL/LTR)
- All strings must be localized
- Notifications sent in user's preferred language
- Consider adding more languages in future

---

### 6.1.8 Theme Settings Screen

**Screen ID**: `SHARED-007`

#### Purpose
Allow users to switch between light and dark theme modes.

#### User Entry Points
- From Profile Screen → Tap "Theme"

#### UI Components

**Top App Bar:**
- Back button (left)
- Title: "Theme"

**Main Content:**
- Section description: "Choose your preferred theme"
- Radio button list with preview:
  - ○ **Light Mode**
    - Preview: Light color swatch
    - Description: "Bright and clean"
  - ○ **Dark Mode**
    - Preview: Dark color swatch
    - Description: "Easy on the eyes"
  - ○ **System Default** (optional)
    - Description: "Follow device settings"
- Current selection pre-selected

**Live Preview:**
- Shows sample UI elements in selected theme

**Sticky Bottom Bar:**
- Primary button: "Apply" (enabled when selection changes)

#### User Actions
- Tap theme option → 
  - Select radio button
  - Preview updates immediately
- Tap "Apply" →
  - Save theme preference locally
  - Apply theme immediately app-wide
  - Navigate back
  - Show brief success toast

#### Empty States
- N/A (options always visible)

#### Loading States
**Applying Theme:**
- Smooth animation transition between themes
- No explicit loading state (instant)

#### Error States
- **Save Error**: Toast "Unable to save theme preference"
  - Retry automatically or on next app launch

#### Related Entities
- Local storage only (no backend sync)
- Preference stored in device secure storage

#### API Endpoints
- None (stored locally only)

#### Notes
- Theme preference persists across sessions
- Theme applies immediately without app restart
- All screens must support both themes
- Consider system default option to follow device settings
- Dark mode improves battery life on OLED screens

---

## 7. Common UI Patterns

### 7.1 Loading States

**Skeleton Loaders:**
- Used for initial screen loads
- Shimmer animation effect
- Mimic actual content layout
- Replace with real content when loaded

**Progress Indicators:**
- **Circular Spinner**: For indefinite operations (API calls)
- **Linear Progress**: For determinate operations (file uploads)
- **Pull-to-Refresh**: For manual content refresh
- **Pagination Loading**: Spinner at list bottom for infinite scroll

**Button Loading:**
- Spinner replaces button text
- Button disabled during loading
- Original text restored after completion

### 7.2 Error Handling

**Error Display Methods:**
- **Full-Screen Error**: For critical failures (cannot load screen)
  - Icon, message, description, retry button
- **Toast Messages**: For non-critical errors (favorite toggle failed)
  - Brief message at bottom, auto-dismiss
- **Inline Errors**: For form validation
  - Red text below field, icon indicator
- **Error Banners**: For persistent issues (no internet)
  - Top banner with dismiss action

**Retry Mechanisms:**
- Retry button on full-screen errors
- Tap message to retry on failed messages
- Pull-to-refresh for list failures
- Automatic retry for critical operations (with backoff)

### 7.3 Confirmation Dialogs

**Standard Dialog Structure:**
- Title (action being confirmed)
- Message (explanation)
- Warning (if destructive)
- Two buttons: Cancel (left) | Confirm (right)

**Destructive Actions:**
- Delete, Cancel, Reject: Use red/destructive styling
- Require explicit confirmation
- Cannot be undone warning

### 7.4 Empty States

**Consistent Elements:**
- Relevant icon (large, centered)
- Primary message (bold)
- Description text (explains why empty)
- Action button (if applicable) - guides next step

**Context-Specific:**
- Different messages for Tenants vs Owners
- Helpful guidance on what to do next
- Positive, encouraging tone

### 7.5 Navigation Transitions

**Standard Transitions:**
- **Push**: New screen slides in from right (iOS) or up (Android)
- **Pop**: Current screen slides out, reveals previous
- **Modal**: Screen slides up from bottom, partial overlay
- **Fade**: Cross-fade for tab switches

**Back Button Behavior:**
- Hardware back (Android): Pop current screen
- Swipe back (iOS): Swipe from left edge to pop
- Confirmation if unsaved changes exist

### 7.6 Accessibility

**Requirements:**
- All interactive elements have minimum 44x44pt touch target
- Sufficient color contrast (WCAG AA)
- Screen reader support (semantic labels)
- Keyboard navigation support
- Text scaling support (up to 200%)

### 7.7 Responsive Design

**Screen Size Support:**
- Small phones (320pt width minimum)
- Standard phones (375-428pt width)
- Large phones/phablets (428+ pt width)
- Tablets (768+ pt width) - adapted layouts

**Orientation:**
- Portrait: Primary orientation, optimized
- Landscape: Supported where applicable (not forced)

---

## 8. Appendix

### 8.1 Screen ID Reference

**Authentication (9 screens):**
- AUTH-001 to AUTH-009

**Tenant Screens (13 screens):**
- TENANT-001 to TENANT-013

**Owner Screens (6 screens):**
- OWNER-001 to OWNER-006

**Shared Screens (8 screens):**
- SHARED-001 to SHARED-008 (Transaction History added)

**Total: 36 screens documented**

### 8.2 API Endpoint Summary

All API endpoints follow RESTful conventions:
- Base URL: `https://api.findout.com/api/v1/`
- Authentication: Bearer token (JWT) in Authorization header
- Request format: JSON
- Response format: JSON
- Standard HTTP status codes

### 8.3 Image Specifications

**Photos:**
- Maximum size: 5MB per image
- Supported formats: JPG, PNG
- Recommended dimensions:
  - Profile photos: 400x400px (1:1 ratio)
  - Apartment photos: 1200x800px (3:2 ratio)
  - ID photos: 1200x1600px (3:4 ratio)
- Compression: Applied before upload

### 8.4 Localization

**Supported Languages:**
- English (en) - LTR
- Arabic (ar) - RTL

**Text Direction:**
- Automatic layout mirroring for RTL
- All UI elements support bidirectional text
- Date/time formats adapt to locale
- Number formats adapt to locale

### 8.5 Push Notifications

**Notification Types:**
- Booking status changes
- New messages
- New booking requests (owners)
- Account approval
- Review received (owners)

**Delivery:**
- iOS: Apple Push Notification Service (APNS)
- Android: Firebase Cloud Messaging (FCM)

---

## 🎉 Documentation Complete

**Customer Mobile App Screen Documentation is now complete!**

### Summary:
- **Total Screens**: 35 screens documented
- **Authentication**: 9 screens (registration, login, OTP)
- **Tenant Screens**: 13 screens (browse, book, manage, rate)
- **Owner Screens**: 6 screens (manage apartments, handle requests)
- **Shared Screens**: 8 screens (messages, profile, transaction history, settings)

### Each Screen Includes:
✅ Purpose and user entry points  
✅ Complete UI component breakdown  
✅ User interaction flows  
✅ Empty, loading, and error states  
✅ Validation rules  
✅ API endpoints with examples  
✅ Implementation notes  

### Additional Documentation:
✅ Common UI patterns  
✅ Error handling strategies  
✅ Navigation architecture  
✅ Accessibility guidelines  
✅ Responsive design considerations  

**This documentation is ready for:**
- Flutter developers (implementation)
- Backend developers (API development)
- UI/UX designers (visual design)
- QA engineers (testing)

---

