# Postman Testing Guide: Messaging System

## 🎯 Overview

This guide shows you how to test the messaging system between a **Tenant** and an **Owner** using Postman.

---

## 📋 Prerequisites

1. **Postman installed** (version 8.0+)
2. **Laravel server running** (`php artisan serve`)
3. **Two test users created:**
   - Tenant: Mobile `+963991877688`, Password `password123`
   - Owner: Mobile `+963935218432`, Password `password123`

---

## 🚀 Step-by-Step Testing

### Step 1: Import Postman Collections

1. Open Postman
2. Click **Import** button
3. Import these files:
   - `docs/postman-collection-tenant.json`
   - `docs/postman-collection-owner.json`
   - `docs/postman-collection-admin.json`

### Step 2: Set Collection Variables

Each collection has variables. Set them:

**For Tenant Collection:**
- `base_url`: `http://localhost:8000/api`
- `mobile_number`: `+963991877688`
- `password`: `password123`

**For Owner Collection:**
- `base_url`: `http://localhost:8000/api`
- `mobile_number`: `+963935218432`
- `password`: `password123`

### Step 3: Authenticate Tenant

1. Open **Tenant Collection** → **Authentication** folder
2. Run requests in order:
   - **Send OTP** → Copy `otp_code` from response
   - **Verify OTP** → Use the OTP code
   - **Login** → Token is auto-saved to `tenant_token` variable

### Step 4: Authenticate Owner

1. Open **Owner Collection** → **Authentication** folder
2. Run requests in order:
   - **Send OTP** → Copy `otp_code` from response
   - **Verify OTP** → Use the OTP code
   - **Login** → Token is auto-saved to `owner_token` variable

### Step 5: Get User IDs

**Get Tenant ID:**
1. Tenant Collection → **Profile** → **Get Current User**
2. Note the `id` from response (e.g., `1`)

**Get Owner ID:**
1. Owner Collection → **Profile** → **Get Current User**
2. Note the `id` from response (e.g., `2`)

### Step 6: Test Messaging Flow

#### 6.1 Tenant Lists Conversations

1. Tenant Collection → **Messaging** → **List Conversations**
2. Should return empty array `[]` (no conversations yet)

#### 6.2 Tenant Sends Message to Owner

1. Tenant Collection → **Messaging** → **Send Message (WebSocket)**
2. Update request body:
   ```json
   {
     "type": "send_message",
     "recipient_id": 2,  // Owner's ID
     "message_text": "Hello! Is this apartment still available?"
   }
   ```
3. Send request
4. **Expected Response:**
   ```json
   {
     "success": true,
     "type": "message_sent",
     "data": {
       "message": {
         "id": 1,
         "sender_id": 1,
         "recipient_id": 2,
         "message_text": "Hello! Is this apartment still available?",
         "is_read": false,
         "created_at": "2025-12-26T10:30:00.000000Z"
       }
     }
   }
   ```

#### 6.3 Owner Lists Conversations

1. Owner Collection → **Messaging** → **List Conversations**
2. **Expected Response:**
   ```json
   {
     "success": true,
     "data": {
       "conversations": [
         {
           "user": {
             "id": 1,
             "first_name": "Tenant",
             "last_name": "Name"
           },
           "last_message": {
             "id": 1,
             "message_text": "Hello! Is this apartment still available?",
             "is_read": false
           },
           "unread_count": 1
         }
       ]
     }
   }
   ```

#### 6.4 Owner Gets Conversation with Tenant

1. Owner Collection → **Messaging** → **Get Conversation**
2. Update URL parameter: `user_id` = Tenant's ID (e.g., `1`)
3. Send request
4. **Expected Response:**
   ```json
   {
     "success": true,
     "data": {
       "conversation_with": {
         "id": 1,
         "first_name": "Tenant",
         "last_name": "Name"
       },
       "messages": [
         {
           "id": 1,
           "sender": { "id": 1, ... },
           "recipient": { "id": 2, ... },
           "message_text": "Hello! Is this apartment still available?",
           "is_read": true,  // Auto-marked as read when viewing
           "created_at": "2025-12-26T10:30:00.000000Z"
         }
       ]
     }
   }
   ```
5. **Note:** Messages are automatically marked as read when viewing conversation

#### 6.5 Owner Responds to Tenant

1. Owner Collection → **Messaging** → **Send Message (WebSocket)**
2. Update request body:
   ```json
   {
     "type": "send_message",
     "recipient_id": 1,  // Tenant's ID
     "message_text": "Yes, it's available! When would you like to visit?"
   }
   ```
3. Send request

#### 6.6 Tenant Gets Updated Conversation

1. Tenant Collection → **Messaging** → **Get Conversation**
2. Update URL parameter: `user_id` = Owner's ID (e.g., `2`)
3. Send request
4. Should see both messages in the conversation

#### 6.7 Test Mark as Read (Optional)

1. Tenant Collection → **Messaging** → **Mark Message as Read**
2. Update URL parameter: `id` = Message ID
3. Send request
4. **Expected Response:**
   ```json
   {
     "success": true,
     "message": "Message marked as read",
     "data": {
       "message_id": 1,
       "is_read": true
     }
   }
   ```

---

## 🧪 Additional Tests

### Test File Upload

1. Tenant Collection → **Messaging** → **Upload Attachment**
2. Go to **Body** tab → **form-data**
3. Add key: `attachment`, type: **File**
4. Select an image file
5. Send request
6. **Expected Response:**
   ```json
   {
     "success": true,
     "data": {
       "attachment_path": "/storage/messages/attachments/...",
       "storage_path": "messages/attachments/..."
     }
   }
   ```
7. Use `storage_path` in next message:
   ```json
   {
     "type": "send_message",
     "recipient_id": 2,
     "message_text": "Check this photo",
     "attachment_path": "messages/attachments/..."
   }
   ```

### Test Typing Indicator

1. Tenant Collection → **Messaging** → **Send Typing Indicator**
2. Update request body:
   ```json
   {
     "type": "typing",
     "recipient_id": 2
   }
   ```
3. Send request
4. Owner should receive typing event via WebSocket (if connected)

### Test Stop Typing

1. Tenant Collection → **Messaging** → **Stop Typing Indicator**
2. Update request body:
   ```json
   {
     "type": "stop_typing",
     "recipient_id": 2
   }
   ```
3. Send request

---

## ✅ Verification Checklist

- [ ] Tenant can send message to owner
- [ ] Owner receives message in conversations list
- [ ] Owner can view conversation with tenant
- [ ] Messages auto-marked as read when viewing
- [ ] Owner can respond to tenant
- [ ] Tenant sees owner's response
- [ ] File upload works
- [ ] Typing indicators work (if WebSocket connected)

---

## 🔍 Troubleshooting

### Issue: "Unauthorized" Error

**Solution:**
- Make sure you've completed authentication flow (OTP → Verify → Login)
- Check that token is saved in collection variables
- Verify token hasn't expired (24 hours)

### Issue: "User not found"

**Solution:**
- Verify recipient_id is correct
- Make sure both users are approved (status = 'approved')
- Check that users exist in database

### Issue: "OTP verification failed"

**Solution:**
- Make sure you verified OTP before login
- OTP expires after 5 minutes
- Request new OTP if expired

### Issue: Messages not appearing

**Solution:**
- Check that both users are authenticated
- Verify recipient_id matches the other user's ID
- Check server logs for errors

---

## 📚 Related Documentation

- `docs/websocket-messaging-implementation.md` - Complete implementation guide
- `docs/endpoints.md` - Full API documentation
- `test-messaging-flow.sh` - Automated curl test script

---

**Happy Testing!** 🚀

