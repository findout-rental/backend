#!/bin/bash

# Script to upload a profile photo to a user via the admin API
# Usage: ./upload_user_photo.sh <user_id> <photo_path> <token>
#
# Example:
#   ./upload_user_photo.sh 1 /path/to/photo.jpg "Bearer your_token_here"

if [ "$#" -lt 3 ]; then
    echo "Usage: $0 <user_id> <photo_path> <token>"
    echo "Example: $0 1 ./profile_photo.jpg 'Bearer eyJ0eXAiOiJKV1QiLCJhbGc...'"
    exit 1
fi

USER_ID=$1
PHOTO_PATH=$2
TOKEN=$3
BASE_URL="http://localhost:8000"

# Check if photo file exists
if [ ! -f "$PHOTO_PATH" ]; then
    echo "Error: Photo file not found: $PHOTO_PATH"
    exit 1
fi

# First, login as admin to get token if not provided
# If token is not provided, you'll need to login first
# TOKEN=$(curl -s -X POST $BASE_URL/api/auth/login \
#   -H "Content-Type: application/json" \
#   -d '{"mobile_number":"+963991877688","password":"admin123"}' | jq -r '.data.token')

# Upload photo using multipart/form-data
echo "Uploading photo to user ID $USER_ID..."
RESPONSE=$(curl -s -X POST "$BASE_URL/api/admin/users/$USER_ID/upload-photo" \
  -H "Authorization: $TOKEN" \
  -F "photo=@$PHOTO_PATH")

echo "Response:"
echo "$RESPONSE" | jq '.'

# Check if upload was successful
if echo "$RESPONSE" | jq -e '.success == true' > /dev/null 2>&1; then
    echo "✓ Photo uploaded successfully!"
    PHOTO_URL=$(echo "$RESPONSE" | jq -r '.data.user.personal_photo // .data.personal_photo')
    echo "Photo URL: $BASE_URL$PHOTO_URL"
else
    echo "✗ Upload failed"
    echo "$RESPONSE" | jq -r '.message // "Unknown error"'
    exit 1
fi
