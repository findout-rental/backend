# Firebase FCM Quick Setup Guide

## What You Need to Copy (Based on Your Current Screen)

### ✅ What You Already Have:
- **Sender ID:** `967699131832` (this is your Project Number)
- **FCM API:** Enabled ✅

### ❌ What You DON'T Need:
- **Server Key** - This is deprecated and no longer available
- **Legacy API** - Don't enable this, it's deprecated

---

## What to Do Now (Step-by-Step)

### Step 1: Create Service Account

You're currently on the "Create credentials" page. Here's what to do:

1. **Select "Application data"** (not "User data")
   - This is for server-to-server communication
   - Click the radio button next to "Application data"

2. **Click "Next" or "Create"**

3. **Service Account Details:**
   - **Service account name:** `fcm-server`
   - **Service account ID:** Will auto-fill
   - Click "Create and Continue"

4. **Grant Role (Optional):**
   - You can skip this step or grant "Firebase Cloud Messaging Admin"
   - Click "Done"

### Step 2: Download JSON Key File

1. **After creating service account:**
   - You'll be redirected to the Service Accounts list
   - Find your newly created account (e.g., `fcm-server@findout-rental.iam.gserviceaccount.com`)
   - Click on it

2. **Go to "Keys" tab:**
   - Click "Keys" tab at the top
   - Click "Add Key" → "Create new key"
   - Select **"JSON"** format
   - Click "Create"
   - **A JSON file will download automatically**

3. **Save the JSON file:**
   - The file will be named something like: `findout-rental-xxxxx-xxxxx.json`
   - Save it somewhere safe (like your Downloads folder)
   - **IMPORTANT:** This file contains sensitive credentials!

### Step 3: Copy Sender ID

From Firebase Console → Project Settings → Cloud Messaging:
- **Sender ID:** `967699131832` ✅ (You already have this!)

---

## What to Copy for .env File

You need **TWO things**:

1. **Sender ID:** `967699131832` ✅ (Already have it!)

2. **Service Account JSON file path:**
   - After you move the JSON file to your Laravel project, you'll use:
   - `storage/app/firebase/fcm-service-account.json`

---

## Next Steps After Getting JSON File

1. **Move JSON file to Laravel project:**
   ```bash
   cd /home/ace/Desktop/findout/backend
   mkdir -p storage/app/firebase
   mv ~/Downloads/findout-rental-*.json storage/app/firebase/fcm-service-account.json
   chmod 600 storage/app/firebase/fcm-service-account.json
   ```

2. **Add to .env:**
   ```env
   FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/fcm-service-account.json
   FCM_SENDER_ID=967699131832
   ```

3. **Add to .gitignore:**
   ```bash
   echo "storage/app/firebase/*.json" >> .gitignore
   ```

---

## Summary

✅ **You have:** Sender ID (`967699131832`)  
⏳ **You need:** Service Account JSON file  
📝 **Action:** Create Service Account → Download JSON → Move to project

Once you have the JSON file downloaded, let me know and I'll help you set it up!

