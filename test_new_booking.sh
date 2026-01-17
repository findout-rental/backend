#!/bin/bash

# Script to test new booking notification to admin

BASE_URL="http://localhost:8000/api"

echo "Testing New Booking Notification..."

# Get admin token
ADMIN_TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"mobile_number":"+963991877688","password":"admin123"}' | jq -r '.data.token // .token')

echo "Admin token obtained"

# Check notifications before
echo -e "\nNotifications BEFORE:"
BEFORE=$(curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.unread_count')
echo "Unread count: $BEFORE"

# Create a test booking notification via tinker
echo -e "\nCreating test booking notification..."
php artisan tinker --execute="
\$admin = App\Models\User::where('role', 'admin')->where('status', 'approved')->first();
if (\$admin) {
    \$notificationService = app(\App\Services\NotificationService::class);
    \$notification = \$notificationService->create(\$admin, 'new_booking', [
        'tenant_name' => 'Test Tenant',
        'apartment_address' => 'Test Apartment, Damascus',
    ]);
    echo 'Notification created with ID: ' . \$notification->id . PHP_EOL;
} else {
    echo 'Admin not found' . PHP_EOL;
}
"

# Check notifications after
echo -e "\nNotifications AFTER:"
AFTER=$(curl -s -X GET "$BASE_URL/notifications" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq -r '.data.unread_count')
echo "Unread count: $AFTER"

if [ "$AFTER" -gt "$BEFORE" ]; then
  echo -e "\n✅ SUCCESS: Notification count increased!"
else
  echo -e "\n❌ FAILED: Notification count did not increase"
fi
