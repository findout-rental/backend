-- =====================================================
-- FindOut - Apartment Rental Application
-- Database Schema SQL Script
-- Version: 1.0
-- Date: 2024
-- Database: MySQL 8.0+
-- =====================================================

-- Drop existing tables if they exist (in reverse order of dependencies)
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS apartments;
DROP TABLE IF EXISTS otp_verifications;
DROP TABLE IF EXISTS users;

-- =====================================================
-- Table: users
-- Description: Stores all user information including tenants, owners, and admins
-- =====================================================
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
    
    -- Indexes
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: apartments
-- Description: Stores apartment information including location, specifications, and photos
-- =====================================================
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
    
    -- Foreign Keys
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_owner (owner_id),
    INDEX idx_governorate (governorate),
    INDEX idx_governorate_ar (governorate_ar),
    INDEX idx_city (city),
    INDEX idx_city_ar (city_ar),
    INDEX idx_status (status),
    INDEX idx_nightly_price (nightly_price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: bookings
-- Description: Stores booking information including dates, status, and payment method
-- =====================================================
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    apartment_id BIGINT UNSIGNED NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    number_of_guests TINYINT UNSIGNED,
    payment_method VARCHAR(50) NOT NULL,
    total_rent DECIMAL(10, 2) NOT NULL,
    status ENUM(
        'pending', 
        'approved', 
        'rejected', 
        'cancelled', 
        'modified_pending', 
        'modified_approved', 
        'modified_rejected', 
        'completed'
    ) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_apartment (apartment_id),
    INDEX idx_status (status),
    INDEX idx_dates (check_in_date, check_out_date),
    
    -- Constraints
    CHECK (check_out_date > check_in_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: ratings
-- Description: Stores apartment ratings and reviews from tenants
-- =====================================================
CREATE TABLE ratings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL UNIQUE,
    apartment_id BIGINT UNSIGNED NOT NULL,
    tenant_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_apartment (apartment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: favorites
-- Description: Stores tenant's favorite apartments
-- =====================================================
CREATE TABLE favorites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    apartment_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (apartment_id) REFERENCES apartments(id) ON DELETE CASCADE,
    
    -- Indexes
    UNIQUE KEY uk_tenant_apartment (tenant_id, apartment_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_apartment (apartment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: messages
-- Description: Stores in-app messages between users (tenants and owners)
-- =====================================================
CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id BIGINT UNSIGNED NOT NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    message_text TEXT NOT NULL,
    attachment_path VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_conversation (sender_id, recipient_id),
    INDEX idx_recipient_read (recipient_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: notifications
-- Description: Stores system notifications for users
-- =====================================================
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
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: otp_verifications
-- Description: Stores OTP codes for registration verification only
-- =====================================================
CREATE TABLE otp_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mobile_number VARCHAR(20) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_mobile (mobile_number, expires_at),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: transactions
-- Description: Stores all balance transactions (deposits, withdrawals, payments, refunds)
-- =====================================================
CREATE TABLE transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('deposit', 'withdrawal', 'rent_payment', 'refund', 'cancellation_fee') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    related_booking_id BIGINT UNSIGNED,
    related_user_id BIGINT UNSIGNED,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_booking (related_booking_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Summary of Relationships:
-- =====================================================
-- 1. users (1) -> (M) apartments (owner_id) [CASCADE DELETE]
-- 2. users (1) -> (M) bookings (tenant_id) [CASCADE DELETE]
-- 3. users (1) -> (M) ratings (tenant_id) [CASCADE DELETE]
-- 4. users (1) -> (M) favorites (tenant_id) [CASCADE DELETE]
-- 5. users (1) -> (M) messages (sender_id) [CASCADE DELETE]
-- 6. users (1) -> (M) messages (recipient_id) [CASCADE DELETE]
-- 7. users (1) -> (M) notifications (user_id) [CASCADE DELETE]
-- 8. apartments (1) -> (M) bookings (apartment_id) [CASCADE DELETE]
-- 9. apartments (1) -> (M) ratings (apartment_id) [CASCADE DELETE]
-- 10. apartments (1) -> (M) favorites (apartment_id) [CASCADE DELETE]
-- 11. bookings (1) -> (1) ratings (booking_id) [CASCADE DELETE, UNIQUE]
-- 12. bookings (1) -> (M) notifications (booking_id) [SET NULL]
-- 13. users (1) -> (M) transactions (user_id) [CASCADE DELETE]
-- 14. bookings (1) -> (M) transactions (related_booking_id) [SET NULL]
-- =====================================================

-- =====================================================
-- End of Schema
-- =====================================================

