# Upload User Photo Script

This script allows you to upload a profile photo for any user via the admin API.

## Usage

```bash
cd /home/ace/Desktop/findout/backend
./upload_user_photo.sh <user_id> <photo_path> <token>
```

## Example

1. **First, login as admin to get a token:**
```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"mobile_number":"+963991877688","password":"admin123"}' | jq -r '.data.token')
```

2. **Then upload the photo:**
```bash
./upload_user_photo.sh 1 ./profile_photo.png "Bearer $TOKEN"
```

Or if you already have a token:
```bash
./upload_user_photo.sh 1 ./profile_photo.png "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

## Parameters

- `<user_id>`: The ID of the user to upload the photo for
- `<photo_path>`: Path to the photo file (must be a .jpg, .jpeg, or .png file, max 5MB)
- `<token>`: Authorization token in format "Bearer <token>"

## Requirements

- `curl` must be installed
- `jq` must be installed (for JSON parsing)
- Backend server must be running on `http://localhost:8000`
- You must be authenticated as an admin user

## Notes

- The photo will be stored in `storage/app/public/users/photos/`
- Old photos are automatically deleted when uploading a new one
- Photo URLs will be returned as full URLs (e.g., `http://localhost:8000/storage/users/photos/...`)
