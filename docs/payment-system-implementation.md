# Payment System Implementation Guide

## FindOut - Apartment Rental Application

**Version:** 1.0  
**Date:** 2024  
**Feature:** Payment/Balance Management System

---

## Overview

This document describes the payment system implementation for the FindOut application. The system manages user balances, tracks all financial transactions, and handles rent payments, refunds, and administrative balance operations.

**Important Note:** This is a simulated payment system for university project purposes. No actual payment processing is involved. The system tracks virtual balances and transactions.

---

## System Architecture

### Database Changes

#### 1. Users Table - Balance Column

Added `balance` column to track user account balance:

```sql
balance DECIMAL(10, 2) NOT NULL DEFAULT 0.00
```

- Initial balance: 0.00 for all new users
- Updated whenever transactions occur
- Supports both tenants and owners

#### 2. Bookings Table - Total Rent Column

Added `total_rent` column to store calculated rent amount:

```sql
total_rent DECIMAL(10, 2) NOT NULL
```

- Calculated when booking is created
- Formula: `apartment.price * number_of_periods`
- Stored for reference and refund calculations

#### 3. Transactions Table - New Table

Created `transactions` table to track all balance changes:

```sql
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
    FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL
)
```

**Transaction Types:**
- `deposit`: Admin adds money to tenant balance
- `withdrawal`: Admin removes money from owner balance
- `rent_payment`: Rent transferred when booking is created
- `refund`: Money returned to tenant (full or partial)
- `cancellation_fee`: 20% fee recorded when booking is cancelled

---

## Payment Flows

### 1. Admin Adds Money to Tenant Balance

**Scenario:** Tenant pays admin cash in reality, admin adds money to tenant's virtual balance.

**Steps:**
1. Admin accesses admin web panel
2. Admin selects tenant
3. Admin enters amount and optional description (e.g., "Cash deposit from tenant John")
4. System updates tenant balance: `balance = balance + amount`
5. System creates transaction record:
   - `type = 'deposit'`
   - `amount = amount` (positive)
   - `user_id = tenant_id`
   - `description = admin's notes`

**Example:**
- Tenant current balance: $50.00
- Admin adds: $100.00
- New balance: $150.00
- Transaction: deposit, $100.00, "Cash deposit from tenant"

---

### 2. Admin Withdraws Money from Owner Balance

**Scenario:** Owner takes money from admin, admin deducts from owner's virtual balance.

**Steps:**
1. Admin accesses admin web panel
2. Admin selects owner
3. Admin enters amount to withdraw
4. System checks owner has sufficient balance
5. If sufficient: System updates owner balance: `balance = balance - amount`
6. System creates transaction record:
   - `type = 'withdrawal'`
   - `amount = amount` (stored as positive, but decreases balance)
   - `user_id = owner_id`
   - `description = admin's notes`

**Example:**
- Owner current balance: $500.00
- Admin withdraws: $200.00
- New balance: $300.00
- Transaction: withdrawal, $200.00, "Cash withdrawal for owner"

---

### 3. Tenant Books Apartment - Rent Payment

**Scenario:** Tenant creates booking, rent is immediately transferred from tenant to owner.

**Steps:**
1. Tenant selects apartment and dates
2. System calculates total rent:
   - Calculate number of nights: `number_of_nights = check_out_date - check_in_date`
   - For bookings ≤ 30 days: `total_rent = nightly_price × number_of_nights`
   - For bookings > 30 days: Compare both options:
     - Daily calculation: `nightly_price × number_of_nights`
     - Monthly calculation: `monthly_price × ceil(number_of_nights/30)`
     - Use whichever is cheaper
3. System checks tenant balance: `balance >= total_rent`
4. If insufficient balance: Show error "Insufficient balance. Please top up your account."
5. If sufficient balance:
   - Deduct from tenant: `tenant.balance = tenant.balance - total_rent`
   - Add to owner: `owner.balance = owner.balance + total_rent`
   - Create booking with `total_rent` stored
   - Create transaction for tenant:
     - `type = 'rent_payment'`
     - `amount = total_rent` (decreases balance)
     - `user_id = tenant_id`
     - `related_booking_id = booking_id`
     - `related_user_id = owner_id`
   - Create transaction for owner:
     - `type = 'rent_payment'`
     - `amount = total_rent` (increases balance)
     - `user_id = owner_id`
     - `related_booking_id = booking_id`
     - `related_user_id = tenant_id`

**Example 1 - Short Stay:**
- Apartment nightly_price: $50/night
- Apartment monthly_price: $800/month
- Booking period: 3 nights (≤ 30 days)
- Total rent: 3 × $50 = $150.00 (uses daily rate)

**Example 2 - Long Stay:**
- Apartment nightly_price: $50/night
- Apartment monthly_price: $800/month
- Booking period: 35 nights (> 30 days)
- Daily calculation: 35 × $50 = $1,750
- Monthly calculation: 2 × $800 = $1,600 (ceil(35/30) = 2 months)
- Total rent: $1,600.00 (uses monthly rate - cheaper option)
- Tenant balance before: $200.00
- Tenant balance after: $50.00
- Owner balance before: $500.00
- Owner balance after: $650.00
- Two transactions created (one for tenant, one for owner)

**Important:** Payment happens immediately when booking is created, not when owner approves.

---

### 4. Owner Rejects Booking - Full Refund

**Scenario:** Owner rejects booking, full refund is returned to tenant.

**Steps:**
1. Owner rejects booking request
2. System retrieves `total_rent` from booking
3. System processes full refund:
   - Deduct from owner: `owner.balance = owner.balance - total_rent`
   - Add to tenant: `tenant.balance = tenant.balance + total_rent`
4. System creates transaction for tenant:
   - `type = 'refund'`
   - `amount = total_rent`
   - `user_id = tenant_id`
   - `related_booking_id = booking_id`
   - `related_user_id = owner_id`
   - `description = 'Full refund - Booking rejected by owner'`
5. System creates transaction for owner:
   - `type = 'refund'`
   - `amount = total_rent` (negative effect on balance)
   - `user_id = owner_id`
   - `related_booking_id = booking_id`
   - `related_user_id = tenant_id`
   - `description = 'Refund issued - Booking rejected'`

**Example:**
- Booking total_rent: $150.00
- Tenant balance before refund: $50.00
- Tenant balance after refund: $200.00
- Owner balance before refund: $650.00
- Owner balance after refund: $500.00

---

### 5. Tenant Cancels Booking - Partial Refund

**Scenario:** Tenant cancels booking, 80% refund returned, 20% cancellation fee kept by owner.

**Steps:**
1. Tenant cancels booking (24+ hours before check-in)
2. System retrieves `total_rent` from booking
3. System calculates:
   - Refund amount: `total_rent * 0.80`
   - Cancellation fee: `total_rent * 0.20`
4. System processes partial refund:
   - Deduct from owner: `owner.balance = owner.balance - refund_amount`
   - Add to tenant: `tenant.balance = tenant.balance + refund_amount`
5. System creates transaction for tenant:
   - `type = 'refund'`
   - `amount = refund_amount` (80% of total_rent)
   - `user_id = tenant_id`
   - `related_booking_id = booking_id`
   - `related_user_id = owner_id`
   - `description = 'Partial refund - Booking cancelled (80%)'`
6. System creates transaction for owner:
   - `type = 'refund'`
   - `amount = refund_amount` (negative effect on balance)
   - `user_id = owner_id`
   - `related_booking_id = booking_id`
   - `related_user_id = tenant_id`
   - `description = 'Refund issued - Booking cancelled (80% returned)'`
7. System creates transaction record for cancellation fee (for audit):
   - `type = 'cancellation_fee'`
   - `amount = cancellation_fee` (20% of total_rent)
   - `user_id = owner_id`
   - `related_booking_id = booking_id`
   - `related_user_id = tenant_id`
   - `description = 'Cancellation fee - Owner keeps 20%'`
   - **Note:** This doesn't change balance (owner already has the money), just records the fee

**Example:**
- Booking total_rent: $150.00
- Refund (80%): $120.00
- Cancellation fee (20%): $30.00
- Tenant balance before refund: $50.00
- Tenant balance after refund: $170.00
- Owner balance before refund: $650.00
- Owner balance after refund: $530.00
- Owner keeps: $30.00 as cancellation fee

---

## Transaction Amount Handling

**Design Decision:** All transaction amounts are stored as **positive values** in the database. The application logic determines whether the amount increases or decreases the balance based on the transaction type and user context.

**Balance Update Rules:**
- `deposit`: Add amount to balance
- `withdrawal`: Subtract amount from balance
- `rent_payment` (for tenant): Subtract amount from balance
- `rent_payment` (for owner): Add amount to balance
- `refund` (for tenant): Add amount to balance
- `refund` (for owner): Subtract amount from balance
- `cancellation_fee`: No balance change (record only)

---

## Implementation Notes

### Rent Calculation Logic

```javascript
// Pseudocode for rent calculation
function calculateRent(apartment, checkInDate, checkOutDate) {
    const daysDiff = checkOutDate - checkInDate;
    
    switch(apartment.price_period) {
        case 'night':
            return apartment.price * daysDiff;
        case 'day':
            return apartment.price * daysDiff;
        case 'month':
            const months = Math.ceil(daysDiff / 30);
            return apartment.price * months;
    }
}
```

### Balance Validation

Before processing rent payment:
```javascript
if (tenant.balance < total_rent) {
    return error("Insufficient balance. Current balance: " + tenant.balance + ", Required: " + total_rent);
}
```

### Transaction Creation Pattern

When creating transactions, always:
1. Update user balance first (within database transaction)
2. Create transaction record(s) for audit trail
3. Link related entities (booking_id, related_user_id)
4. Add descriptive text for clarity

---

## API Endpoints (Recommendations)

### For Mobile App (Tenant/Owner)

- `GET /api/user/balance` - Get current balance
- `GET /api/user/transactions` - Get transaction history
- `POST /api/bookings` - Create booking (includes payment processing)

### For Admin Web App

- `GET /api/admin/users/{id}/balance` - Get user balance
- `POST /api/admin/users/{id}/deposit` - Add money to tenant
- `POST /api/admin/users/{id}/withdraw` - Withdraw money from owner
- `GET /api/admin/users/{id}/transactions` - Get user transaction history

---

## Testing Scenarios

1. **Insufficient Balance Test:**
   - Tenant tries to book with balance < total_rent
   - Expected: Error message, booking not created

2. **Successful Booking Test:**
   - Tenant has sufficient balance
   - Booking created, rent transferred, transactions recorded

3. **Rejection Refund Test:**
   - Owner rejects booking
   - Expected: Full refund processed, balances restored

4. **Cancellation Refund Test:**
   - Tenant cancels booking (24+ hours before check-in)
   - Expected: 80% refund to tenant, 20% kept by owner

5. **Admin Operations Test:**
   - Admin adds money to tenant: Balance increases
   - Admin withdraws from owner: Balance decreases (if sufficient)

---

## Database Migration

When implementing, use these SQL migration statements:

```sql
-- Add balance column to users
ALTER TABLE users ADD COLUMN balance DECIMAL(10, 2) NOT NULL DEFAULT 0.00;

-- Add total_rent column to bookings
ALTER TABLE bookings ADD COLUMN total_rent DECIMAL(10, 2) NOT NULL DEFAULT 0.00;

-- Update apartments table: Replace price/price_period with nightly_price/monthly_price
ALTER TABLE apartments 
  ADD COLUMN nightly_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER address_ar,
  ADD COLUMN monthly_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER nightly_price;

-- Migrate existing data (if any):
-- Assuming old price was nightly, you can set monthly_price as estimate or require owner to update
UPDATE apartments SET nightly_price = price WHERE nightly_price = 0;
UPDATE apartments SET monthly_price = nightly_price * 30 WHERE monthly_price = 0; -- Default estimate

-- Drop old columns
ALTER TABLE apartments DROP COLUMN price, DROP COLUMN price_period;

-- Update indexes
ALTER TABLE apartments DROP INDEX idx_price;
ALTER TABLE apartments ADD INDEX idx_nightly_price (nightly_price);

-- Create transactions table (see full SQL in database.sql)
```

**Note:** For existing bookings without total_rent, you may need to backfill using the new pricing logic:
```sql
UPDATE bookings b
JOIN apartments a ON a.id = b.apartment_id
SET b.total_rent = CASE
  WHEN DATEDIFF(b.check_out_date, b.check_in_date) <= 30 
    THEN a.nightly_price * DATEDIFF(b.check_out_date, b.check_in_date)
  ELSE LEAST(
    a.nightly_price * DATEDIFF(b.check_out_date, b.check_in_date),
    a.monthly_price * CEIL(DATEDIFF(b.check_out_date, b.check_in_date) / 30)
  )
END
WHERE b.total_rent = 0;
```

---

## Summary

The payment system provides:
- ✅ Virtual balance management for all users
- ✅ Complete transaction history for audit trail
- ✅ Automatic rent processing on booking creation
- ✅ Refund handling for rejected and cancelled bookings
- ✅ Admin tools for balance management
- ✅ Full integration with existing booking system

All financial operations are tracked in the transactions table, providing a complete audit trail for all money movements in the system.

---

**End of Document**

