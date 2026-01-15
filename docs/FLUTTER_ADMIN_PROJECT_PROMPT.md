# Flutter Admin Web Panel - Complete Project Creation Prompt

## Project Overview

Create a Flutter Web application for the FindOut Admin Panel. This is a desktop-only admin dashboard (minimum 1024px width) for system administrators to manage user registrations, view statistics, and manage the apartment rental platform.

## Project Setup Command

```bash
# Create Flutter web project
flutter create --platforms=web admin_web
cd admin_web

# Initialize GetStorage
# (Will be done in code)

# Project structure will follow DDD (Domain Driven Design)
```

## Key Requirements

### 1. Technology Stack
- **Framework**: Flutter Web (latest stable)
- **State Management**: GetX (get: ^4.6.6)
- **HTTP Client**: Dio (dio: ^5.4.0)
- **Local Storage**: GetStorage (get_storage: ^2.1.1)
- **Package Name**: `com.findout.adminweb`

### 2. Architecture
- **Pattern**: Domain Driven Design (DDD)
- **Structure**: 
  - `domain/` - Entities, Repositories (interfaces), Use Cases
  - `data/` - Models, Repository implementations, Data Sources
  - `presentation/` - Controllers, Pages, Widgets
  - `core/` - Constants, Utils, Theme, Network

### 3. Design Requirements
- **Minimum Width**: 1024px (show message for smaller screens)
- **Theme**: Material Design with Light/Dark mode support
- **Languages**: English (LTR) and Arabic (RTL)
- **Responsive**: Desktop-optimized only (no mobile adaptation)

### 4. API Integration
- **Base URL**: `http://localhost:8000/api` (configurable)
- **Authentication**: JWT Bearer token
- **Note**: Some APIs may not be ready yet - mark with `// TODO: API not ready`

## Implementation Files Reference

### Core Documentation Files
1. **`docs/admin-web-screens.md`** - Complete screen specifications (11 screens)
2. **`docs/postman-collection-admin.json`** - API endpoints reference
3. **`docs/SRS.md`** - Software requirements
4. **`docs/ERD.md`** - Database schema reference

### Implementation Guides
1. **`docs/flutter-admin-web-implementation-guide.md`** - Complete project structure and setup
2. **`docs/flutter-admin-screens-implementation.md`** - Screen-by-screen implementation details

## Screens to Implement (11 Total)

### Authentication (1 screen)
1. ✅ **Login Screen** (ADMIN-AUTH-001)
   - Split-screen design (50/50)
   - Mobile number + password
   - Remember me option
   - Language selector

### Dashboard (1 screen)
2. ✅ **Dashboard** (ADMIN-DASH-001)
   - Statistics cards (4 cards: Users, Apartments, Pending, Bookings)
   - Quick actions section
   - Auto-refresh every 60 seconds

### Registration Management (2 screens)
3. ✅ **Pending Registrations List** (ADMIN-REG-001)
   - Data table with search, filter, sort
   - Quick approve/reject actions
   - Pagination

4. ✅ **Registration Detail** (ADMIN-REG-002)
   - Full user details with large photo previews
   - ID photo verification
   - Approve/Reject buttons with reason field

### User Management (1 screen)
5. ✅ **All Users** (ADMIN-USER-001)
   - Master-detail layout (40% list, 60% detail)
   - User list with filters
   - User detail panel with:
     - Personal information
     - Account balance (with deposit/withdraw)
     - Activity summary
     - Delete user (if no active bookings)

### Content Overview (2 screens)
6. ✅ **All Apartments** (ADMIN-CONTENT-001)
   - Data table with filters
   - Apartment detail panel (slide-out)
   - Read-only view

7. ✅ **All Bookings** (ADMIN-CONTENT-002)
   - Data table with status filters
   - Booking detail panel (slide-out)
   - Read-only view

### Settings (3 screens)
8. ✅ **Profile Settings** (ADMIN-SETTINGS-001)
   - Edit name and photo
   - View account info

9. ✅ **Language Settings** (ADMIN-SETTINGS-002)
   - Switch between English/Arabic
   - RTL/LTR layout switching

10. ✅ **Theme Settings** (ADMIN-SETTINGS-003)
    - Light/Dark mode toggle
    - Live preview

## Common Components to Build

### Layout Components
- **AppScaffold** - Main layout with sidebar and top bar
- **Sidebar** - Collapsible navigation menu
- **TopAppBar** - Header with notifications and profile
- **Breadcrumb** - Navigation breadcrumbs

### UI Components
- **StatCard** - Dashboard statistics card
- **DataTableWidget** - Reusable data table with pagination
- **AppButton** - Standardized button
- **AppTextField** - Standardized text field
- **AppDialog** - Standardized dialog
- **LoadingIndicator** - Loading states
- **ErrorWidget** - Error states
- **EmptyState** - Empty states

## API Endpoints Reference

### Authentication
- `POST /api/auth/login` - Admin login
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Get current user

### Profile
- `GET /api/profile` - Get profile
- `PUT /api/profile` - Update profile
- `POST /api/profile/upload-photo` - Upload photo
- `PUT /api/profile/language` - Update language

### Admin Endpoints
- `GET /api/admin/dashboard/statistics` - Dashboard stats
- `GET /api/admin/registrations/pending` - Pending registrations
- `GET /api/admin/registrations/{id}` - Registration detail
- `PUT /api/admin/registrations/{id}/approve` - Approve registration
- `PUT /api/admin/registrations/{id}/reject` - Reject registration
- `GET /api/admin/users` - List all users
- `GET /api/admin/users/{id}` - User detail
- `DELETE /api/admin/users/{id}` - Delete user
- `POST /api/admin/users/{id}/deposit` - Add money to tenant
- `POST /api/admin/users/{id}/withdraw` - Withdraw money from owner
- `GET /api/admin/users/{id}/transactions` - Transaction history
- `GET /api/admin/apartments` - List all apartments
- `GET /api/admin/bookings` - List all bookings

### Notifications
- `GET /api/notifications` - List notifications
- `PUT /api/notifications/{id}/read` - Mark as read
- `PUT /api/notifications/read-all` - Mark all as read

**Note**: For web, notifications will be in-app only (no FCM push). Use polling or WebSockets if available.

## Implementation Phases

### Phase 1: Core Infrastructure ✅
- [x] Project setup
- [x] DDD folder structure
- [x] Network layer (Dio + interceptors)
- [x] Theme configuration
- [x] Localization setup
- [x] Storage setup (GetStorage)

### Phase 2: Authentication
- [ ] Login page UI
- [ ] Auth controller
- [ ] Auth repository & use case
- [ ] Token management
- [ ] Auth guard middleware

### Phase 3: Layout & Navigation
- [ ] AppScaffold component
- [ ] Sidebar navigation
- [ ] Top app bar
- [ ] Breadcrumbs
- [ ] Route guards

### Phase 4: Dashboard
- [ ] Dashboard page
- [ ] Statistics cards
- [ ] Dashboard controller
- [ ] Statistics API integration

### Phase 5: Registration Management
- [ ] Pending registrations list
- [ ] Registration detail page
- [ ] Approve/Reject functionality
- [ ] Quick actions

### Phase 6: User Management
- [ ] All users page (master-detail)
- [ ] User detail panel
- [ ] Delete user
- [ ] Balance management (deposit/withdraw)
- [ ] Transaction history panel

### Phase 7: Content Overview
- [ ] All apartments page
- [ ] All bookings page
- [ ] Filters and search
- [ ] Detail panels

### Phase 8: Settings
- [ ] Profile page
- [ ] Language settings
- [ ] Theme settings

### Phase 9: Common Components
- [ ] Data tables
- [ ] Modals and dialogs
- [ ] Loading states
- [ ] Error handling
- [ ] Empty states

## Key Implementation Details

### 1. State Management with GetX

```dart
// Controller example
class MyController extends GetxController {
  final isLoading = false.obs;
  final data = Rxn<MyData>();
  
  @override
  void onInit() {
    super.onInit();
    loadData();
  }
  
  Future<void> loadData() async {
    isLoading.value = true;
    try {
      // API call
      data.value = await repository.getData();
    } finally {
      isLoading.value = false;
    }
  }
}

// Usage in UI
Obx(() => isLoading.value 
  ? CircularProgressIndicator()
  : Text(data.value?.name ?? '')
)
```

### 2. API Client Setup

```dart
// Use dependency injection
Get.put(ApiClient());
Get.put(AuthRepository(Get.find()));
Get.put(AuthController(Get.find()));
```

### 3. Error Handling

```dart
try {
  // API call
} catch (e) {
  if (e is ApiException) {
    Get.snackbar('Error', e.message);
  } else {
    Get.snackbar('Error', 'An unexpected error occurred');
  }
}
```

### 4. Responsive Check

```dart
LayoutBuilder(
  builder: (context, constraints) {
    if (constraints.maxWidth < 1024) {
      return _buildSmallScreenMessage(context);
    }
    return _buildNormalLayout(context);
  },
)
```

### 5. RTL Support

```dart
// In MaterialApp
locale: Get.locale ?? const Locale('en', 'US'),
supportedLocales: const [
  Locale('en', 'US'),
  Locale('ar', 'SA'),
],
```

## Testing Checklist

### Functionality
- [ ] Login/logout works
- [ ] All screens load correctly
- [ ] API calls succeed
- [ ] Error handling works
- [ ] Loading states display
- [ ] Empty states display

### UI/UX
- [ ] Responsive on 1024px+ width
- [ ] Shows message for < 1024px
- [ ] Light/Dark theme works
- [ ] English/Arabic switching works
- [ ] RTL layout correct for Arabic
- [ ] All navigation works

### Performance
- [ ] Page load < 2 seconds
- [ ] Smooth transitions
- [ ] No memory leaks
- [ ] Efficient API calls

## Deployment Notes

1. **Build for Web**: `flutter build web`
2. **Output**: `build/web/` directory
3. **Serve**: Use any web server (nginx, Apache, etc.)
4. **Environment**: Update API base URL for production

## Important Notes

1. **Incomplete APIs**: Some endpoints may not be ready. Mark with `// TODO: API not ready` and implement mock data for development.

2. **Notifications**: Web doesn't use FCM. Implement in-app notifications using API polling or WebSockets.

3. **File Uploads**: Use `image_picker_web` for photo uploads in web.

4. **Localization**: All text should be localized. Use GetX localization or `flutter_localizations`.

5. **State Persistence**: Use GetStorage for:
   - Auth token
   - User data
   - Language preference
   - Theme preference

6. **Security**: 
   - Never store sensitive data in localStorage
   - Validate all inputs
   - Handle token expiration
   - Implement proper error messages

## Getting Started

1. Read `docs/flutter-admin-web-implementation-guide.md` for complete project structure
2. Read `docs/flutter-admin-screens-implementation.md` for screen implementations
3. Follow the implementation phases in order
4. Test each phase before moving to the next
5. Refer to `docs/admin-web-screens.md` for detailed screen specifications

## Support Files

All necessary documentation is in the `docs/` folder:
- Screen specifications
- API endpoints
- Database schema
- Implementation guides

Start with Phase 1 (Core Infrastructure) and work through each phase systematically.

