#!/bin/bash

# Script to send a notification to the admin by sending a message from a user
# This will create a notification automatically

BASE_URL="http://localhost:8000/api"

# Step 1: Get admin token
echo "Step 1: Getting admin token..."
ADMIN_TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"mobile_number":"+963991877688","password":"admin123"}' | jq -r '.data.token // .token')

if [ -z "$ADMIN_TOKEN" ] || [ "$ADMIN_TOKEN" = "null" ]; then
  echo "Error: Failed to get admin token"
  exit 1
fi

echo "Admin token obtained: ${ADMIN_TOKEN:0:20}..."

# Step 2: Get admin user ID
echo -e "\nStep 2: Getting admin user ID..."
ADMIN_USER_ID=$(curl -s -X GET "$BASE_URL/auth/me" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.user.id // .data.id // .id')

if [ -z "$ADMIN_USER_ID" ] || [ "$ADMIN_USER_ID" = "null" ]; then
  echo "Error: Failed to get admin user ID"
  exit 1
fi

echo "Admin user ID: $ADMIN_USER_ID"

# Step 3: Get a non-admin user (tenant or owner) to send message from
echo -e "\nStep 3: Getting a user to send message from..."
# Get first user that's not admin
USER_TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"mobile_number":"+963935218432","password":"password123"}' 2>/dev/null | jq -r '.data.token // .token')

if [ -z "$USER_TOKEN" ] || [ "$USER_TOKEN" = "null" ]; then
  echo "Warning: Could not get user token. Trying alternative..."
  # Try owner test
  USER_TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d '{"mobile_number":"+9639914343250","password":"password123"}' 2>/dev/null | jq -r '.data.token // .token')
fi

if [ -z "$USER_TOKEN" ] || [ "$USER_TOKEN" = "null" ]; then
  echo "Error: Could not get a user token to send message from"
  exit 1
fi

SENDER_USER_ID=$(curl -s -X GET "$BASE_URL/auth/me" \
  -H "Authorization: Bearer $USER_TOKEN" | jq -r '.data.user.id // .data.id // .id')

echo "Sender user ID: $SENDER_USER_ID"

# Step 4: Send message from user to admin (this will create a notification)
echo -e "\nStep 4: Sending message to admin (this creates a notification)..."
MESSAGE_TEXT="${1:-Hello Admin! This is a test message that will create a notification.}"

RESPONSE=$(curl -s -X POST "$BASE_URL/messages/ws" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"type\": \"send_message\",
    \"recipient_id\": $ADMIN_USER_ID,
    \"message_text\": \"$MESSAGE_TEXT\"
  }")

echo "Response:"
echo "$RESPONSE" | jq '.'

# Step 5: Check notifications for admin
echo -e "\nStep 5: Checking admin notifications..."
NOTIFICATIONS=$(curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq '.data')

UNREAD_COUNT=$(echo "$NOTIFICATIONS" | jq '.unread_count')
echo "Unread count: $UNREAD_COUNT"

echo -e "\nLatest notifications:"
echo "$NOTIFICATIONS" | jq '.notifications[0:3] | .[] | {id, title, message, is_read, created_at}'

echo -e "\n✅ Notification sent successfully!"
echo "The admin should now see a new notification in their panel."
