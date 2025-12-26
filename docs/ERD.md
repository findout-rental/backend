# Entity-Relationship Diagram (ERD)

## FindOut - Apartment Rental Application

**Version:** 1.0  
**Date:** 2024  
**Project:** FindOut  
**Database:** MySQL

---

## Table of Contents

1. [Overview](#1-overview)
2. [Entities and Attributes](#2-entities-and-attributes)
3. [Relationships](#3-relationships)
4. [Entity-Relationship Diagram](#4-entity-relationship-diagram)
5. [Database Schema](#5-database-schema)

---

## 1. Overview

This document describes the database design for FindOut application. The database follows a relational model with the following key entities:

- **Users** (with role: tenant, owner, admin)
- **Apartments**
- **Bookings**
- **Ratings**
- **Favorites**
- **Messages**
- **Notifications**
- **OTP Verifications**
- **Transactions** (Payment system)

---

## 2. Entities and Attributes

### 2.1 Users Entity

**Table Name:** `users`

**Description:** Stores all user information including tenants, owners, and admins. Role is determined by the `role` column.

**Attributes:**

| Attribute           | Type                                    | Constraints                                           | Description                          |
| ------------------- | --------------------------------------- | ----------------------------------------------------- | ------------------------------------ |
| id                  | BIGINT UNSIGNED                         | PRIMARY KEY, AUTO_INCREMENT                           | Unique user identifier               |
| mobile_number       | VARCHAR(20)                             | UNIQUE, NOT NULL                                      | User's mobile number for login       |
| password            | VARCHAR(255)                            | NOT NULL                                              | Hashed password for authentication   |
| first_name          | VARCHAR(100)                            | NOT NULL                                              | User's first name                    |
| last_name           | VARCHAR(100)                            | NOT NULL                                              | User's last name                     |
| personal_photo      | VARCHAR(255)                            | NOT NULL                                              | Path to personal photo               |
| date_of_birth       | DATE                                    | NOT NULL                                              | User's date of birth                 |
| id_photo            | VARCHAR(255)                            | NOT NULL                                              | Path to ID photo                     |
| role                | ENUM('tenant', 'owner', 'admin')        | NOT NULL                                              | User role                            |
| status              | ENUM('pending', 'approved', 'rejected') | NOT NULL, DEFAULT 'pending'                           | Registration status                  |
| language_preference | ENUM('ar', 'en')                        | DEFAULT 'en'                                          | Preferred language for notifications |
| balance             | DECIMAL(10, 2)                          | NOT NULL, DEFAULT 0.00                                | User account balance                 |
| created_at          | TIMESTAMP                               | DEFAULT CURRENT_TIMESTAMP                             | Registration date                    |
| updated_at          | TIMESTAMP                               | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last update date                     |

**Indexes:**

- PRIMARY KEY (id)
- UNIQUE KEY (mobile_number)
- INDEX (role)
- INDEX (status)

---

### 2.2 Apartments Entity

**Table Name:** `apartments`

**Description:** Stores apartment information including location, specifications, and photos.

**Attributes:**

| Attribute      | Type                                  | Constraints                                           | Description                                                 |
| -------------- | ------------------------------------- | ----------------------------------------------------- | ----------------------------------------------------------- |
| id             | BIGINT UNSIGNED                       | PRIMARY KEY, AUTO_INCREMENT                           | Unique apartment identifier                                 |
| owner_id       | BIGINT UNSIGNED                       | FOREIGN KEY, NOT NULL                                 | Reference to users.id (owner)                               |
| governorate    | VARCHAR(100)                          | NOT NULL                                              | Governorate name (English)                                  |
| governorate_ar | VARCHAR(100)                          | NULL                                                  | Governorate name (Arabic)                                   |
| city           | VARCHAR(100)                          | NOT NULL                                              | City name (English)                                         |
| city_ar        | VARCHAR(100)                          | NULL                                                  | City name (Arabic)                                          |
| address        | TEXT                                  | NOT NULL                                              | Full address (English)                                      |
| address_ar     | TEXT                                  | NULL                                                  | Full address (Arabic)                                       |
| nightly_price  | DECIMAL(10, 2)                        | NOT NULL                                              | Price per night/day                                         |
| monthly_price  | DECIMAL(10, 2)                        | NOT NULL                                              | Price per month (30 days)                                   |
| bedrooms       | TINYINT UNSIGNED                      | NOT NULL                                              | Number of bedrooms                                          |
| bathrooms      | TINYINT UNSIGNED                      | NOT NULL                                              | Number of bathrooms                                         |
| living_rooms   | TINYINT UNSIGNED                      | NOT NULL                                              | Number of living rooms                                      |
| size           | DECIMAL(8, 2)                         | NOT NULL                                              | Apartment size in square meters                             |
| description    | TEXT                                  | NULL                                                  | Apartment description (English)                             |
| description_ar | TEXT                                  | NULL                                                  | Apartment description (Arabic)                              |
| photos         | JSON                                  | NULL                                                  | Array of photo paths: ["photo1.jpg", "photo2.jpg"]          |
| amenities      | JSON                                  | NULL                                                  | Array of amenity keys: ["wifi", "parking", "ac"]            |
| status         | ENUM('active', 'inactive', 'deleted') | NOT NULL, DEFAULT 'active'                            | Apartment status                                            |
| created_at     | TIMESTAMP                             | DEFAULT CURRENT_TIMESTAMP                             | Creation date                                               |
| updated_at     | TIMESTAMP                             | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last update date                                            |

**Indexes:**

- PRIMARY KEY (id)
- FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
- INDEX (governorate)
- INDEX (governorate_ar)
- INDEX (city)
- INDEX (city_ar)
- INDEX (status)
- INDEX (nightly_price)

**Notes:**

- `photos` and `amenities` are stored as JSON arrays
- `amenities` stores keys (e.g., "wifi", "parking") for frontend translation
- Arabic columns (`governorate_ar`, `city_ar`, `address_ar`, `description_ar`) are optional
- Owner can provide Arabic translations when adding/editing apartment
- Frontend displays appropriate language based on user's language preference
- Owner must be approved before creating apartments
- **Pricing**: Owner sets both nightly_price and monthly_price. System automatically uses the better rate based on booking duration (compares daily calculation vs monthly calculation and uses whichever is cheaper)

---

### 2.3 Bookings Entity

**Table Name:** `bookings`

**Description:** Stores booking information including dates, status, and payment method.

**Attributes:**

| Attribute        | Type                                                                                                                            | Constraints                                           | Description                                               |
| ---------------- | ------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------- | --------------------------------------------------------- |
| id               | BIGINT UNSIGNED                                                                                                                 | PRIMARY KEY, AUTO_INCREMENT                           | Unique booking identifier                                 |
| tenant_id        | BIGINT UNSIGNED                                                                                                                 | FOREIGN KEY, NOT NULL                                 | Reference to users.id (tenant)                            |
| apartment_id     | BIGINT UNSIGNED                                                                                                                 | FOREIGN KEY, NOT NULL                                 | Reference to apartments.id                                |
| check_in_date    | DATE                                                                                                                            | NOT NULL                                              | Check-in date                                             |
| check_out_date   | DATE                                                                                                                            | NOT NULL                                              | Check-out date                                            |
| number_of_guests | TINYINT UNSIGNED                                                                                                                | NULL                                                  | Number of guests                                          |
| payment_method   | VARCHAR(50)                                                                                                                     | NOT NULL                                              | Payment method confirmation (e.g., "Cash", "Credit Card") |
| total_rent       | DECIMAL(10, 2)                                                                                                                  | NOT NULL                                              | Total rent amount calculated for this booking            |
| status           | ENUM('pending', 'approved', 'rejected', 'cancelled', 'modified_pending', 'modified_approved', 'modified_rejected', 'completed') | NOT NULL, DEFAULT 'pending'                           | Booking status                                            |
| created_at       | TIMESTAMP                                                                                                                       | DEFAULT CURRENT_TIMESTAMP                             | Booking creation date                                     |
| updated_at       | TIMESTAMP                                                                                                                       | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last update date                                          |

**Indexes:**

- PRIMARY KEY (id)
- FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE
- INDEX (status)
- INDEX (check_in_date)
- INDEX (check_out_date)
- INDEX (tenant_id, status)

**Notes:**

- Status tracks current booking state
- Modifications update the same record (no separate modification table)
- Check-out date must be after check-in date

---

### 2.4 Ratings Entity

**Table Name:** `ratings`

**Description:** Stores apartment ratings and reviews from tenants.

**Attributes:**

| Attribute    | Type             | Constraints                                           | Description                    |
| ------------ | ---------------- | ----------------------------------------------------- | ------------------------------ |
| id           | BIGINT UNSIGNED  | PRIMARY KEY, AUTO_INCREMENT                           | Unique rating identifier       |
| booking_id   | BIGINT UNSIGNED  | FOREIGN KEY, NOT NULL, UNIQUE                         | Reference to bookings.id       |
| apartment_id | BIGINT UNSIGNED  | FOREIGN KEY, NOT NULL                                 | Reference to apartments.id     |
| tenant_id    | BIGINT UNSIGNED  | FOREIGN KEY, NOT NULL                                 | Reference to users.id (tenant) |
| rating       | TINYINT UNSIGNED | NOT NULL, CHECK (rating BETWEEN 1 AND 5)              | Rating (1-5 stars)             |
| review_text  | TEXT             | NULL                                                  | Optional review text           |
| created_at   | TIMESTAMP        | DEFAULT CURRENT_TIMESTAMP                             | Rating date                    |
| updated_at   | TIMESTAMP        | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last update date               |

**Indexes:**

- PRIMARY KEY (id)
- FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
- FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE
- FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
- UNIQUE KEY (booking_id)
- INDEX (apartment_id)

**Notes:**

- One rating per booking (enforced by UNIQUE constraint)
- Rating can only be created after booking period has ended
- Average rating calculated from this table

---

### 2.5 Favorites Entity

**Table Name:** `favorites`

**Description:** Stores tenant's favorite apartments.

**Attributes:**

| Attribute    | Type            | Constraints                 | Description                    |
| ------------ | --------------- | --------------------------- | ------------------------------ |
| id           | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique favorite identifier     |
| tenant_id    | BIGINT UNSIGNED | FOREIGN KEY, NOT NULL       | Reference to users.id (tenant) |
| apartment_id | BIGINT UNSIGNED | FOREIGN KEY, NOT NULL       | Reference to apartments.id     |
| created_at   | TIMESTAMP       | DEFAULT CURRENT_TIMESTAMP   | When apartment was favorited   |

**Indexes:**

- PRIMARY KEY (id)
- FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE
- UNIQUE KEY (tenant_id, apartment_id)

**Notes:**

- Prevents duplicate favorites (same tenant, same apartment)
- Only tenants can have favorites

---

### 2.6 Messages Entity

**Table Name:** `messages`

**Description:** Stores in-app messages between users (tenants and owners).

**Attributes:**

| Attribute       | Type            | Constraints                                           | Description                       |
| --------------- | --------------- | ----------------------------------------------------- | --------------------------------- |
| id              | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT                           | Unique message identifier         |
| sender_id       | BIGINT UNSIGNED | FOREIGN KEY, NOT NULL                                 | Reference to users.id (sender)    |
| recipient_id    | BIGINT UNSIGNED | FOREIGN KEY, NOT NULL                                 | Reference to users.id (recipient) |
| message_text    | TEXT            | NOT NULL                                              | Message content                   |
| attachment_path | VARCHAR(255)    | NULL                                                  | Path to attached file/image       |
| is_read         | BOOLEAN         | DEFAULT FALSE                                         | Read status                       |
| created_at      | TIMESTAMP       | DEFAULT CURRENT_TIMESTAMP                             | Message timestamp                 |
| updated_at      | TIMESTAMP       | DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last update date                  |

**Indexes:**

- PRIMARY KEY (id)
- FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
- INDEX (sender_id, recipient_id)
- INDEX (recipient_id, is_read)
- INDEX (created_at)

**Notes:**

- Any user can message any other user
- Supports text messages and file/image attachments
- Read status tracked per message

---

### 2.7 Notifications Entity

**Table Name:** `notifications`

**Description:** Stores system notifications for users.

**Attributes:**

| Attribute  | Type            | Constraints                 | Description                                                                     |
| ---------- | --------------- | --------------------------- | ------------------------------------------------------------------------------- |
| id         | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique notification identifier                                                  |
| user_id    | BIGINT UNSIGNED | FOREIGN KEY, NOT NULL       | Reference to users.id                                                           |
| type       | VARCHAR(50)     | NOT NULL                    | Notification type (e.g., 'booking_approved', 'booking_rejected', 'new_message') |
| title      | VARCHAR(255)    | NOT NULL                    | Notification title (English)                                                    |
| title_ar   | VARCHAR(255)    | NULL                        | Notification title (Arabic)                                                     |
| message    | TEXT            | NOT NULL                    | Notification message (English)                                                  |
| message_ar | TEXT            | NULL                        | Notification message (Arabic)                                                   |
| booking_id | BIGINT UNSIGNED | FOREIGN KEY, NULL           | Reference to bookings.id (if related to booking)                                |
| is_read    | BOOLEAN         | DEFAULT FALSE               | Read status                                                                     |
| created_at | TIMESTAMP       | DEFAULT CURRENT_TIMESTAMP   | Notification timestamp                                                          |

**Indexes:**

- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
- INDEX (user_id, is_read)
- INDEX (created_at)

**Notes:**

- Notifications sent for all booking status changes
- Backend generates notifications in both English and Arabic when creating
- Frontend displays title/message based on user's language preference
- Arabic columns (`title_ar`, `message_ar`) are optional but recommended
- Can be linked to bookings or standalone

---

### 2.8 OTP Verifications Entity

**Table Name:** `otp_verifications`

**Description:** Stores OTP codes for registration verification only.

**Attributes:**

| Attribute     | Type            | Constraints                 | Description                    |
| ------------- | --------------- | --------------------------- | ------------------------------ |
| id            | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Unique OTP identifier          |
| mobile_number | VARCHAR(20)     | NOT NULL                    | Mobile number for verification |
| otp_code      | VARCHAR(6)      | NOT NULL                    | 6-digit OTP code               |
| expires_at    | TIMESTAMP       | NOT NULL                    | OTP expiration time            |
| verified_at   | TIMESTAMP       | NULL                        | When OTP was verified          |
| created_at    | TIMESTAMP       | DEFAULT CURRENT_TIMESTAMP   | OTP creation time              |

**Indexes:**

- PRIMARY KEY (id)
- INDEX (mobile_number, expires_at)
- INDEX (expires_at)

**Notes:**

- OTP is used ONLY for registration verification, not for login
- OTP expires after a set time (e.g., 5 minutes)
- Old OTPs should be cleaned up periodically
- After successful OTP verification, user creates password for future logins

---

### 2.9 Transactions Entity

**Table Name:** `transactions`

**Description:** Stores all balance transactions for users including deposits, withdrawals, rent payments, refunds, and cancellation fees.

**Attributes:**

| Attribute          | Type                                                                                                                          | Constraints                                           | Description                                                                    |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------- | ------------------------------------------------------------------------------ |
| id                 | BIGINT UNSIGNED                                                                                                               | PRIMARY KEY, AUTO_INCREMENT                           | Unique transaction identifier                                                  |
| user_id            | BIGINT UNSIGNED                                                                                                               | FOREIGN KEY, NOT NULL                                 | Reference to users.id (user whose balance is affected)                         |
| type               | ENUM('deposit', 'withdrawal', 'rent_payment', 'refund', 'cancellation_fee')                                                  | NOT NULL                                              | Transaction type                                                               |
| amount             | DECIMAL(10, 2)                                                                                                                | NOT NULL                                              | Transaction amount (positive for deposits/additions, negative for withdrawals) |
| related_booking_id | BIGINT UNSIGNED                                                                                                               | FOREIGN KEY, NULL                                     | Reference to bookings.id (if transaction is related to booking)                |
| related_user_id    | BIGINT UNSIGNED                                                                                                               | FOREIGN KEY, NULL                                     | Reference to users.id (related user, e.g., owner in rent payment)              |
| description        | TEXT                                                                                                                          | NULL                                                  | Transaction description/notes                                                  |
| created_at         | TIMESTAMP                                                                                                                     | DEFAULT CURRENT_TIMESTAMP                             | Transaction timestamp                                                          |

**Indexes:**

- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (related_booking_id) REFERENCES bookings(id) ON DELETE SET NULL
- FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL
- INDEX (user_id)
- INDEX (type)
- INDEX (related_booking_id)
- INDEX (created_at)

**Notes:**

- Tracks all balance changes for complete audit trail
- Transaction types:
  - `deposit`: Admin adds money to user balance (e.g., tenant pays admin cash)
  - `withdrawal`: Admin removes money from user balance (e.g., owner withdraws from admin)
  - `rent_payment`: Rent transferred from tenant to owner when booking is created
  - `refund`: Full refund returned to tenant (when booking rejected)
  - `cancellation_fee`: Partial refund (80% returned to tenant, 20% kept by owner as fee)
- Amount can be positive (increases balance) or negative (decreases balance)
- `related_booking_id` links transaction to specific booking
- `related_user_id` tracks the other party in transfers (e.g., owner receiving rent payment)
- `description` field allows admin to add notes (e.g., "Cash deposit from tenant John")

---

## 3. Relationships

### 3.1 Users Relationships

1. **Users → Apartments (One-to-Many)**

   - One owner can have many apartments
   - Foreign Key: `apartments.owner_id` → `users.id`
   - Cascade Delete: Yes

2. **Users → Bookings (One-to-Many as Tenant)**

   - One tenant can have many bookings
   - Foreign Key: `bookings.tenant_id` → `users.id`
   - Cascade Delete: Yes

3. **Users → Ratings (One-to-Many)**

   - One tenant can give many ratings
   - Foreign Key: `ratings.tenant_id` → `users.id`
   - Cascade Delete: Yes

4. **Users → Favorites (One-to-Many)**

   - One tenant can have many favorites
   - Foreign Key: `favorites.tenant_id` → `users.id`
   - Cascade Delete: Yes

5. **Users → Messages (One-to-Many as Sender)**

   - One user can send many messages
   - Foreign Key: `messages.sender_id` → `users.id`
   - Cascade Delete: Yes

6. **Users → Messages (One-to-Many as Recipient)**

   - One user can receive many messages
   - Foreign Key: `messages.recipient_id` → `users.id`
   - Cascade Delete: Yes

7. **Users → Notifications (One-to-Many)**
   - One user can have many notifications
   - Foreign Key: `notifications.user_id` → `users.id`
   - Cascade Delete: Yes

8. **Users → Transactions (One-to-Many)**
   - One user can have many transactions
   - Foreign Key: `transactions.user_id` → `users.id`
   - Cascade Delete: Yes

### 3.2 Apartments Relationships

1. **Apartments → Bookings (One-to-Many)**

   - One apartment can have many bookings
   - Foreign Key: `bookings.apartment_id` → `apartments.id`
   - Cascade Delete: Yes

2. **Apartments → Ratings (One-to-Many)**

   - One apartment can have many ratings
   - Foreign Key: `ratings.apartment_id` → `apartments.id`
   - Cascade Delete: Yes

3. **Apartments → Favorites (One-to-Many)**
   - One apartment can be favorited by many tenants
   - Foreign Key: `favorites.apartment_id` → `apartments.id`
   - Cascade Delete: Yes

### 3.3 Bookings Relationships

1. **Bookings → Ratings (One-to-One)**

   - One booking can have one rating
   - Foreign Key: `ratings.booking_id` → `bookings.id`
   - Unique Constraint: Yes
   - Cascade Delete: Yes

2. **Bookings → Notifications (One-to-Many)**
   - One booking can generate many notifications
   - Foreign Key: `notifications.booking_id` → `bookings.id`
   - Cascade Delete: SET NULL (notification history preserved)

3. **Bookings → Transactions (One-to-Many)**
   - One booking can have multiple transactions (rent payment, refund, cancellation fee)
   - Foreign Key: `transactions.related_booking_id` → `bookings.id`
   - Cascade Delete: SET NULL (transaction history preserved even if booking deleted)

---

## 4. Entity-Relationship Diagram

### 4.1 Visual ERD (Mermaid Diagram)

```mermaid
erDiagram
    USERS ||--o{ APARTMENTS : "owns"
    USERS ||--o{ BOOKINGS : "books_as_tenant"
    USERS ||--o{ RATINGS : "rates"
    USERS ||--o{ FAVORITES : "favorites"
    USERS ||--o{ MESSAGES : "sends"
    USERS ||--o{ MESSAGES : "receives"
    USERS ||--o{ NOTIFICATIONS : "receives"
    USERS ||--o{ TRANSACTIONS : "has"

    APARTMENTS ||--o{ BOOKINGS : "has"
    APARTMENTS ||--o{ RATINGS : "receives"
    APARTMENTS ||--o{ FAVORITES : "favorited_by"

    BOOKINGS ||--|| RATINGS : "rated_once"
    BOOKINGS ||--o{ NOTIFICATIONS : "generates"
    BOOKINGS ||--o{ TRANSACTIONS : "generates"

    USERS {
        bigint id PK
        varchar mobile_number UK
        varchar password
        varchar first_name
        varchar last_name
        varchar personal_photo
        date date_of_birth
        varchar id_photo
        enum role
        enum status
        enum language_preference
        decimal balance
        timestamp created_at
        timestamp updated_at
    }

    APARTMENTS {
        bigint id PK
        bigint owner_id FK
        varchar governorate
        varchar governorate_ar
        varchar city
        varchar city_ar
        text address
        text address_ar
        decimal price
        decimal nightly_price
        decimal monthly_price
        tinyint bedrooms
        tinyint bathrooms
        tinyint living_rooms
        decimal size
        text description
        text description_ar
        json photos
        json amenities
        enum status
        timestamp created_at
        timestamp updated_at
    }

    BOOKINGS {
        bigint id PK
        bigint tenant_id FK
        bigint apartment_id FK
        date check_in_date
        date check_out_date
        tinyint number_of_guests
        varchar payment_method
        decimal total_rent
        enum status
        timestamp created_at
        timestamp updated_at
    }

    RATINGS {
        bigint id PK
        bigint booking_id FK UK
        bigint apartment_id FK
        bigint tenant_id FK
        tinyint rating
        text review_text
        timestamp created_at
        timestamp updated_at
    }

    FAVORITES {
        bigint id PK
        bigint tenant_id FK
        bigint apartment_id FK
        timestamp created_at
    }

    MESSAGES {
        bigint id PK
        bigint sender_id FK
        bigint recipient_id FK
        text message_text
        varchar attachment_path
        boolean is_read
        timestamp created_at
        timestamp updated_at
    }

    NOTIFICATIONS {
        bigint id PK
        bigint user_id FK
        varchar type
        varchar title
        varchar title_ar
        text message
        text message_ar
        bigint booking_id FK
        boolean is_read
        timestamp created_at
    }

    OTP_VERIFICATIONS {
        bigint id PK
        varchar mobile_number
        varchar otp_code
        timestamp expires_at
        timestamp verified_at
        timestamp created_at
    }

    TRANSACTIONS {
        bigint id PK
        bigint user_id FK
        enum type
        decimal amount
        bigint related_booking_id FK
        bigint related_user_id FK
        text description
        timestamp created_at
    }
```

---

## 5. Database Schema

### 5.1 SQL Schema Script

```sql
-- Users Table
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mobile_number VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    personal_photo VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    id_photo VARCHAR(255) NOT NULL,
    role ENUM('tenant', 'owner', 'admin') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    language_preference ENUM('ar', 'en') DEFAULT 'en',
    balance DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Apartments Table
CREATE TABLE apartments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id BIGINT UNSIGNED NOT NULL,
    governorate VARCHAR(100) NOT NULL,
    governorate_ar VARCHAR(100),
    city VARCHAR(100) NOT NULL,
    city_ar VARCHAR(100),
    address TEXT NOT NULL,
    address_ar TEXT,
    nightly_price DECIMAL(10, 2) NOT NULL,
    monthly_price DECIMAL(10, 2) NOT NULL,
    bedrooms TINYINT UNSIGNED NOT NULL,
    bathrooms TINYINT UNSIGNED NOT NULL,
    living_rooms TINYINT UNSIGNED NOT NULL,
    size DECIMAL(8, 2) NOT NULL,
    description TEXT,
    description_ar TEXT,
    photos JSON,
    amenities JSON,
    status ENUM('active', 'inactive', 'deleted') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id),
    INDEX idx_governorate (governorate),
    INDEX idx_governorate_ar (governorate_ar),
    INDEX idx_city (city),
    INDEX idx_city_ar (city_ar),
    INDEX idx_status (status),
    INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings Table
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    apartment_id BIGINT UNSIGNED NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    number_of_guests TINYINT UNSIGNED,
    payment_method VARCHAR(50) NOT NULL,
    total_rent DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled', 'modified_pending', 'modified_approved', 'modified_rejected', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_apartment (apartment_id),
    INDEX idx_status (status),
    INDEX idx_dates (check_in_date, check_out_date),
    CHECK (check_out_date > check_in_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ratings Table
CREATE TABLE ratings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL UNIQUE,
    apartment_id BIGINT UNSIGNED NOT NULL,
    tenant_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_apartment (apartment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Favorites Table
CREATE TABLE favorites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    apartment_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    UNIQUE KEY uk_tenant_apartment (tenant_id, apartment_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_apartment (apartment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages Table
CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id BIGINT UNSIGNED NOT NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    message_text TEXT NOT NULL,
    attachment_path VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation (sender_id, recipient_id),
    INDEX idx_recipient_read (recipient_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications Table
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    title_ar VARCHAR(255),
    message TEXT NOT NULL,
    message_ar TEXT,
    booking_id BIGINT UNSIGNED,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OTP Verifications Table
CREATE TABLE otp_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mobile_number VARCHAR(20) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mobile (mobile_number, expires_at),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions Table
CREATE TABLE transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('deposit', 'withdrawal', 'rent_payment', 'refund', 'cancellation_fee') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    related_booking_id BIGINT UNSIGNED,
    related_user_id BIGINT UNSIGNED,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_booking (related_booking_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 6. Key Design Decisions

### 6.1 Single Users Table

- All users (tenants, owners, admins) stored in one table with `role` column
- Simplifies authentication and user management
- No data duplication

### 6.2 JSON Storage for Photos and Amenities

- Photos and amenities stored as JSON arrays in apartments table
- Simplifies schema but requires JSON queries for filtering
- Suitable for this use case where these are primarily display data

### 6.3 Booking Modifications

- Modifications update the same booking record
- Status tracks modification state (modified_pending, modified_approved, etc.)
- No separate modification history table

### 6.4 Status Tracking

- Current status only (no history tables)
- Status changes tracked through notifications
- Simpler schema, sufficient for requirements

### 6.5 One-to-One Rating per Booking

- Each booking can have exactly one rating
- Enforced by UNIQUE constraint on booking_id
- Rating only allowed after booking period ends (application logic)

### 6.6 Payment System Design

- Balance stored directly in users table for quick access
- Transactions table provides complete audit trail of all balance changes
- Rent calculated at booking creation: `apartment.price * number_of_periods`
- Total rent stored in bookings table for reference
- Payment happens immediately when booking is created (before owner approval)
- Refund logic handles rejection (full refund) vs cancellation (partial refund with fee)
- Transaction types clearly distinguish different payment flows

---

## 7. Indexes Summary

### Performance Optimization Indexes:

- **Users:** mobile_number (unique), role, status
- **Apartments:** owner_id, governorate, city, status, price
- **Bookings:** tenant_id+status (composite), apartment_id, check_in_date, check_out_date
- **Ratings:** apartment_id (for average calculation)
- **Favorites:** tenant_id, apartment_id (composite unique)
- **Messages:** sender_id+recipient_id (conversation lookup), recipient_id+is_read
- **Notifications:** user_id+is_read (unread notifications)
- **Transactions:** user_id, type, related_booking_id, created_at (for transaction history queries)

---

## 8. Constraints and Business Rules

### 8.1 Data Integrity Constraints

- Foreign keys with CASCADE DELETE for related data cleanup
- UNIQUE constraints prevent duplicates (mobile_number, tenant+apartment favorites, booking+rating)
- CHECK constraints validate data ranges (rating 1-5, check_out > check_in)

### 8.2 Business Rules (Application Level)

- Users must be approved before login
- Owners must be approved before adding apartments
- Bookings cannot overlap (enforced in application logic)
- Ratings only after booking period ends
- Cancellation only 24+ hours before check-in
- OTP expiration (5 minutes typical)

**Payment System Rules:**
- Tenant must have sufficient balance before booking (check balance >= total_rent)
- Rent is transferred from tenant to owner when booking is created
- If booking is rejected by owner: Full refund (100%) returned to tenant from owner
- If booking is cancelled by tenant: Partial refund (80% to tenant, 20% cancellation fee kept by owner)
- Admin can add money to tenant balance (when tenant pays cash to admin)
- Admin can withdraw money from owner balance (when owner takes money from admin)
- All balance changes are tracked in transactions table for audit trail
- Rent calculation: System compares `nightly_price × number_of_nights` vs `monthly_price × ceil(number_of_nights/30)` and uses whichever is cheaper. For bookings ≤ 30 days, uses daily rate calculation.

---

**End of Document**
