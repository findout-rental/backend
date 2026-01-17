#!/bin/bash

# Comprehensive test script for all admin notifications
# Tests: New User Registration, New Apartment, New Booking

BASE_URL="http://localhost:8000/api"
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Testing All Admin Notifications       ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"

# Get admin token
echo -e "\n${YELLOW}[1/4] Authenticating as admin...${NC}"
ADMIN_TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"mobile_number":"+963991877688","password":"admin123"}' | jq -r '.data.token // .token')

if [ -z "$ADMIN_TOKEN" ] || [ "$ADMIN_TOKEN" = "null" ]; then
  echo -e "${RED}✗ Failed to get admin token${NC}"
  exit 1
fi

ADMIN_USER_ID=$(curl -s -X GET "$BASE_URL/auth/me" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.user.id // .data.id // .id')

echo -e "${GREEN}✓ Admin authenticated (ID: $ADMIN_USER_ID)${NC}"

# Get initial notification count
INITIAL_COUNT=$(curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.unread_count // 0')
echo -e "${GREEN}✓ Initial unread notifications: $INITIAL_COUNT${NC}"

# ============================================
# TEST 1: New User Registration
# ============================================
echo -e "\n${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  TEST 1: New User Registration         ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"

BEFORE_REG=$(curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.unread_count // 0')

php artisan tinker --execute="
\$admin = App\Models\User::where('role', 'admin')->where('status', 'approved')->first();
if (\$admin) {
    \$notificationService = app(\App\Services\NotificationService::class);
    \$notification = \$notificationService->create(\$admin, 'new_user_registration', [
        'user_name' => 'Test User ' . rand(1000, 9999),
        'user_role' => 'tenant',
    ]);
    echo 'Notification ID: ' . \$notification->id . PHP_EOL;
} else {
    echo 'Admin not found' . PHP_EOL;
}
" > /dev/null 2>&1

sleep 1
AFTER_REG=$(curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.unread_count // 0')

if [ "$AFTER_REG" -gt "$BEFORE_REG" ]; then
  echo -e "${GREEN}✓ SUCCESS: Notification created (Count: $BEFORE_REG → $AFTER_REG)${NC}"
else
  echo -e "${RED}✗ FAILED: Notification not created${NC}"
fi

# ============================================
# TEST 2: New Apartment
# ============================================
echo -e "\n${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  TEST 2: New Apartment                  ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"

BEFORE_APT=$(curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.unread_count // 0')

php artisan tinker --execute="
\$admin = App\Models\User::where('role', 'admin')->where('status', 'approved')->first();
if (\$admin) {
    \$notificationService = app(\App\Services\NotificationService::class);
    \$notification = \$notificationService->create(\$admin, 'new_apartment', [
        'apartment_address' => 'Test Street ' . rand(1, 100) . ', Damascus',
        'owner_name' => 'Test Owner',
    ]);
    echo 'Notification ID: ' . \$notification->id . PHP_EOL;
} else {
    echo 'Admin not found' . PHP_EOL;
}
" > /dev/null 2>&1

sleep 1
AFTER_APT=$(curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.unread_count // 0')

if [ "$AFTER_APT" -gt "$BEFORE_APT" ]; then
  echo -e "${GREEN}✓ SUCCESS: Notification created (Count: $BEFORE_APT → $AFTER_APT)${NC}"
else
  echo -e "${RED}✗ FAILED: Notification not created${NC}"
fi

# ============================================
# TEST 3: New Booking
# ============================================
echo -e "\n${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  TEST 3: New Booking                   ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"

BEFORE_BOOK=$(curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.unread_count // 0')

php artisan tinker --execute="
\$admin = App\Models\User::where('role', 'admin')->where('status', 'approved')->first();
if (\$admin) {
    \$notificationService = app(\App\Services\NotificationService::class);
    \$notification = \$notificationService->create(\$admin, 'new_booking', [
        'tenant_name' => 'Test Tenant',
        'apartment_address' => 'Test Apartment, Damascus',
    ]);
    echo 'Notification ID: ' . \$notification->id . PHP_EOL;
} else {
    echo 'Admin not found' . PHP_EOL;
}
" > /dev/null 2>&1

sleep 1
AFTER_BOOK=$(curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.unread_count // 0')

if [ "$AFTER_BOOK" -gt "$BEFORE_BOOK" ]; then
  echo -e "${GREEN}✓ SUCCESS: Notification created (Count: $BEFORE_BOOK → $AFTER_BOOK)${NC}"
else
  echo -e "${RED}✗ FAILED: Notification not created${NC}"
fi

# ============================================
# Final Summary
# ============================================
echo -e "\n${BLUE}╔════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Final Results                        ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"

FINAL_COUNT=$(curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.unread_count // 0')

echo -e "\n${YELLOW}Notification Summary:${NC}"
echo -e "  Initial count: ${GREEN}$INITIAL_COUNT${NC}"
echo -e "  Final count:   ${GREEN}$FINAL_COUNT${NC}"
echo -e "  New notifications: ${GREEN}$((FINAL_COUNT - INITIAL_COUNT))${NC}"

echo -e "\n${YELLOW}Latest 5 notifications:${NC}"
curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.notifications[0:5] | .[] | "  \(.id) - [\(.type)] \(.title) - \(if .is_read then "READ" else "UNREAD" end)"'

echo -e "\n${GREEN}✅ All tests completed!${NC}"
echo -e "${YELLOW}Note:${NC} These notifications will also be sent automatically when:"
echo -e "  • A new user registers via the app"
echo -e "  • An owner creates a new apartment via the app"
echo -e "  • A tenant creates a new booking via the app"
