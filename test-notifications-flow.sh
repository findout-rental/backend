#!/bin/bash

# Test script for Notifications System
# This script tests all notification endpoints using curl

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
BASE_URL="${BASE_URL:-http://localhost:8000/api}"
TENANT_MOBILE="${TENANT_MOBILE:-+963991877688}"
TENANT_PASSWORD="${TENANT_PASSWORD:-password123}"
OWNER_MOBILE="${OWNER_MOBILE:-+963935218432}"
OWNER_PASSWORD="${OWNER_PASSWORD:-password123}"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Notifications System Test Script${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Function to print section headers
print_section() {
    echo ""
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${YELLOW}$1${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

# Function to make API call and print response
api_call() {
    local method=$1
    local endpoint=$2
    local data=$3
    local token=$4
    local description=$5
    
    echo -e "${BLUE}→ $description${NC}"
    echo -e "${BLUE}  $method $endpoint${NC}"
    
    if [ -n "$data" ]; then
        echo -e "${BLUE}  Data: $data${NC}"
    fi
    
    if [ "$method" = "GET" ]; then
        response=$(curl -s -w "\n%{http_code}" -X GET \
            -H "Content-Type: application/json" \
            ${token:+-H "Authorization: Bearer $token"} \
            "$BASE_URL$endpoint")
    elif [ "$method" = "POST" ]; then
        response=$(curl -s -w "\n%{http_code}" -X POST \
            -H "Content-Type: application/json" \
            ${token:+-H "Authorization: Bearer $token"} \
            -d "$data" \
            "$BASE_URL$endpoint")
    elif [ "$method" = "PUT" ]; then
        response=$(curl -s -w "\n%{http_code}" -X PUT \
            -H "Content-Type: application/json" \
            ${token:+-H "Authorization: Bearer $token"} \
            ${data:+-d "$data"} \
            "$BASE_URL$endpoint")
    fi
    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    echo -e "${BLUE}  HTTP Status: $http_code${NC}"
    
    if [ "$http_code" -ge 200 ] && [ "$http_code" -lt 300 ]; then
        echo -e "${GREEN}✓ Success${NC}"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    else
        echo -e "${RED}✗ Failed${NC}"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
    
    echo ""
    
    # Return the body for further processing
    echo "$body"
}

# Step 1: Get OTP for Tenant
print_section "Step 1: Get OTP for Tenant"

TENANT_OTP_RESPONSE=$(api_call "POST" "/auth/send-otp" \
    "{\"mobile_number\":\"$TENANT_MOBILE\"}" \
    "" \
    "Send OTP to Tenant")

TENANT_OTP=$(echo "$TENANT_OTP_RESPONSE" | jq -r '.data.otp_code // empty' 2>/dev/null)

if [ -z "$TENANT_OTP" ] || [ "$TENANT_OTP" = "null" ]; then
    echo -e "${RED}✗ Failed to get tenant OTP. Response:${NC}"
    echo "$TENANT_OTP_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✓ Tenant OTP: $TENANT_OTP${NC}"

# Step 2: Verify Tenant OTP
print_section "Step 2: Verify Tenant OTP"

TENANT_VERIFY_RESPONSE=$(api_call "POST" "/auth/verify-otp" \
    "{\"mobile_number\":\"$TENANT_MOBILE\",\"otp_code\":\"$TENANT_OTP\"}" \
    "" \
    "Verify Tenant OTP")

# Step 3: Login as Tenant
print_section "Step 3: Login as Tenant"

TENANT_LOGIN_RESPONSE=$(api_call "POST" "/auth/login" \
    "{\"mobile_number\":\"$TENANT_MOBILE\",\"password\":\"$TENANT_PASSWORD\"}" \
    "" \
    "Login as Tenant")

TENANT_TOKEN=$(echo "$TENANT_LOGIN_RESPONSE" | jq -r '.data.token // empty' 2>/dev/null)

if [ -z "$TENANT_TOKEN" ] || [ "$TENANT_TOKEN" = "null" ]; then
    echo -e "${RED}✗ Failed to get tenant token. Please check credentials.${NC}"
    echo -e "${YELLOW}Note: Make sure you have created test users with:${NC}"
    echo -e "${YELLOW}  Tenant: mobile=$TENANT_MOBILE, password=$TENANT_PASSWORD${NC}"
    echo -e "${YELLOW}  Owner: mobile=$OWNER_MOBILE, password=$OWNER_PASSWORD${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Tenant authenticated. Token: ${TENANT_TOKEN:0:20}...${NC}"

# Step 4: Get OTP for Owner
print_section "Step 4: Get OTP for Owner"

OWNER_OTP_RESPONSE=$(api_call "POST" "/auth/send-otp" \
    "{\"mobile_number\":\"$OWNER_MOBILE\"}" \
    "" \
    "Send OTP to Owner")

OWNER_OTP=$(echo "$OWNER_OTP_RESPONSE" | jq -r '.data.otp_code // empty' 2>/dev/null)

if [ -z "$OWNER_OTP" ] || [ "$OWNER_OTP" = "null" ]; then
    echo -e "${RED}✗ Failed to get owner OTP. Response:${NC}"
    echo "$OWNER_OTP_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✓ Owner OTP: $OWNER_OTP${NC}"

# Step 5: Verify Owner OTP
print_section "Step 5: Verify Owner OTP"

OWNER_VERIFY_RESPONSE=$(api_call "POST" "/auth/verify-otp" \
    "{\"mobile_number\":\"$OWNER_MOBILE\",\"otp_code\":\"$OWNER_OTP\"}" \
    "" \
    "Verify Owner OTP")

# Step 6: Login as Owner
print_section "Step 6: Login as Owner"

OWNER_LOGIN_RESPONSE=$(api_call "POST" "/auth/login" \
    "{\"mobile_number\":\"$OWNER_MOBILE\",\"password\":\"$OWNER_PASSWORD\"}" \
    "" \
    "Login as Owner")

OWNER_TOKEN=$(echo "$OWNER_LOGIN_RESPONSE" | jq -r '.data.token // empty' 2>/dev/null)

if [ -z "$OWNER_TOKEN" ] || [ "$OWNER_TOKEN" = "null" ]; then
    echo -e "${RED}✗ Failed to get owner token. Please check credentials.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Owner authenticated. Token: ${OWNER_TOKEN:0:20}...${NC}"

# Step 7: List Tenant Notifications (Initial State)
print_section "Step 7: List Tenant Notifications (Initial State)"

TENANT_NOTIFICATIONS=$(api_call "GET" "/notifications" \
    "" \
    "$TENANT_TOKEN" \
    "Get tenant notifications")

NOTIFICATION_COUNT=$(echo "$TENANT_NOTIFICATIONS" | jq -r '.data.notifications | length' 2>/dev/null || echo "0")
UNREAD_COUNT=$(echo "$TENANT_NOTIFICATIONS" | jq -r '.data.unread_count // 0' 2>/dev/null || echo "0")

echo -e "${GREEN}✓ Found $NOTIFICATION_COUNT notifications ($UNREAD_COUNT unread)${NC}"

# Step 8: Update FCM Token (Tenant)
print_section "Step 8: Update FCM Token (Tenant)"

FCM_TOKEN="test_fcm_token_$(date +%s)_tenant"
api_call "POST" "/notifications/fcm-token" \
    "{\"fcm_token\":\"$FCM_TOKEN\"}" \
    "$TENANT_TOKEN" \
    "Update tenant FCM token"

# Step 9: List Owner Notifications
print_section "Step 9: List Owner Notifications"

OWNER_NOTIFICATIONS=$(api_call "GET" "/notifications" \
    "" \
    "$OWNER_TOKEN" \
    "Get owner notifications")

OWNER_NOTIFICATION_COUNT=$(echo "$OWNER_NOTIFICATIONS" | jq -r '.data.notifications | length' 2>/dev/null || echo "0")
OWNER_UNREAD_COUNT=$(echo "$OWNER_NOTIFICATIONS" | jq -r '.data.unread_count // 0' 2>/dev/null || echo "0")

echo -e "${GREEN}✓ Found $OWNER_NOTIFICATION_COUNT notifications ($OWNER_UNREAD_COUNT unread)${NC}"

# Step 10: Get First Notification ID (if exists)
print_section "Step 10: Mark Notification as Read (Tenant)"

FIRST_NOTIFICATION_ID=$(echo "$TENANT_NOTIFICATIONS" | jq -r '.data.notifications[0].id // empty' 2>/dev/null)

if [ -n "$FIRST_NOTIFICATION_ID" ] && [ "$FIRST_NOTIFICATION_ID" != "null" ]; then
    api_call "PUT" "/notifications/$FIRST_NOTIFICATION_ID/read" \
        "" \
        "$TENANT_TOKEN" \
        "Mark notification $FIRST_NOTIFICATION_ID as read"
else
    echo -e "${YELLOW}⚠ No notifications to mark as read${NC}"
fi

# Step 11: Mark All Notifications as Read (Owner)
print_section "Step 11: Mark All Notifications as Read (Owner)"

if [ "$OWNER_UNREAD_COUNT" -gt 0 ]; then
    api_call "PUT" "/notifications/read-all" \
        "" \
        "$OWNER_TOKEN" \
        "Mark all owner notifications as read"
else
    echo -e "${YELLOW}⚠ No unread notifications to mark${NC}"
fi

# Step 12: List Notifications with Filters
print_section "Step 12: List Notifications with Filters"

api_call "GET" "/notifications?per_page=5&unread_only=true" \
    "" \
    "$TENANT_TOKEN" \
    "Get unread notifications only (limit 5)"

# Step 13: Verify Notification Counts
print_section "Step 13: Verify Updated Notification Counts"

FINAL_TENANT_NOTIFICATIONS=$(api_call "GET" "/notifications" \
    "" \
    "$TENANT_TOKEN" \
    "Get final tenant notification count")

FINAL_UNREAD_COUNT=$(echo "$FINAL_TENANT_NOTIFICATIONS" | jq -r '.data.unread_count // 0' 2>/dev/null || echo "0")

echo -e "${GREEN}✓ Final unread count: $FINAL_UNREAD_COUNT${NC}"

# Step 14: Test Error Cases
print_section "Step 14: Test Error Cases"

# Try to mark non-existent notification as read
api_call "PUT" "/notifications/99999/read" \
    "" \
    "$TENANT_TOKEN" \
    "Try to mark non-existent notification as read (should fail)"

# Try to update FCM token with invalid data
api_call "POST" "/notifications/fcm-token" \
    "{}" \
    "$TENANT_TOKEN" \
    "Try to update FCM token without token (should fail)"

# Summary
print_section "Test Summary"

echo -e "${GREEN}✅ All notification endpoints tested!${NC}"
echo ""
echo -e "${BLUE}Tested Endpoints:${NC}"
echo -e "  ✓ GET /api/notifications"
echo -e "  ✓ GET /api/notifications?per_page=5&unread_only=true"
echo -e "  ✓ PUT /api/notifications/{id}/read"
echo -e "  ✓ PUT /api/notifications/read-all"
echo -e "  ✓ POST /api/notifications/fcm-token"
echo ""
echo -e "${YELLOW}Note: To test notification creation, you need to:${NC}"
echo -e "  - Create a booking (tenant → owner gets notification)"
echo -e "  - Approve/reject booking (owner → tenant gets notification)"
echo -e "  - Create a rating (tenant → owner gets notification)"
echo -e "  - Approve account (admin → user gets notification)"
echo ""

