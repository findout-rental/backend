#!/bin/bash

# Test Messaging Flow: Tenant → Owner
# This script tests the complete messaging flow between a tenant and owner

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

BASE_URL="http://localhost:8000/api"

echo -e "${BLUE}🧪 Testing Messaging Flow${NC}"
echo "============================================================"
echo ""

# Test users (adjust these based on your database)
TENANT_MOBILE="+963991877688"
OWNER_MOBILE="+963935218432"
PASSWORD="password123"

echo -e "${YELLOW}Step 1: Get OTP for Tenant${NC}"
TENANT_OTP_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/send-otp" \
  -H "Content-Type: application/json" \
  -d "{\"mobile_number\": \"$TENANT_MOBILE\"}")

TENANT_OTP=$(echo $TENANT_OTP_RESPONSE | grep -o '"otp_code":"[^"]*' | cut -d'"' -f4)

if [ -z "$TENANT_OTP" ]; then
    echo -e "${RED}❌ Failed to get tenant OTP${NC}"
    echo "$TENANT_OTP_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✅ Tenant OTP: $TENANT_OTP${NC}"
echo ""

echo -e "${YELLOW}Step 2: Verify Tenant OTP${NC}"
TENANT_VERIFY_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/verify-otp" \
  -H "Content-Type: application/json" \
  -d "{\"mobile_number\": \"$TENANT_MOBILE\", \"otp_code\": \"$TENANT_OTP\"}")

if ! echo "$TENANT_VERIFY_RESPONSE" | grep -q '"success":\s*true'; then
    echo -e "${RED}❌ Failed to verify tenant OTP${NC}"
    echo "$TENANT_VERIFY_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✅ Tenant OTP verified${NC}"
echo ""

echo -e "${YELLOW}Step 3: Login as Tenant${NC}"
TENANT_LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"mobile_number\": \"$TENANT_MOBILE\", \"password\": \"$PASSWORD\"}")

TENANT_TOKEN=$(echo $TENANT_LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TENANT_TOKEN" ]; then
    echo -e "${RED}❌ Failed to login as tenant${NC}"
    echo "$TENANT_LOGIN_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✅ Tenant logged in${NC}"
echo "Token: ${TENANT_TOKEN:0:20}..."
echo ""

echo -e "${YELLOW}Step 4: Get OTP for Owner${NC}"
OWNER_OTP_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/send-otp" \
  -H "Content-Type: application/json" \
  -d "{\"mobile_number\": \"$OWNER_MOBILE\"}")

OWNER_OTP=$(echo $OWNER_OTP_RESPONSE | grep -o '"otp_code":"[^"]*' | cut -d'"' -f4)

if [ -z "$OWNER_OTP" ]; then
    echo -e "${RED}❌ Failed to get owner OTP${NC}"
    echo "$OWNER_OTP_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✅ Owner OTP: $OWNER_OTP${NC}"
echo ""

echo -e "${YELLOW}Step 5: Verify Owner OTP${NC}"
OWNER_VERIFY_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/verify-otp" \
  -H "Content-Type: application/json" \
  -d "{\"mobile_number\": \"$OWNER_MOBILE\", \"otp_code\": \"$OWNER_OTP\"}")

if ! echo "$OWNER_VERIFY_RESPONSE" | grep -q '"success":\s*true'; then
    echo -e "${RED}❌ Failed to verify owner OTP${NC}"
    echo "$OWNER_VERIFY_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✅ Owner OTP verified${NC}"
echo ""

echo -e "${YELLOW}Step 6: Login as Owner${NC}"
OWNER_LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"mobile_number\": \"$OWNER_MOBILE\", \"password\": \"$PASSWORD\"}")

OWNER_TOKEN=$(echo $OWNER_LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$OWNER_TOKEN" ]; then
    echo -e "${RED}❌ Failed to login as owner${NC}"
    echo "$OWNER_LOGIN_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✅ Owner logged in${NC}"
echo "Token: ${OWNER_TOKEN:0:20}..."
echo ""

echo -e "${YELLOW}Step 7: Get Owner User ID${NC}"
OWNER_ME_RESPONSE=$(curl -s -X GET "$BASE_URL/auth/me" \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H "Content-Type: application/json")

OWNER_ID=$(echo $OWNER_ME_RESPONSE | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

if [ -z "$OWNER_ID" ]; then
    echo -e "${RED}❌ Failed to get owner ID${NC}"
    echo "$OWNER_ME_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✅ Owner ID: $OWNER_ID${NC}"
echo ""

echo -e "${YELLOW}Step 8: Tenant Lists Conversations${NC}"
TENANT_CONVERSATIONS=$(curl -s -X GET "$BASE_URL/messages" \
  -H "Authorization: Bearer $TENANT_TOKEN" \
  -H "Content-Type: application/json")

echo "$TENANT_CONVERSATIONS" | jq '.' 2>/dev/null || echo "$TENANT_CONVERSATIONS"
echo ""

echo -e "${YELLOW}Step 9: Tenant Sends Message to Owner (via WebSocket endpoint)${NC}"
MESSAGE_TEXT="Hello! Is this apartment still available?"
SEND_MESSAGE_RESPONSE=$(curl -s -X POST "$BASE_URL/messages/ws" \
  -H "Authorization: Bearer $TENANT_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"type\": \"send_message\",
    \"recipient_id\": $OWNER_ID,
    \"message_text\": \"$MESSAGE_TEXT\"
  }")

if echo "$SEND_MESSAGE_RESPONSE" | grep -q '"success":\s*true'; then
    echo -e "${GREEN}✅ Message sent successfully${NC}"
    MESSAGE_ID=$(echo $SEND_MESSAGE_RESPONSE | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    echo "Message ID: $MESSAGE_ID"
    echo "$SEND_MESSAGE_RESPONSE" | jq '.' 2>/dev/null || echo "$SEND_MESSAGE_RESPONSE"
else
    echo -e "${RED}❌ Failed to send message${NC}"
    echo "$SEND_MESSAGE_RESPONSE"
    exit 1
fi
echo ""

echo -e "${YELLOW}Step 10: Owner Lists Conversations${NC}"
OWNER_CONVERSATIONS=$(curl -s -X GET "$BASE_URL/messages" \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H "Content-Type: application/json")

echo "$OWNER_CONVERSATIONS" | jq '.' 2>/dev/null || echo "$OWNER_CONVERSATIONS"
echo ""

echo -e "${YELLOW}Step 11: Owner Gets Conversation with Tenant${NC}"
TENANT_ME_RESPONSE=$(curl -s -X GET "$BASE_URL/auth/me" \
  -H "Authorization: Bearer $TENANT_TOKEN" \
  -H "Content-Type: application/json")

TENANT_ID=$(echo $TENANT_ME_RESPONSE | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

if [ -z "$TENANT_ID" ]; then
    echo -e "${RED}❌ Failed to get tenant ID${NC}"
    exit 1
fi

OWNER_CONVERSATION=$(curl -s -X GET "$BASE_URL/messages/$TENANT_ID" \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H "Content-Type: application/json")

echo "$OWNER_CONVERSATION" | jq '.' 2>/dev/null || echo "$OWNER_CONVERSATION"
echo ""

echo -e "${YELLOW}Step 12: Owner Responds to Tenant${NC}"
RESPONSE_TEXT="Yes, it's available! When would you like to visit?"
OWNER_SEND_RESPONSE=$(curl -s -X POST "$BASE_URL/messages/ws" \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"type\": \"send_message\",
    \"recipient_id\": $TENANT_ID,
    \"message_text\": \"$RESPONSE_TEXT\"
  }")

if echo "$OWNER_SEND_RESPONSE" | grep -q '"success":\s*true'; then
    echo -e "${GREEN}✅ Owner response sent successfully${NC}"
    echo "$OWNER_SEND_RESPONSE" | jq '.' 2>/dev/null || echo "$OWNER_SEND_RESPONSE"
else
    echo -e "${RED}❌ Failed to send owner response${NC}"
    echo "$OWNER_SEND_RESPONSE"
    exit 1
fi
echo ""

echo -e "${YELLOW}Step 13: Tenant Gets Updated Conversation${NC}"
TENANT_CONVERSATION=$(curl -s -X GET "$BASE_URL/messages/$OWNER_ID" \
  -H "Authorization: Bearer $TENANT_TOKEN" \
  -H "Content-Type: application/json")

echo "$TENANT_CONVERSATION" | jq '.' 2>/dev/null || echo "$TENANT_CONVERSATION"
echo ""

echo "============================================================"
echo -e "${GREEN}✅ Messaging Flow Test Complete!${NC}"
echo "============================================================"

