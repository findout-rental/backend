# Flutter Admin Web - Screen-by-Screen Implementation Guide

This document provides detailed implementation steps for each screen in the admin web panel, following the specifications in `admin-web-screens.md`.

## Screen Implementation Order

1. **Login Screen** (ADMIN-AUTH-001)
2. **Dashboard** (ADMIN-DASH-001)
3. **Pending Registrations List** (ADMIN-REG-001)
4. **Registration Detail** (ADMIN-REG-002)
5. **All Users** (ADMIN-USER-001)
6. **All Apartments** (ADMIN-CONTENT-001)
7. **All Bookings** (ADMIN-CONTENT-002)
8. **Profile Settings** (ADMIN-SETTINGS-001)
9. **Language Settings** (ADMIN-SETTINGS-002)
10. **Theme Settings** (ADMIN-SETTINGS-003)

---

## Screen 1: Login Screen (ADMIN-AUTH-001)

### Implementation Steps

#### Step 1.1: Create Login Controller

**File: `lib/presentation/controllers/auth/login_controller.dart`**

```dart
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../../core/constants/storage_keys.dart';
import '../../../core/utils/validators.dart';
import '../../../domain/repositories/auth_repository.dart';
import '../../auth/auth_controller.dart';

class LoginController extends GetxController {
  final AuthRepository authRepository;
  final AuthController authController = Get.find();
  final GetStorage storage = GetStorage();

  // Form controllers
  final mobileNumberController = TextEditingController();
  final passwordController = TextEditingController();
  
  // Form key
  final formKey = GlobalKey<FormState>();
  
  // State
  final isPasswordVisible = false.obs;
  final rememberMe = false.obs;
  final isLoading = false.obs;
  String? errorMessage;

  LoginController() : authRepository = Get.find() {
    _loadSavedCredentials();
  }

  void _loadSavedCredentials() {
    final savedMobile = storage.read<String>('saved_mobile_number');
    final savedRememberMe = storage.read<bool>(StorageKeys.rememberMe) ?? false;
    
    if (savedMobile != null) {
      mobileNumberController.text = savedMobile;
      rememberMe.value = savedRememberMe;
    }
  }

  void togglePasswordVisibility() {
    isPasswordVisible.value = !isPasswordVisible.value;
  }

  void toggleRememberMe(bool? value) {
    rememberMe.value = value ?? false;
  }

  Future<void> login() async {
    if (!formKey.currentState!.validate()) {
      return;
    }

    errorMessage = null;
    isLoading.value = true;
    update();

    try {
      final mobileNumber = mobileNumberController.text.trim();
      final password = passwordController.text;

      final success = await authController.login(mobileNumber, password);

      if (success) {
        // Save credentials if remember me is checked
        if (rememberMe.value) {
          storage.write('saved_mobile_number', mobileNumber);
          storage.write(StorageKeys.rememberMe, true);
        } else {
          storage.remove('saved_mobile_number');
          storage.remove(StorageKeys.rememberMe);
        }

        Get.offAllNamed('/dashboard');
      } else {
        errorMessage = 'Invalid mobile number or password. Please try again.';
        update();
      }
    } catch (e) {
      errorMessage = _getErrorMessage(e);
      update();
    } finally {
      isLoading.value = false;
      update();
    }
  }

  String _getErrorMessage(dynamic error) {
    if (error.toString().contains('network') || error.toString().contains('connection')) {
      return 'Unable to connect. Please check your internet connection.';
    }
    if (error.toString().contains('401') || error.toString().contains('unauthorized')) {
      return 'Invalid mobile number or password. Please try again.';
    }
    return 'Something went wrong. Please try again later.';
  }

  @override
  void onClose() {
    mobileNumberController.dispose();
    passwordController.dispose();
    super.onClose();
  }
}
```

#### Step 1.2: Create Login Page UI

**File: `lib/presentation/pages/auth/login_page.dart`**

```dart
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../controllers/auth/login_controller.dart';
import '../../../core/utils/validators.dart';

class LoginPage extends StatelessWidget {
  const LoginPage({super.key});

  @override
  Widget build(BuildContext context) {
    return GetBuilder<LoginController>(
      init: LoginController(),
      builder: (controller) {
        return Scaffold(
          body: LayoutBuilder(
            builder: (context, constraints) {
              // Show message if screen is too small
              if (constraints.maxWidth < 1024) {
                return _buildSmallScreenMessage(context);
              }

              return Row(
                children: [
                  // Left Panel - Branding (50%)
                  Expanded(
                    child: _buildBrandingPanel(context),
                  ),
                  // Right Panel - Login Form (50%)
                  Expanded(
                    child: _buildLoginForm(context, controller),
                  ),
                ],
              );
            },
          ),
        );
      },
    );
  }

  Widget _buildSmallScreenMessage(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.desktop_windows, size: 64, color: Colors.grey[400]),
            const SizedBox(height: 24),
            Text(
              'Desktop Required',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 16),
            Text(
              'This admin panel requires a desktop browser.',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.bodyLarge,
            ),
            const SizedBox(height: 8),
            Text(
              'Please access from a computer with minimum screen width of 1024px.',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Colors.grey[600],
                  ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBrandingPanel(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            Theme.of(context).colorScheme.primary,
            Theme.of(context).colorScheme.primary.withOpacity(0.8),
          ],
        ),
      ),
      child: Center(
        child: Padding(
          padding: const EdgeInsets.all(48.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Logo
              Icon(
                Icons.apartment,
                size: 120,
                color: Colors.white,
              ),
              const SizedBox(height: 32),
              // Headline
              Text(
                'FindOut Admin',
                style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 16),
              // Subheadline
              Text(
                'Manage your apartment rental platform',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      color: Colors.white70,
                    ),
                textAlign: TextAlign.center,
              ),
              const Spacer(),
              // Version
              Text(
                'Version 1.0.0',
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: Colors.white60,
                    ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLoginForm(BuildContext context, LoginController controller) {
    return Container(
      padding: const EdgeInsets.all(48.0),
      child: Center(
        child: SingleChildScrollView(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 400),
            child: Form(
              key: controller.formKey,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Header
                  Text(
                    'Welcome Back',
                    style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Sign in to your admin account',
                    style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                          color: Colors.grey[600],
                        ),
                  ),
                  const SizedBox(height: 48),
                  
                  // Error Message
                  if (controller.errorMessage != null) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.red[50],
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.red[200]!),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.error_outline, color: Colors.red[700], size: 20),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              controller.errorMessage!,
                              style: TextStyle(color: Colors.red[700], fontSize: 14),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                  ],
                  
                  // Mobile Number Field
                  TextFormField(
                    controller: controller.mobileNumberController,
                    keyboardType: TextInputType.phone,
                    textInputAction: TextInputAction.next,
                    autofocus: true,
                    decoration: const InputDecoration(
                      labelText: 'Mobile Number',
                      hintText: '+201234567890',
                      prefixIcon: Icon(Icons.phone),
                      border: OutlineInputBorder(),
                    ),
                    validator: Validators.mobileNumber,
                  ),
                  const SizedBox(height: 24),
                  
                  // Password Field
                  Obx(() => TextFormField(
                    controller: controller.passwordController,
                    obscureText: !controller.isPasswordVisible.value,
                    textInputAction: TextInputAction.done,
                    onFieldSubmitted: (_) => controller.login(),
                    decoration: InputDecoration(
                      labelText: 'Password',
                      hintText: 'Enter your password',
                      prefixIcon: const Icon(Icons.lock),
                      suffixIcon: IconButton(
                        icon: Icon(
                          controller.isPasswordVisible.value
                              ? Icons.visibility
                              : Icons.visibility_off,
                        ),
                        onPressed: controller.togglePasswordVisibility,
                      ),
                      border: const OutlineInputBorder(),
                    ),
                    validator: Validators.required,
                  )),
                  const SizedBox(height: 16),
                  
                  // Remember Me & Forgot Password
                  Row(
                    children: [
                      Obx(() => Checkbox(
                        value: controller.rememberMe.value,
                        onChanged: controller.toggleRememberMe,
                      )),
                      const Text('Keep me signed in'),
                      const Spacer(),
                      TextButton(
                        onPressed: () {
                          // TODO: Navigate to forgot password (future feature)
                          Get.snackbar(
                            'Info',
                            'Forgot password feature coming soon',
                            snackPosition: SnackPosition.BOTTOM,
                          );
                        },
                        child: const Text('Forgot Password?'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 32),
                  
                  // Login Button
                  Obx(() => ElevatedButton(
                    onPressed: controller.isLoading.value ? null : controller.login,
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      minimumSize: const Size(double.infinity, 50),
                    ),
                    child: controller.isLoading.value
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          )
                        : const Text(
                            'Sign In',
                            style: TextStyle(fontSize: 16),
                          ),
                  )),
                  const SizedBox(height: 32),
                  
                  // Language Selector
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      TextButton(
                        onPressed: () {
                          // TODO: Change language to English
                        },
                        child: const Text('English'),
                      ),
                      const Text('|'),
                      TextButton(
                        onPressed: () {
                          // TODO: Change language to Arabic
                        },
                        child: const Text('العربية'),
                      ),
                    ],
                  ),
                  
                  // Footer Links
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      TextButton(
                        onPressed: () {
                          // TODO: Open privacy policy
                        },
                        child: const Text('Privacy Policy'),
                      ),
                      const Text('|'),
                      TextButton(
                        onPressed: () {
                          // TODO: Open terms of service
                        },
                        child: const Text('Terms of Service'),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
```

#### Step 1.3: Add Validators

**File: `lib/core/utils/validators.dart`**

```dart
class Validators {
  static String? required(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'This field is required';
    }
    return null;
  }

  static String? mobileNumber(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Mobile number is required';
    }
    // Basic mobile number validation (starts with + and has digits)
    final regex = RegExp(r'^\+?[1-9]\d{1,14}$');
    if (!regex.hasMatch(value.replaceAll(' ', ''))) {
      return 'Please enter a valid mobile number';
    }
    return null;
  }

  static String? email(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Email is required';
    }
    final regex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
    if (!regex.hasMatch(value)) {
      return 'Please enter a valid email address';
    }
    return null;
  }

  static String? minLength(String? value, int minLength) {
    if (value == null || value.length < minLength) {
      return 'Must be at least $minLength characters';
    }
    return null;
  }

  static String? maxLength(String? value, int maxLength) {
    if (value != null && value.length > maxLength) {
      return 'Must be at most $maxLength characters';
    }
    return null;
  }
}
```

---

## Screen 2: Dashboard (ADMIN-DASH-001)

### Implementation Steps

#### Step 2.1: Create Dashboard Controller

**File: `lib/presentation/controllers/dashboard/dashboard_controller.dart`**

```dart
import 'package:get/get.dart';
import '../../../domain/usecases/dashboard/get_statistics_usecase.dart';

class DashboardController extends GetxController {
  final GetStatisticsUsecase getStatisticsUsecase;

  DashboardController(this.getStatisticsUsecase);

  // Statistics
  final isLoading = true.obs;
  final statistics = Rxn<DashboardStatistics>();

  // Statistics data
  final totalUsers = 0.obs;
  final totalTenants = 0.obs;
  final totalOwners = 0.obs;
  final totalApartments = 0.obs;
  final activeApartments = 0.obs;
  final inactiveApartments = 0.obs;
  final pendingRegistrations = 0.obs;
  final totalBookings = 0.obs;
  final activeBookings = 0.obs;
  final completedBookings = 0.obs;

  @override
  void onInit() {
    super.onInit();
    loadStatistics();
    // Auto-refresh every 60 seconds
    // Timer.periodic(const Duration(seconds: 60), (_) => loadStatistics());
  }

  Future<void> loadStatistics() async {
    isLoading.value = true;
    try {
      final stats = await getStatisticsUsecase.execute();
      statistics.value = stats;
      _updateObservableValues(stats);
    } catch (e) {
      Get.snackbar(
        'Error',
        'Unable to load dashboard data. Please try again.',
        snackPosition: SnackPosition.BOTTOM,
      );
    } finally {
      isLoading.value = false;
    }
  }

  void _updateObservableValues(DashboardStatistics stats) {
    totalUsers.value = stats.users.total;
    totalTenants.value = stats.users.tenants;
    totalOwners.value = stats.users.owners;
    totalApartments.value = stats.apartments.total;
    activeApartments.value = stats.apartments.active;
    inactiveApartments.value = stats.apartments.inactive;
    pendingRegistrations.value = stats.users.pending;
    totalBookings.value = stats.bookings.total;
    activeBookings.value = stats.bookings.active;
    completedBookings.value = stats.bookings.completed;
  }

  void refreshStatistics() {
    loadStatistics();
  }
}

// Statistics model
class DashboardStatistics {
  final UserStatistics users;
  final ApartmentStatistics apartments;
  final BookingStatistics bookings;
  final DateTime lastUpdated;

  DashboardStatistics({
    required this.users,
    required this.apartments,
    required this.bookings,
    required this.lastUpdated,
  });
}

class UserStatistics {
  final int total;
  final int tenants;
  final int owners;
  final int pending;

  UserStatistics({
    required this.total,
    required this.tenants,
    required this.owners,
    required this.pending,
  });
}

class ApartmentStatistics {
  final int total;
  final int active;
  final int inactive;

  ApartmentStatistics({
    required this.total,
    required this.active,
    required this.inactive,
  });
}

class BookingStatistics {
  final int total;
  final int active;
  final int completed;

  BookingStatistics({
    required this.total,
    required this.active,
    required this.completed,
  });
}
```

#### Step 2.2: Create Dashboard Page

**File: `lib/presentation/pages/dashboard/dashboard_page.dart`**

```dart
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:intl/intl.dart';
import '../../controllers/dashboard/dashboard_controller.dart';
import '../../widgets/layout/app_scaffold.dart';
import '../../widgets/cards/stat_card.dart';

class DashboardPage extends StatelessWidget {
  const DashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return AppScaffold(
      title: 'Dashboard',
      child: GetBuilder<DashboardController>(
        init: DashboardController(Get.find()),
        builder: (controller) {
          return RefreshIndicator(
            onRefresh: controller.loadStatistics,
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Page Header
                  _buildPageHeader(context, controller),
                  const SizedBox(height: 32),
                  
                  // Statistics Cards Grid
                  Obx(() {
                    if (controller.isLoading.value) {
                      return _buildLoadingState();
                    }
                    return _buildStatisticsGrid(context, controller);
                  }),
                  
                  const SizedBox(height: 32),
                  
                  // Quick Actions
                  _buildQuickActions(context),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildPageHeader(BuildContext context, DashboardController controller) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Dashboard',
          style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                fontWeight: FontWeight.bold,
              ),
        ),
        const SizedBox(height: 8),
        Text(
          'Overview of your platform',
          style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                color: Colors.grey[600],
              ),
        ),
        Obx(() {
          if (controller.statistics.value != null) {
            return Text(
              'Last updated: ${DateFormat('MMM dd, yyyy - HH:mm').format(controller.statistics.value!.lastUpdated)}',
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Colors.grey[500],
                  ),
            );
          }
          return const SizedBox.shrink();
        }),
      ],
    );
  }

  Widget _buildLoadingState() {
    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      crossAxisSpacing: 16,
      mainAxisSpacing: 16,
      childAspectRatio: 1.5,
      children: List.generate(4, (index) {
        return Card(
          child: Center(
            child: CircularProgressIndicator(),
          ),
        );
      }),
    );
  }

  Widget _buildStatisticsGrid(BuildContext context, DashboardController controller) {
    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      crossAxisSpacing: 16,
      mainAxisSpacing: 16,
      childAspectRatio: 1.5,
      children: [
        // Total Users Card
        StatCard(
          icon: Icons.people,
          title: 'Total Users',
          value: controller.totalUsers.value.toString(),
          subtitle: '${controller.totalTenants.value} Tenants | ${controller.totalOwners.value} Owners',
          onTap: () => Get.toNamed('/users'),
        ),
        
        // Total Apartments Card
        StatCard(
          icon: Icons.apartment,
          title: 'Total Apartments',
          value: controller.totalApartments.value.toString(),
          subtitle: '${controller.activeApartments.value} Active | ${controller.inactiveApartments.value} Inactive',
          onTap: () => Get.toNamed('/apartments'),
        ),
        
        // Pending Registrations Card
        StatCard(
          icon: Icons.pending_actions,
          title: 'Pending Registrations',
          value: controller.pendingRegistrations.value.toString(),
          subtitle: controller.pendingRegistrations.value > 0
              ? 'Requires Action'
              : 'All reviewed',
          badge: controller.pendingRegistrations.value > 0
              ? controller.pendingRegistrations.value
              : null,
          badgeColor: Colors.orange,
          onTap: () => Get.toNamed('/pending-registrations'),
          actionLabel: controller.pendingRegistrations.value > 0
              ? 'Review Now →'
              : null,
        ),
        
        // Total Bookings Card
        StatCard(
          icon: Icons.calendar_today,
          title: 'Total Bookings',
          value: controller.totalBookings.value.toString(),
          subtitle: '${controller.activeBookings.value} Active | ${controller.completedBookings.value} Completed',
          onTap: () => Get.toNamed('/bookings'),
        ),
      ],
    );
  }

  Widget _buildQuickActions(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Quick Actions',
          style: Theme.of(context).textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.bold,
              ),
        ),
        const SizedBox(height: 16),
        Wrap(
          spacing: 16,
          runSpacing: 16,
          children: [
            _buildActionButton(
              context,
              'Review Pending Registrations',
              Icons.pending_actions,
              Colors.orange,
              () => Get.toNamed('/pending-registrations'),
            ),
            _buildActionButton(
              context,
              'View All Users',
              Icons.people,
              Colors.blue,
              () => Get.toNamed('/users'),
            ),
            _buildActionButton(
              context,
              'View All Apartments',
              Icons.apartment,
              Colors.green,
              () => Get.toNamed('/apartments'),
            ),
            _buildActionButton(
              context,
              'View All Bookings',
              Icons.calendar_today,
              Colors.purple,
              () => Get.toNamed('/bookings'),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildActionButton(
    BuildContext context,
    String label,
    IconData icon,
    Color color,
    VoidCallback onTap,
  ) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withOpacity(0.3)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: color, size: 24),
            const SizedBox(width: 12),
            Text(
              label,
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
```

#### Step 2.3: Create Stat Card Widget

**File: `lib/presentation/widgets/cards/stat_card.dart`**

```dart
import 'package:flutter/material.dart';

class StatCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String value;
  final String? subtitle;
  final int? badge;
  final Color? badgeColor;
  final String? actionLabel;
  final VoidCallback? onTap;

  const StatCard({
    super.key,
    required this.icon,
    required this.title,
    required this.value,
    this.subtitle,
    this.badge,
    this.badgeColor,
    this.actionLabel,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Icon(icon, size: 32, color: Theme.of(context).colorScheme.primary),
                  if (badge != null && badge! > 0)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: badgeColor ?? Colors.red,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        badge.toString(),
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                ],
              ),
              const Spacer(),
              Text(
                value,
                style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 4),
              Text(
                title,
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w500,
                    ),
              ),
              if (subtitle != null) ...[
                const SizedBox(height: 8),
                Text(
                  subtitle!,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Colors.grey[600],
                      ),
                ),
              ],
              if (actionLabel != null) ...[
                const SizedBox(height: 12),
                Text(
                  actionLabel!,
                  style: TextStyle(
                    color: Theme.of(context).colorScheme.primary,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
```

---

## Common Widgets to Create

### App Scaffold (Layout with Sidebar)

**File: `lib/presentation/widgets/layout/app_scaffold.dart`**

This widget provides the main layout with sidebar navigation and top app bar. Implementation should include:
- Sidebar with navigation items
- Top app bar with notifications and profile
- Responsive layout
- Breadcrumbs

### Data Table Widget

**File: `lib/presentation/widgets/tables/data_table_widget.dart`**

Reusable data table with:
- Sortable columns
- Pagination
- Row selection
- Loading states
- Empty states

---

## Implementation Notes

1. **API Integration**: Mark endpoints that aren't ready with `// TODO: API endpoint not ready`

2. **Error Handling**: All controllers should handle errors gracefully and show user-friendly messages

3. **Loading States**: Always show loading indicators during async operations

4. **Responsive Design**: Ensure all screens work on 1024px+ width

5. **RTL Support**: Test with Arabic language to ensure proper RTL layout

6. **State Management**: Use GetX reactive programming (`Obx`, `.obs`, etc.)

7. **Navigation**: Use GetX navigation (`Get.toNamed()`, `Get.offAllNamed()`, etc.)

---

## Next Steps

Continue implementing remaining screens following the same pattern:
1. Create controller
2. Create page/widget
3. Add to routes
4. Test functionality

Each screen should follow the specifications in `admin-web-screens.md`.

