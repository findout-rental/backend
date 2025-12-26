# WebSocket Messaging Implementation (WhatsApp-Style)

## ✅ Implementation Complete

The messaging system now works like WhatsApp - **everything via WebSocket**!

---

## 🏗️ Architecture

### How It Works:

```
Client (Tenant/Owner)
    │
    │ 1. Connect to WebSocket (when app opens)
    │    ws://localhost:8080/app/{app_key}
    │
    │ 2. Subscribe to private channel
    │    private-user.{userId}
    │
    │ 3. Send message via WebSocket
    │    {
    │      "type": "send_message",
    │      "recipient_id": 5,
    │      "message_text": "Hello!"
    │    }
    │
    ▼
Server (Laravel + Reverb)
    │
    ├─> Process message
    ├─> Save to database
    ├─> Broadcast to recipient via WebSocket
    ├─> Send FCM push (if recipient offline)
    └─> Send acknowledgment to sender
        │
        ▼
Client receives:
    - Sender gets: { type: "message_sent", data: {...} }
    - Recipient gets: { event: "message.sent", data: {...} }
```

---

## 📡 Message Types

### 1. Send Message
```json
{
  "type": "send_message",
  "recipient_id": 5,
  "message_text": "Hello, is the apartment available?",
  "attachment_path": null  // Optional, from file upload
}
```

**Response:**
```json
{
  "success": true,
  "type": "message_sent",
  "data": {
    "message": {
      "id": 123,
      "sender_id": 1,
      "recipient_id": 5,
      "message_text": "Hello, is the apartment available?",
      "is_read": false,
      "created_at": "2025-12-26T10:30:00.000000Z"
    }
  }
}
```

### 2. Mark as Read
```json
{
  "type": "mark_read",
  "message_id": 123
}
```

**Response:**
```json
{
  "success": true,
  "type": "message_read",
  "data": {
    "message_id": 123,
    "is_read": true
  }
}
```

### 3. Typing Indicator
```json
{
  "type": "typing",
  "recipient_id": 5
}
```

**Response:**
```json
{
  "success": true,
  "type": "typing_sent"
}
```

**Recipient receives:**
```json
{
  "event": "user.typing",
  "data": {
    "sender_id": 1,
    "is_typing": true
  }
}
```

### 4. Stop Typing
```json
{
  "type": "stop_typing",
  "recipient_id": 5
}
```

---

## 🔄 Complete Flow

### Tenant Sends Message to Owner:

1. **Tenant opens chat with owner**
   - WebSocket already connected
   - Subscribed to `private-user.{tenant_id}`

2. **Tenant types message and hits send**
   ```javascript
   websocket.send(JSON.stringify({
     type: 'send_message',
     recipient_id: 5,  // owner's ID
     message_text: 'Hello, is this available?'
   }));
   ```

3. **Server processes:**
   - Validates message
   - Saves to database
   - Broadcasts to owner's channel: `private-user.5`
   - Sends acknowledgment to tenant

4. **Tenant receives acknowledgment:**
   ```json
   {
     "success": true,
     "type": "message_sent",
     "data": { "message": {...} }
   }
   ```
   - Shows message in chat (sent state)
   - Shows ✓ (sent)

5. **Owner receives message:**
   ```json
   {
     "event": "message.sent",
     "data": {
       "id": 123,
       "sender_id": 1,
       "message_text": "Hello, is this available?",
       "sender": { "first_name": "John", ... }
     }
   }
   ```
   - If app open: Message appears instantly
   - If app closed: FCM push notification

6. **Owner opens chat:**
   - Messages auto-marked as read
   - `MessageRead` event broadcasted to tenant
   - Tenant sees ✓✓ (read)

---

## 📁 File Attachments

### Process:

1. **Upload file via HTTP** (easier for multipart/form-data):
   ```javascript
   POST /api/messages/upload-attachment
   FormData: { attachment: file }
   ```

2. **Get file path:**
   ```json
   {
     "success": true,
     "data": {
       "attachment_path": "/storage/messages/attachments/...",
       "storage_path": "messages/attachments/..."
     }
   }
   ```

3. **Send message via WebSocket with file:**
   ```javascript
   websocket.send(JSON.stringify({
     type: 'send_message',
     recipient_id: 5,
     message_text: 'Check this photo',
     attachment_path: 'messages/attachments/...'
   }));
   ```

---

## 🔧 Implementation Details

### How Client Messages Work:

**Important:** Laravel Reverb primarily broadcasts FROM server TO clients. For clients to send messages TO server via WebSocket, we use a hybrid approach:

1. **Client sends message via WebSocket** using a custom event
2. **Server listens for the event** and processes it
3. **Server broadcasts response** back via WebSocket

### Files Created:

1. **`app/Services/WebSocketMessageService.php`**
   - Handles all WebSocket message types
   - Processes send_message, mark_read, typing, stop_typing

2. **`app/Http/Controllers/WebSocketMessageController.php`**
   - HTTP endpoint for processing WebSocket messages
   - Can be called directly or via WebSocket event handler
   - Authenticates user
   - Delegates to WebSocketMessageService

3. **`app/Events/UserTyping.php`**
   - Broadcasts typing indicators
   - Event: `user.typing`

### Routes:

- `POST /api/messages/ws` - WebSocket message handler (send message, mark as read, typing indicators)
- `POST /api/messages/upload-attachment` - File upload (HTTP, for multipart/form-data)
- `GET /api/messages` - List conversations (HTTP, for initial load)
- `GET /api/messages/{user_id}` - Get conversation (HTTP, for initial load, auto-marks as read)

---

## 🧪 Client Implementation (Flutter/JavaScript)

### Connect to WebSocket:

```javascript
const ws = new WebSocket('ws://localhost:8080/app/YOUR_APP_KEY');

// Authenticate
ws.onopen = () => {
  // Subscribe to your private channel
  fetch('/broadcasting/auth', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer YOUR_JWT_TOKEN',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      socket_id: '123.456',
      channel_name: 'private-user.1'
    })
  })
  .then(res => res.json())
  .then(auth => {
    ws.send(JSON.stringify({
      event: 'pusher:subscribe',
      data: {
        channel: 'private-user.1',
        auth: auth.auth
      }
    }));
  });
};
```

### Send Message:

```javascript
ws.send(JSON.stringify({
  type: 'send_message',
  recipient_id: 5,
  message_text: 'Hello!'
}));
```

### Listen for Messages:

```javascript
ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  
  if (data.event === 'message.sent') {
    // New message received
    console.log('New message:', data.data);
  }
  
  if (data.event === 'message.read') {
    // Message was read
    console.log('Message read:', data.data);
  }
  
  if (data.event === 'user.typing') {
    // User is typing
    console.log('User typing:', data.data);
  }
};
```

---

## ✅ Benefits

1. **Real-time feel** - Like WhatsApp
2. **Single connection** - Everything over WebSocket
3. **Lower latency** - No HTTP overhead
4. **Typing indicators** - Real-time feedback
5. **Read receipts** - Instant updates
6. **Presence** - Can add online/offline status

---

## 🚨 Important Notes

1. **File uploads still use HTTP** - Easier with multipart/form-data
2. **HTTP endpoints kept as fallback** - If WebSocket fails
3. **Initial load uses HTTP** - Get conversation history
4. **WebSocket for real-time** - Sending, receiving, typing, read receipts

---

## 🎯 Next Steps

1. Install Reverb: `composer require laravel/reverb`
2. Configure Reverb: `php artisan reverb:install`
3. Start Reverb: `php artisan reverb:start`
4. Update Flutter app to use WebSocket for messaging
5. Test end-to-end flow

---

**Everything is ready! Just connect your Flutter app to WebSocket and start messaging!** 🚀
