# Messaging API Testing Guide

Complete guide for testing messaging endpoints using **curl** and **Postman**.

---

## 📋 Prerequisites

1. **Server Running:** `php artisan serve` (default: http://localhost:8000)
2. **Redis Running:** `redis-cli ping` should return `PONG`
3. **Test Users:** At least 2 approved users in database
4. **OTP Verification:** Users must have verified OTP (or verify during testing)

---

## 🔐 Step 1: Authentication Flow

### 1.1 Send OTP

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/send-otp \
  -H "Content-Type: application/json" \
  -d '{
    "mobile_number": "+963991877688"
  }'
```

**Postman:**
- **Method:** POST
- **URL:** `http://localhost:8000/api/auth/send-otp`
- **Headers:**
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "mobile_number": "+963991877688"
  }
  ```

**Response:**
```json
{
  "success": true,
  "message": "OTP sent successfully",
  "otp_id": 123,
  "otp_code": "123456"  // Only in development/testing
}
```

### 1.2 Verify OTP

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{
    "mobile_number": "+963991877688",
    "otp_code": "123456"
  }'
```

**Postman:**
- **Method:** POST
- **URL:** `http://localhost:8000/api/auth/verify-otp`
- **Headers:**
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "mobile_number": "+963991877688",
    "otp_code": "123456"
  }
  ```

**Response:**
```json
{
  "success": true,
  "message": "OTP verified successfully",
  "otp_id": 123
}
```

### 1.3 Login (Get JWT Token)

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "mobile_number": "+963991877688",
    "password": "password123"
  }'
```

**Postman:**
- **Method:** POST
- **URL:** `http://localhost:8000/api/auth/login`
- **Headers:**
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "mobile_number": "+963991877688",
    "password": "password123"
  }
  ```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 1440,
    "user": {
      "id": 1,
      "mobile_number": "+963991877688",
      "first_name": "Admin",
      "last_name": "User",
      "role": "admin",
      "status": "approved"
    }
  }
}
```

**💡 Save the `token` value for subsequent requests!**

---

## 💬 Step 2: Messaging Endpoints

### 2.1 List Conversations

Get all conversations for the authenticated user.

**curl:**
```bash
curl -X GET http://localhost:8000/api/messages \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Postman:**
- **Method:** GET
- **URL:** `http://localhost:8000/api/messages`
- **Headers:**
  - `Authorization: Bearer YOUR_TOKEN_HERE`
  - `Content-Type: application/json`

**Response:**
```json
{
  "success": true,
  "message": "Conversations retrieved successfully",
  "data": {
    "conversations": [
      {
        "partner": {
          "id": 2,
          "first_name": "John",
          "last_name": "Doe",
          "personal_photo": "http://localhost:8000/storage/photos/..."
        },
        "last_message": {
          "id": 5,
          "message_text": "Hello!",
          "created_at": "2025-12-26T10:30:00.000000Z",
          "is_read": false
        },
        "unread_count": 2
      }
    ]
  }
}
```

---

### 2.2 Send Message

Send a message to another user.

**curl:**
```bash
curl -X POST http://localhost:8000/api/messages \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "recipient_id": 2,
    "message_text": "Hello! This is a test message."
  }'
```

**Postman:**
- **Method:** POST
- **URL:** `http://localhost:8000/api/messages`
- **Headers:**
  - `Authorization: Bearer YOUR_TOKEN_HERE`
  - `Content-Type: application/json`
- **Body (raw JSON):**
  ```json
  {
    "recipient_id": 2,
    "message_text": "Hello! This is a test message."
  }
  ```

**Response:**
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "message": {
      "id": 6,
      "sender": {
        "id": 1,
        "first_name": "Admin",
        "last_name": "User",
        "personal_photo": null
      },
      "recipient": {
        "id": 2,
        "first_name": "John",
        "last_name": "Doe",
        "personal_photo": null
      },
      "message_text": "Hello! This is a test message.",
      "attachment_path": null,
      "is_read": false,
      "created_at": "2025-12-26T10:35:00.000000Z"
    }
  }
}
```

**💡 What happens:**
1. Message saved to database
2. Database notification created
3. WebSocket event broadcasted (if recipient's app is open)
4. FCM push notification sent (if recipient not in chat)

---

### 2.3 Get Conversation

Get all messages in a conversation with a specific user.

**curl:**
```bash
curl -X GET http://localhost:8000/api/messages/2 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Postman:**
- **Method:** GET
- **URL:** `http://localhost:8000/api/messages/2` (where `2` is the partner's user ID)
- **Headers:**
  - `Authorization: Bearer YOUR_TOKEN_HERE`
  - `Content-Type: application/json`

**Response:**
```json
{
  "success": true,
  "message": "Conversation retrieved successfully",
  "data": {
    "partner": {
      "id": 2,
      "first_name": "John",
      "last_name": "Doe",
      "personal_photo": null
    },
    "messages": [
      {
        "id": 5,
        "sender_id": 1,
        "recipient_id": 2,
        "message_text": "Hello!",
        "attachment_path": null,
        "is_read": true,
        "created_at": "2025-12-26T10:30:00.000000Z",
        "sender": {
          "id": 1,
          "first_name": "Admin",
          "last_name": "User"
        }
      },
      {
        "id": 6,
        "sender_id": 2,
        "recipient_id": 1,
        "message_text": "Hi there!",
        "attachment_path": null,
        "is_read": false,
        "created_at": "2025-12-26T10:32:00.000000Z",
        "sender": {
          "id": 2,
          "first_name": "John",
          "last_name": "Doe"
        }
      }
    ]
  }
}
```

---

### 2.4 Mark Message as Read

Mark a specific message as read.

**curl:**
```bash
curl -X PUT http://localhost:8000/api/messages/6/read \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**Postman:**
- **Method:** PUT
- **URL:** `http://localhost:8000/api/messages/6/read` (where `6` is the message ID)
- **Headers:**
  - `Authorization: Bearer YOUR_TOKEN_HERE`
  - `Content-Type: application/json`

**Response:**
```json
{
  "success": true,
  "message": "Message marked as read",
  "data": {
    "message_id": 6,
    "is_read": true
  }
}
```

**💡 What happens:**
1. Message `is_read` updated to `true`
2. WebSocket event broadcasted to sender (shows ✓✓ Read)

---

## 🧪 Complete Test Flow

### Test Scenario: User 1 sends message to User 2

1. **User 1: Send OTP**
   ```bash
   curl -X POST http://localhost:8000/api/auth/send-otp \
     -H "Content-Type: application/json" \
     -d '{"mobile_number": "+963991877688"}'
   ```

2. **User 1: Verify OTP**
   ```bash
   curl -X POST http://localhost:8000/api/auth/verify-otp \
     -H "Content-Type: application/json" \
     -d '{"mobile_number": "+963991877688", "otp_code": "123456"}'
   ```

3. **User 1: Login**
   ```bash
   curl -X POST http://localhost:8000/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"mobile_number": "+963991877688", "password": "password123"}'
   ```
   **Save TOKEN1 from response**

4. **User 2: Send OTP**
   ```bash
   curl -X POST http://localhost:8000/api/auth/send-otp \
     -H "Content-Type: application/json" \
     -d '{"mobile_number": "+963935218432"}'
   ```

5. **User 2: Verify OTP**
   ```bash
   curl -X POST http://localhost:8000/api/auth/verify-otp \
     -H "Content-Type: application/json" \
     -d '{"mobile_number": "+963935218432", "otp_code": "654321"}'
   ```

6. **User 2: Login**
   ```bash
   curl -X POST http://localhost:8000/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"mobile_number": "+963935218432", "password": "password123"}'
   ```
   **Save TOKEN2 from response**

7. **User 1: Send Message to User 2**
   ```bash
   curl -X POST http://localhost:8000/api/messages \
     -H "Authorization: Bearer TOKEN1" \
     -H "Content-Type: application/json" \
     -d '{"recipient_id": 2, "message_text": "Hello User 2!"}'
   ```
   **Save MESSAGE_ID from response**

8. **User 2: List Conversations**
   ```bash
   curl -X GET http://localhost:8000/api/messages \
     -H "Authorization: Bearer TOKEN2" \
     -H "Content-Type: application/json"
   ```

9. **User 2: Get Conversation with User 1**
   ```bash
   curl -X GET http://localhost:8000/api/messages/1 \
     -H "Authorization: Bearer TOKEN2" \
     -H "Content-Type: application/json"
   ```

10. **User 2: Mark Message as Read**
    ```bash
    curl -X PUT http://localhost:8000/api/messages/MESSAGE_ID/read \
      -H "Authorization: Bearer TOKEN2" \
      -H "Content-Type: application/json"
    ```

---

## 📱 Postman Collection Setup

### Environment Variables

Create a Postman Environment with:

| Variable | Initial Value | Current Value |
|----------|---------------|---------------|
| `base_url` | `http://localhost:8000/api` | `http://localhost:8000/api` |
| `user1_mobile` | `+963991877688` | `+963991877688` |
| `user2_mobile` | `+963935218432` | `+963935218432` |
| `user1_token` | (empty) | (auto-filled after login) |
| `user2_token` | (empty) | (auto-filled after login) |
| `message_id` | (empty) | (auto-filled after sending message) |

### Postman Collection Structure

```
📁 Messaging API
├── 🔐 Authentication
│   ├── Send OTP (User 1)
│   ├── Verify OTP (User 1)
│   ├── Login (User 1) → Sets `user1_token`
│   ├── Send OTP (User 2)
│   ├── Verify OTP (User 2)
│   └── Login (User 2) → Sets `user2_token`
│
└── 💬 Messaging
    ├── List Conversations (User 1)
    ├── List Conversations (User 2)
    ├── Send Message (User 1 → User 2) → Sets `message_id`
    ├── Get Conversation (User 2 with User 1)
    └── Mark as Read (User 2)
```

### Postman Scripts (Tests Tab)

**For Login requests, add this script to save token:**
```javascript
if (pm.response.code === 200) {
    const jsonData = pm.response.json();
    if (jsonData.data && jsonData.data.token) {
        pm.environment.set("user1_token", jsonData.data.token);
        // or user2_token for User 2 login
    }
}
```

**For Send Message request, add this script to save message ID:**
```javascript
if (pm.response.code === 201) {
    const jsonData = pm.response.json();
    if (jsonData.data && jsonData.data.message && jsonData.data.message.id) {
        pm.environment.set("message_id", jsonData.data.message.id);
    }
}
```

---

## 🐛 Troubleshooting

### Error: "OTP verification required"
- **Solution:** Verify OTP first using `/api/auth/verify-otp`

### Error: "Invalid mobile number or password"
- **Solution:** Set password for test users:
  ```bash
  php artisan tinker --execute="\$user = \App\Models\User::find(1); \$user->password = bcrypt('password123'); \$user->save();"
  ```

### Error: "Your account is pending approval"
- **Solution:** Approve user:
  ```bash
  php artisan tinker --execute="\$user = \App\Models\User::find(1); \$user->status = 'approved'; \$user->save();"
  ```

### Error: "Unauthenticated" or 401
- **Solution:** Check token is valid and included in `Authorization: Bearer {token}` header

### Error: "Recipient not found"
- **Solution:** Verify recipient user ID exists and is approved

---

## ✅ Automated Test Script

Use the provided bash script for automated testing:

```bash
chmod +x test-messaging-api.sh
./test-messaging-api.sh
```

This script:
1. Gets test users
2. Verifies OTP for both users
3. Logs in to get tokens
4. Tests all messaging endpoints
5. Shows results

---

## 📊 Expected Results

After successful testing, you should see:

1. ✅ Messages created in database
2. ✅ Notifications created with `message_id` link
3. ✅ WebSocket events broadcasted (check Redis)
4. ✅ FCM push notifications (if tokens configured)
5. ✅ Read status updates working

---

**Happy Testing! 🚀**

