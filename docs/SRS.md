# Software Requirements Specification (SRS)

## FindOut - Apartment Rental Application

**Version:** 1.0  
**Date:** 2024  
**Project:** FindOut  
**Technology Stack:** Laravel (Backend API), Flutter (Customer Mobile App), Flutter Web (Admin Panel)

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Overall Description](#2-overall-description)
3. [System Features](#3-system-features)
4. [External Interface Requirements](#4-external-interface-requirements)
5. [System Constraints](#5-system-constraints)
6. [Non-Functional Requirements](#6-non-functional-requirements)

---

## 1. Introduction

### 1.1 Purpose

This document specifies the software requirements for FindOut, a mobile application for booking residential apartments. The system allows users to search for accommodation, view apartment details, and complete booking processes through a mobile interface, with administrative oversight through a Flutter web application.

### 1.2 Scope

FindOut is a full-stack application consisting of:

- **Customer Mobile Application (Flutter):** For tenants and apartment owners
- **Admin Web Application (Flutter Web):** For system administrators
- **Backend API (Laravel):** RESTful API serving both customer mobile app and admin web app

### 1.3 Definitions, Acronyms, and Abbreviations

- **SRS:** Software Requirements Specification
- **API:** Application Programming Interface
- **OTP:** One-Time Password
- **RTL:** Right-to-Left (text direction)
- **LTR:** Left-to-Right (text direction)
- **Admin:** System Administrator
- **Owner:** Apartment Owner
- **Tenant:** User seeking to rent apartments

### 1.4 References

- Project Requirements Document (project-requirements.md)

---

## 2. Overall Description

### 2.1 Product Perspective

FindOut is a standalone application that provides apartment rental services. The system integrates with:

- SMS service provider (for OTP verification)
- Push notification service (for real-time notifications)
- File storage service (for images and documents)

### 2.2 Product Functions

The system provides the following main functions:

1. User registration and authentication with OTP verification
2. Apartment listing and management
3. Apartment search and filtering
4. Booking management with conflict prevention
5. Rating and review system
6. In-app messaging between users
7. Favorites management
8. Administrative dashboard and user management
9. Multi-language support (Arabic/English)
10. Dark/Light theme support

### 2.3 User Classes and Characteristics

#### 2.3.1 Tenants

- Users seeking to rent apartments
- Access via mobile application only
- Can browse, search, book, rate, and message owners

#### 2.3.2 Apartment Owners

- Users who own and list apartments
- Access via mobile application only
- Can add apartments, manage bookings, and message tenants

#### 2.3.3 System Administrators

- Manage user registrations and system operations
- Access via Flutter web application (browser-based)
- Can approve/reject registrations, delete users, and view statistics

### 2.4 Operating Environment

- **Customer Mobile Application:** Android and iOS devices
- **Admin Web Application:** Modern web browsers (Chrome, Firefox, Safari, Edge) - Built with Flutter Web
- **Backend:** Linux/Windows server with PHP 8.1+ and MySQL database

### 2.5 Design and Implementation Constraints

- Backend must be implemented using Laravel framework
- Mobile application must be implemented using Flutter framework
- Database must use MySQL
- API must follow RESTful principles

---

## 3. System Features

### 3.1 Mandatory Requirements

#### 3.1.1 User Registration (REQ-M-001)

**Priority:** Mandatory (2 marks)

**Description:**
Users must be able to register in the mobile application using their mobile number. Registration is available for both apartment owners and tenants.

**Functional Requirements:**

- **REQ-M-001.1:** System shall allow users to enter their mobile number
- **REQ-M-001.2:** System shall send OTP via SMS to the provided mobile number
- **REQ-M-001.3:** System shall verify OTP before allowing registration to proceed
- **REQ-M-001.4:** System shall require users to select their role (Owner or Tenant) during registration
- **REQ-M-001.5:** After OTP verification, system shall require users to create login credentials (password)
- **REQ-M-001.6:** After OTP verification, system shall require users to complete all personal information fields:
  - First Name (required)
  - Last Name (required)
  - Personal Photo (required)
  - Date of Birth (required)
  - ID Photo (required)
- **REQ-M-001.7:** System shall set user status to "Pending" after registration submission
- **REQ-M-001.8:** System shall prevent users from logging in until Admin approves their registration

**Input:**

- Mobile number
- OTP code (for verification only)
- Password (for future logins)
- Role selection (Owner/Tenant)
- Personal information (First Name, Last Name, Personal Photo, Date of Birth, ID Photo)

**Output:**

- Registration confirmation
- Pending status notification

**Preconditions:**

- User has a valid mobile number
- SMS service is available

**Postconditions:**

- User account created with "Pending" status
- Admin receives notification of new registration request

---

#### 3.1.2 Personal Information Collection (REQ-M-002)

**Priority:** Mandatory (2 marks)

**Description:**
All users (tenants and apartment owners) must provide complete personal information during registration.

**Functional Requirements:**

- **REQ-M-002.1:** System shall require First Name (text field, required)
- **REQ-M-002.2:** System shall require Last Name (text field, required)
- **REQ-M-002.3:** System shall require Personal Photo (image upload, required)
- **REQ-M-002.4:** System shall require Date of Birth (date picker, required)
- **REQ-M-002.5:** System shall require ID Photo (image upload, required)
- **REQ-M-002.6:** System shall validate all fields before allowing submission
- **REQ-M-002.7:** System shall display validation errors for incomplete or invalid data

**Input:**

- First Name (string, max 100 characters)
- Last Name (string, max 100 characters)
- Personal Photo (image file, max 5MB, formats: JPG, PNG)
- Date of Birth (date, must be valid date)
- ID Photo (image file, max 5MB, formats: JPG, PNG)

**Output:**

- Validation success or error messages

**Preconditions:**

- User has completed OTP verification
- User has selected role (Owner/Tenant)

**Postconditions:**

- Personal information saved to user profile
- User can proceed to submit registration

---

#### 3.1.3 User Authentication (REQ-M-003)

**Priority:** Mandatory (2 marks)

**Description:**
Users must be able to log in and log out of the mobile application using token-based authentication.

**Functional Requirements:**

- **REQ-M-003.1:** System shall allow users to log in using their registered mobile number and stored credentials
- **REQ-M-003.2:** System shall use token-based authentication (JWT) for secure session management
- **REQ-M-003.3:** System shall check user approval status before allowing login
- **REQ-M-003.4:** System shall deny login if user status is "Pending" or "Rejected"
- **REQ-M-003.5:** System shall allow approved users to log in successfully
- **REQ-M-003.6:** System shall maintain user session until logout or token expiration (24 hours of inactivity)
- **REQ-M-003.7:** System shall provide logout functionality
- **REQ-M-003.8:** System shall invalidate session token upon logout
- **REQ-M-003.9:** System shall securely store authentication tokens on the device (Keychain/Keystore)
- **REQ-M-003.10:** System shall automatically refresh tokens before expiration for active users

**Input:**

- Mobile number
- Password/Credentials (established during registration)

**Output:**

- Login success or error message
- User session token (JWT) on success

**Preconditions:**

- User must be registered (completed OTP verification during registration)
- User must be approved by Admin

**Postconditions:**

- User session created with valid JWT token (on successful login)
- User session destroyed and token invalidated (on logout)

---

#### 3.1.4 Apartment Browsing and Viewing (REQ-M-004)

**Priority:** Mandatory (3 marks)

**Description:**
Users must be able to browse apartments and view their complete specifications.

**Functional Requirements:**

- **REQ-M-004.1:** System shall display a list of available apartments
- **REQ-M-004.2:** System shall display apartment specifications including:
  - Location (Governorate, City, Address)
  - Price (nightly rate and monthly rate - system uses best rate)
  - Number of Bedrooms
  - Number of Bathrooms
  - Number of Living Rooms
  - Apartment Size/Area (square meters)
  - Multiple Photos
  - Description/Amenities (WiFi, Parking, Air Conditioning, etc.)
  - Availability Calendar
  - Owner Information
  - Average Rating and Individual Reviews
- **REQ-M-004.3:** System shall allow users to view detailed apartment information
- **REQ-M-004.4:** System shall display apartment photos in a gallery view
- **REQ-M-004.5:** System shall show availability status for each apartment

**Input:**

- None (for browsing)
- Apartment ID (for viewing details)

**Output:**

- List of apartments with basic information
- Detailed apartment information with all specifications

**Preconditions:**

- Apartments must be added by approved owners
- User must be logged in (for some features)

**Postconditions:**

- User can view apartment details
- User can proceed to booking if interested

---

### 3.2 Basic Requirements

#### 3.2.1 Apartment Filtering (REQ-B-001)

**Priority:** Basic (2 marks)

**Description:**
Tenants must be able to filter apartments by governorate, city, price range, and multiple specifications simultaneously.

**Functional Requirements:**

- **REQ-B-001.1:** System shall allow filtering by Governorate (dropdown/selection)
- **REQ-B-001.2:** System shall allow filtering by City (dropdown/selection, dependent on governorate)
- **REQ-B-001.3:** System shall allow filtering by Price Range (minimum and maximum price)
- **REQ-B-001.4:** System shall allow multi-select filtering by specifications:
  - Number of Bedrooms (e.g., 1+, 2+, 3+)
  - Number of Bathrooms (e.g., 1+, 2+)
  - Number of Living Rooms
  - Amenities (WiFi, Parking, Air Conditioning, etc.) - multiple selection
- **REQ-B-001.5:** System shall apply all selected filters simultaneously (AND logic)
- **REQ-B-001.6:** System shall display filtered results in real-time
- **REQ-B-001.7:** System shall allow users to clear all filters

**Input:**

- Governorate selection
- City selection
- Minimum price
- Maximum price
- Specification selections (multiple)

**Output:**

- Filtered list of apartments matching all criteria

**Preconditions:**

- User is browsing apartments
- Apartments exist in the system

**Postconditions:**

- Filtered results displayed
- User can view filtered apartments

---

#### 3.2.2 Booking with Conflict Prevention (REQ-B-002)

**Priority:** Basic (2 marks)

**Description:**
Multiple users must be able to book apartments for specific periods without booking conflicts. The system must automatically prevent overlapping bookings.

**Functional Requirements:**

- **REQ-B-002.1:** System shall allow tenants to select check-in date and check-out date
- **REQ-B-002.2:** System shall check for existing approved bookings for the selected period
- **REQ-B-002.3:** System shall prevent booking if any part of the requested period overlaps with existing approved bookings
- **REQ-B-002.4:** System shall display error message if dates conflict
- **REQ-B-002.5:** System shall only allow booking for available date ranges
- **REQ-B-002.6:** System shall check tenant balance before allowing booking
- **REQ-B-002.7:** System shall prevent booking if tenant has insufficient balance
- **REQ-B-002.8:** System shall calculate total rent amount using nightly and monthly prices, automatically selecting the better rate for the booking duration
- **REQ-B-002.9:** System shall transfer rent from tenant balance to owner balance when booking is created
- **REQ-B-002.10:** System shall create transaction records for rent payment
- **REQ-B-002.11:** System shall create booking with status "Pending" after payment is processed
- **REQ-B-002.12:** System shall require owner approval before booking is confirmed

**Input:**

- Apartment ID
- Check-in date
- Check-out date
- Payment method confirmation (text selection: Cash, Credit Card, etc.)
- Number of guests (optional)

**Output:**

- Booking confirmation (if dates available)
- Error message (if dates conflict)

**Preconditions:**

- User is logged in as Tenant
- Apartment exists and is active
- Selected dates are in the future
- Tenant has sufficient balance (balance >= total_rent)

**Postconditions:**

- Total rent calculated and stored in booking
- Rent transferred from tenant balance to owner balance
- Transaction records created for payment
- Booking created with "Pending" status and total_rent
- Owner receives notification of booking request
- Tenant receives confirmation notification

---

#### 3.2.3 Booking Modification and Cancellation (REQ-B-003)

**Priority:** Basic (2 marks)

**Description:**
Users must be able to cancel or modify their bookings with specific rules and owner approval requirements.

**Functional Requirements:**

- **REQ-B-003.1:** System shall allow tenants to modify booking dates
- **REQ-B-003.2:** System shall allow tenants to modify other booking details (e.g., number of guests)
- **REQ-B-003.3:** System shall only show available dates when modifying (prevent conflicts)
- **REQ-B-003.4:** System shall create modification request with status "Modified - Pending"
- **REQ-B-003.5:** System shall require owner approval for all modifications
- **REQ-B-003.6:** System shall allow cancellation up to 24 hours before check-in time
- **REQ-B-003.7:** System shall prevent cancellation less than 24 hours before check-in
- **REQ-B-003.8:** System shall update booking status to "Cancelled" upon cancellation
- **REQ-B-003.9:** System shall process partial refund when booking is cancelled (80% to tenant, 20% kept by owner as cancellation fee)
- **REQ-B-003.10:** System shall create transaction records for refund and cancellation fee
- **REQ-B-003.11:** System shall send notifications for all modification and cancellation actions

**Input:**

- Booking ID
- New check-in date (for modification)
- New check-out date (for modification)
- New number of guests (for modification)
- Cancellation confirmation (for cancellation)

**Output:**

- Modification request confirmation
- Cancellation confirmation
- Error message (if cancellation not allowed)

**Preconditions:**

- User has an active or pending booking
- For cancellation: At least 24 hours before check-in

**Postconditions:**

- Booking status updated
- Owner receives notification (for modifications)
- Notifications sent to all parties

---

#### 3.2.4 Booking History (REQ-B-004)

**Priority:** Basic (2 marks)

**Description:**
Users must be able to view all their bookings, including previous, current, and cancelled bookings.

**Functional Requirements:**

- **REQ-B-004.1:** System shall display all bookings made by the user
- **REQ-B-004.2:** System shall categorize bookings as:
  - Current/Active bookings
  - Previous/Completed bookings
  - Cancelled bookings
- **REQ-B-004.3:** System shall display booking details including:
  - Apartment information
  - Check-in and check-out dates
  - Booking status
  - Booking date
- **REQ-B-004.4:** System shall allow filtering by booking status
- **REQ-B-004.5:** System shall allow sorting by date (newest/oldest)

**Input:**

- User ID (from session)
- Optional: Status filter
- Optional: Sort preference

**Output:**

- List of all user bookings with details

**Preconditions:**

- User is logged in
- User has made at least one booking (for display)

**Postconditions:**

- User can view their complete booking history

---

#### 3.2.5 Apartment Rating (REQ-B-005)

**Priority:** Basic (1 mark)

**Description:**
Tenants must be able to rate apartments they have previously booked, with specific timing and display requirements.

**Functional Requirements:**

- **REQ-B-005.1:** System shall allow rating only after booking period has ended
- **REQ-B-005.2:** System shall provide single overall rating (1-5 stars)
- **REQ-B-005.3:** System shall calculate and display average rating on apartment listing
- **REQ-B-005.4:** System shall display individual ratings on apartment details page
- **REQ-B-005.5:** System shall prevent multiple ratings for the same booking
- **REQ-B-005.6:** System shall allow users to view all ratings and reviews for an apartment

**Input:**

- Booking ID
- Rating (1-5 stars)
- Optional: Review text

**Output:**

- Rating confirmation
- Updated average rating displayed

**Preconditions:**

- User has a completed booking for the apartment
- Booking period has ended
- User has not already rated this booking

**Postconditions:**

- Rating saved
- Average rating updated
- Rating visible to other users

---

### 3.3 Additional Requirements

#### 3.3.1 Notification System (REQ-A-001)

**Priority:** Additional (2 marks)

**Description:**
System must send notifications to users for every change in their booking status.

**Functional Requirements:**

- **REQ-A-001.1:** System shall track all booking statuses:
  - Pending
  - Approved
  - Rejected
  - Cancelled
  - Modified (Pending)
  - Modified Approved
  - Modified Rejected
- **REQ-A-001.2:** System shall send push notification for every status change
- **REQ-A-001.3:** System shall send notifications in user's preferred language (Arabic/English)
- **REQ-A-001.4:** System shall notify tenants when:
  - Booking request is submitted
  - Owner approves booking
  - Owner rejects booking
  - Modification request is submitted
  - Owner approves modification
  - Owner rejects modification
  - Booking is cancelled
- **REQ-A-001.5:** System shall notify owners when:
  - New booking request is received
  - Tenant modifies booking
  - Tenant cancels booking

**Input:**

- Booking status change event
- User ID
- User language preference

**Output:**

- Push notification sent to user device

**Preconditions:**

- User has push notifications enabled
- Booking exists and status changes

**Postconditions:**

- Notification delivered to user
- Notification logged in system

---

#### 3.3.2 Multi-Language Support (REQ-A-002)

**Priority:** Additional (2 marks)

**Description:**
Application must support Arabic and English languages with automatic RTL/LTR direction switching.

**Functional Requirements:**

- **REQ-A-002.1:** System shall allow users to switch between Arabic and English
- **REQ-A-002.2:** System shall save language preference per user account
- **REQ-A-002.3:** System shall automatically switch to RTL layout when Arabic is selected
- **REQ-A-002.4:** System shall automatically switch to LTR layout when English is selected
- **REQ-A-002.5:** System shall translate all application text based on selected language
- **REQ-A-002.6:** System shall use saved language preference for notifications
- **REQ-A-002.7:** System shall apply language preference across all app screens

**Input:**

- Language selection (Arabic/English)

**Output:**

- Application interface in selected language
- RTL/LTR layout applied

**Preconditions:**

- User is logged in (for saving preference)

**Postconditions:**

- Language preference saved
- Interface updated immediately

---

#### 3.3.3 In-App Messaging (REQ-A-003)

**Priority:** Additional (2 marks)

**Description:**
Tenants and apartment owners must be able to communicate through an in-app messaging system.

**Functional Requirements:**

- **REQ-A-003.1:** System shall allow any tenant to message any apartment owner
- **REQ-A-003.2:** System shall allow apartment owners to message tenants
- **REQ-A-003.3:** System shall support text messages
- **REQ-A-003.4:** System shall support image/file attachments
- **REQ-A-003.5:** System shall send push notifications for new messages
- **REQ-A-003.6:** System shall display message history in conversation view
- **REQ-A-003.7:** System shall show read/unread message status
- **REQ-A-003.8:** System shall allow users to view conversation list

**Input:**

- Recipient user ID
- Message text
- Optional: Image/file attachment

**Output:**

- Message sent confirmation
- Push notification to recipient

**Preconditions:**

- User is logged in
- Recipient user exists

**Postconditions:**

- Message saved in database
- Recipient receives notification

---

#### 3.3.4 Dark/Light Mode (REQ-A-004)

**Priority:** Additional (2 marks)

**Description:**
Application must support dark and light theme modes.

**Functional Requirements:**

- **REQ-A-004.1:** System shall allow users to switch between Light and Dark mode
- **REQ-A-004.2:** System shall store theme preference locally on device
- **REQ-A-004.3:** System shall apply theme immediately upon selection
- **REQ-A-004.4:** System shall maintain theme preference across app sessions
- **REQ-A-004.5:** System shall apply theme to all application screens

**Input:**

- Theme selection (Light/Dark)

**Output:**

- Application theme updated

**Preconditions:**

- None

**Postconditions:**

- Theme preference saved locally
- Interface updated with selected theme

---

#### 3.3.5 Favorites List (REQ-A-005)

**Priority:** Additional (1 mark)

**Description:**
Tenants must be able to add apartments to a favorites list and book from the favorites list.

**Functional Requirements:**

- **REQ-A-005.1:** System shall allow logged-in tenants to add apartments to favorites
- **REQ-A-005.2:** System shall provide a dedicated Favorites page/screen
- **REQ-A-005.3:** System shall display all favorited apartments in the Favorites page
- **REQ-A-005.4:** System shall allow users to remove apartments from favorites
- **REQ-A-005.5:** System shall allow booking directly from Favorites page
- **REQ-A-005.6:** System shall use the same booking flow from Favorites as regular booking

**Input:**

- Apartment ID (to add/remove from favorites)
- Booking details (when booking from favorites)

**Output:**

- Favorites list updated
- Booking confirmation (when booking from favorites)

**Preconditions:**

- User is logged in as Tenant
- Apartment exists

**Postconditions:**

- Apartment added/removed from favorites
- Booking created (if booking from favorites)

---

### 3.4 Owner-Specific Features

#### 3.4.1 Apartment Management (REQ-O-001)

**Priority:** Basic

**Description:**
Apartment owners must be able to add and manage their apartments through the mobile application.

**Functional Requirements:**

- **REQ-O-001.1:** System shall allow owners to add apartments via mobile app
- **REQ-O-001.2:** System shall require all mandatory fields to be completed immediately:
  - Location (Governorate, City, Address) - required
  - Nightly Price (per night) - required
  - Monthly Price (per month) - required
  - Number of Bedrooms - required
  - Number of Bathrooms - required
  - Number of Living Rooms - required
  - Apartment Size - required
  - Photos (at least one) - required
  - Description - required
- **REQ-O-001.3:** System shall allow optional fields:
  - Additional amenities
  - Additional photos
- **REQ-O-001.4:** System shall allow owners to edit their apartments
- **REQ-O-001.5:** System shall allow owners to delete their apartments
- **REQ-O-001.6:** System shall prevent deletion if apartment has active bookings

**Input:**

- All apartment specification fields
- Images

**Output:**

- Apartment created/updated confirmation

**Preconditions:**

- User is logged in as Owner
- User is approved by Admin

**Postconditions:**

- Apartment added to system
- Apartment visible in listings

---

#### 3.4.2 Booking Management (REQ-O-002)

**Priority:** Basic

**Description:**
Apartment owners must be able to manage booking requests for their apartments through the mobile application.

**Functional Requirements:**

- **REQ-O-002.1:** System shall display all booking requests for owner's apartments
- **REQ-O-002.2:** System shall allow owners to approve booking requests
- **REQ-O-002.3:** System shall allow owners to reject booking requests
- **REQ-O-002.4:** System shall allow owners to approve modification requests
- **REQ-O-002.5:** System shall allow owners to reject modification requests
- **REQ-O-002.6:** System shall display booking details before approval/rejection
- **REQ-O-002.7:** System shall process full refund when owner rejects booking (100% returned to tenant from owner)
- **REQ-O-002.8:** System shall create transaction records for refund when booking is rejected
- **REQ-O-002.9:** System shall send notifications to tenants upon owner action

**Input:**

- Booking ID
- Action (Approve/Reject)

**Output:**

- Booking status updated
- Notification sent to tenant

**Preconditions:**

- Owner has apartments with pending bookings
- Owner is logged in

**Postconditions:**

- Booking status changed
- If rejected: Full refund processed (rent returned from owner to tenant)
- Transaction records created (if refund processed)
- Tenant notified

---

### 3.5 Admin Features

#### 3.5.1 Registration Management (REQ-ADM-001)

**Priority:** Mandatory

**Description:**
System administrators must be able to manage user registrations through a Flutter web application.

**Functional Requirements:**

- **REQ-ADM-001.1:** System shall provide Flutter web application for Admin access only
- **REQ-ADM-001.2:** System shall display dashboard with statistics:
  - Total users
  - Total apartments
  - Pending registrations count
  - Total bookings
- **REQ-ADM-001.3:** System shall list all pending registration requests
- **REQ-ADM-001.4:** System shall allow Admin to view user details before approving:
  - Personal information
  - ID photo
  - Registration date
- **REQ-ADM-001.5:** System shall allow Admin to approve registration requests
- **REQ-ADM-001.6:** System shall allow Admin to reject registration requests
- **REQ-ADM-001.7:** System shall send notification to user upon approval/rejection

**Input:**

- User ID
- Action (Approve/Reject)

**Output:**

- User status updated
- Notification sent to user

**Preconditions:**

- Admin is logged in to Flutter web application
- Pending registrations exist

**Postconditions:**

- User status changed to "Approved" or "Rejected"
- User can log in (if approved)

---

#### 3.5.2 User Management (REQ-ADM-002)

**Priority:** Additional

**Description:**
System administrators must be able to delete users from the application.

**Functional Requirements:**

- **REQ-ADM-002.1:** System shall allow Admin to view all users
- **REQ-ADM-002.2:** System shall allow Admin to view user details before deleting
- **REQ-ADM-002.3:** System shall allow Admin to delete users
- **REQ-ADM-002.4:** System shall prevent deletion of users with active bookings
- **REQ-ADM-002.5:** System shall display warning before deletion

**Input:**

- User ID
- Deletion confirmation

**Output:**

- User deleted confirmation

**Preconditions:**

- Admin is logged in
- User exists
- User has no active bookings (for deletion)

**Postconditions:**

- User account deleted
- User data removed from system

---

### 3.6 Payment System Features

#### 3.6.1 Balance Management (REQ-PAY-001)

**Priority:** Basic

**Description:**
System must maintain account balances for all users (tenants and owners) and track all balance transactions.

**Functional Requirements:**

- **REQ-PAY-001.1:** System shall maintain balance for each user (tenant and owner)
- **REQ-PAY-001.2:** System shall initialize user balance to 0.00 when account is created
- **REQ-PAY-001.3:** System shall track all balance changes in transactions table
- **REQ-PAY-001.4:** System shall allow users to view their current balance
- **REQ-PAY-001.5:** System shall allow users to view their transaction history

**Input:**

- User ID (from session)

**Output:**

- Current balance
- Transaction history list

**Preconditions:**

- User is logged in

**Postconditions:**

- Balance and transaction history displayed

---

#### 3.6.2 Admin Balance Operations (REQ-PAY-002)

**Priority:** Basic

**Description:**
System administrators must be able to add money to tenant balances and withdraw money from owner balances through the admin web application.

**Functional Requirements:**

- **REQ-PAY-002.1:** System shall allow Admin to add money to tenant balance
- **REQ-PAY-002.2:** System shall allow Admin to add optional description/notes when adding money (e.g., "Cash deposit from tenant John")
- **REQ-PAY-002.3:** System shall update tenant balance when money is added
- **REQ-PAY-002.4:** System shall create transaction record with type "deposit" when money is added
- **REQ-PAY-002.5:** System shall allow Admin to withdraw money from owner balance
- **REQ-PAY-002.6:** System shall allow Admin to add optional description/notes when withdrawing money
- **REQ-PAY-002.7:** System shall check owner has sufficient balance before allowing withdrawal
- **REQ-PAY-002.8:** System shall update owner balance when money is withdrawn
- **REQ-PAY-002.9:** System shall create transaction record with type "withdrawal" when money is withdrawn
- **REQ-PAY-002.10:** System shall display error message if owner has insufficient balance for withdrawal

**Input:**

- User ID (tenant or owner)
- Amount (positive decimal)
- Optional description/notes
- Action type (deposit for tenant, withdrawal for owner)

**Output:**

- Balance updated confirmation
- Transaction record created
- Error message (if insufficient balance for withdrawal)

**Preconditions:**

- Admin is logged in to Flutter web application
- User exists and is approved
- For withdrawal: Owner has sufficient balance

**Postconditions:**

- User balance updated
- Transaction record created with description
- Balance change logged for audit trail

---

#### 3.6.3 Rent Payment Processing (REQ-PAY-003)

**Priority:** Basic

**Description:**
System must process rent payments when tenants book apartments, transferring money from tenant balance to owner balance.

**Functional Requirements:**

- **REQ-PAY-003.1:** System shall calculate total rent amount based on:
  - Apartment price per period (night/day/month)
  - Number of periods (check-in to check-out)
  - Formula: `apartment.price * number_of_periods`
- **REQ-PAY-003.2:** System shall check tenant balance is sufficient before processing payment
- **REQ-PAY-003.3:** System shall prevent booking if tenant has insufficient balance
- **REQ-PAY-003.4:** System shall deduct total rent from tenant balance when booking is created
- **REQ-PAY-003.5:** System shall add total rent to owner balance when booking is created
- **REQ-PAY-003.6:** System shall create transaction record with type "rent_payment" for tenant (negative amount)
- **REQ-PAY-003.7:** System shall create transaction record with type "rent_payment" for owner (positive amount)
- **REQ-PAY-003.8:** System shall link both transaction records to the booking via related_booking_id
- **REQ-PAY-003.9:** System shall store calculated total_rent in bookings table

**Input:**

- Booking details (tenant_id, apartment_id, check_in_date, check_out_date)
- Apartment nightly_price and monthly_price

**Output:**

- Payment processed confirmation
- Transaction records created
- Booking created with total_rent

**Preconditions:**

- Tenant is logged in
- Tenant has sufficient balance
- Apartment exists and is active
- Selected dates are valid

**Postconditions:**

- Tenant balance decreased by total_rent
- Owner balance increased by total_rent
- Transaction records created and linked to booking
- Booking created with total_rent stored

---

#### 3.6.4 Refund Processing (REQ-PAY-004)

**Priority:** Basic

**Description:**
System must process refunds when bookings are rejected by owners or cancelled by tenants, with different refund policies for each scenario.

**Functional Requirements:**

- **REQ-PAY-004.1:** System shall process full refund (100%) when owner rejects booking
- **REQ-PAY-004.2:** System shall transfer full rent amount from owner balance back to tenant balance when booking is rejected
- **REQ-PAY-004.3:** System shall create transaction record with type "refund" for tenant (positive amount)
- **REQ-PAY-004.4:** System shall create transaction record with type "refund" for owner (negative amount)
- **REQ-PAY-004.5:** System shall link refund transactions to the booking via related_booking_id
- **REQ-PAY-004.6:** System shall process partial refund when tenant cancels booking
- **REQ-PAY-004.7:** System shall calculate partial refund as 80% of total rent
- **REQ-PAY-004.8:** System shall calculate cancellation fee as 20% of total rent (kept by owner)
- **REQ-PAY-004.9:** System shall transfer 80% of rent from owner balance back to tenant balance when booking is cancelled
- **REQ-PAY-004.10:** System shall create transaction record with type "refund" for tenant (80% of total rent, positive amount)
- **REQ-PAY-004.11:** System shall create transaction record with type "cancellation_fee" for owner (20% of total rent, no balance change, owner already has the money)
- **REQ-PAY-004.12:** System shall link cancellation transactions to the booking via related_booking_id

**Input:**

- Booking ID
- Refund reason (rejected or cancelled)
- Total rent amount (from booking)

**Output:**

- Refund processed confirmation
- Transaction records created
- Balance updates completed

**Preconditions:**

- Booking exists with status "pending" or "approved"
- Booking has total_rent amount
- Owner has sufficient balance (for rejection refund)

**Postconditions:**

- If rejected: Full refund processed, balances updated
- If cancelled: Partial refund (80%) processed, cancellation fee (20%) recorded
- Transaction records created and linked to booking
- Refund logged for audit trail

---

## 4. External Interface Requirements

### 4.1 User Interfaces

#### 4.1.1 Customer Mobile Application (Flutter)

- **Platform:** Android and iOS
- **Design:** Material Design (Android) and Cupertino (iOS)
- **Responsiveness:** Support for various screen sizes (phones and tablets)
- **Languages:** Arabic (RTL) and English (LTR)
- **Themes:** Light and Dark mode
- **Users:** Tenants and Apartment Owners

#### 4.1.2 Admin Web Application (Flutter Web)

- **Platform:** Modern web browsers (Chrome, Firefox, Safari, Edge)
- **Design:** Responsive Material Design
- **Responsiveness:** Optimized for desktop and tablet screens
- **Languages:** Arabic (RTL) and English (LTR)
- **Themes:** Light and Dark mode
- **Access:** Admin users only
- **Technology:** Built with Flutter Web framework

### 4.2 Hardware Interfaces

- **Mobile Devices:** Camera for photo capture
- **Storage:** Local storage for theme preferences

### 4.3 Software Interfaces

- **SMS Service:** Integration with SMS provider for OTP delivery
- **Push Notification Service:** Integration with FCM/APNS for notifications
- **File Storage:** Cloud storage service for images and documents

### 4.4 Communication Interfaces

- **API:** RESTful API using Laravel
- **Protocol:** HTTPS for all communications
- **Authentication:** Token-based authentication (JWT)

---

## 5. System Constraints

### 5.1 Regulatory Constraints

- User data privacy compliance
- Image storage and handling regulations

### 5.2 Hardware Constraints

- Mobile application requires internet connection
- Camera access required for photo uploads

### 5.3 Software Constraints

- Laravel 10+ required for backend
- Flutter 3.0+ required for mobile app
- MySQL 8.0+ required for database
- PHP 8.1+ required

### 5.4 Performance Constraints

- API response time: < 2 seconds
- Image upload size limit: 5MB per image
- Maximum images per apartment: 10

---

## 6. Non-Functional Requirements

### 6.1 Performance Requirements

- Application shall load apartment listings within 2 seconds
- Search and filter operations shall complete within 1 second
- Image uploads shall complete within 10 seconds (depending on connection)

### 6.2 Security Requirements

- All API communications shall use HTTPS
- User passwords/OTPs shall not be stored in plain text
- User sessions shall expire after 24 hours of inactivity
- Admin access shall require additional authentication

### 6.3 Reliability Requirements

- System shall be available 99% of the time
- Data backup shall be performed daily
- System shall handle concurrent users without performance degradation

### 6.4 Usability Requirements

- Application shall be intuitive and easy to navigate
- All user actions shall provide feedback (loading indicators, success/error messages)
- Application shall support accessibility features

### 6.5 Maintainability Requirements

- Code shall follow Laravel and Flutter best practices
- Code shall be well-documented
- Database schema shall be normalized

---

## 7. Appendices

### 7.1 Glossary

- **Booking:** A reservation made by a tenant for an apartment for a specific period
- **OTP:** One-Time Password sent via SMS for registration verification only (not used for login)
- **Pending:** Status indicating waiting for approval
- **RTL:** Right-to-Left text direction (for Arabic)
- **LTR:** Left-to-Right text direction (for English)
- **JWT:** JSON Web Token used for secure authentication and session management

### 7.2 Assumptions and Dependencies

- SMS service provider is available and reliable
- Push notification service is configured and working
- Users have stable internet connection
- Users have devices with camera capabilities

---

**End of Document**
