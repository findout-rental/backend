# Flutter Admin Web Panel - Implementation Guide

## Project Setup Instructions

### 1. Create Flutter Web Project

```bash
# Create new Flutter project for web
flutter create --platforms=web admin_web
cd admin_web

# Update pubspec.yaml with package name
# Package name: com.findout.adminweb
```

### 2. Update pubspec.yaml

```yaml
name: admin_web
description: FindOut Admin Web Panel - System Administration Dashboard
publish_to: 'none'
version: 1.0.0+1

environment:
  sdk: '>=3.0.0 <4.0.0'

dependencies:
  flutter:
    sdk: flutter
  
  # State Management
  get: ^4.6.6
  
  # HTTP Client
  dio: ^5.4.0
  pretty_dio_logger: ^1.3.1
  
  # Local Storage
  get_storage: ^2.1.1
  
  # Image Handling
  cached_network_image: ^3.3.0
  image_picker_web: ^2.1.0
  
  # UI Components
  flutter_svg: ^2.0.9
  intl: ^0.19.0
  flutter_localizations:
    sdk: flutter
  
  # Utils
  equatable: ^2.0.5
  uuid: ^4.2.1

dev_dependencies:
  flutter_test:
    sdk: flutter
  flutter_lints: ^3.0.0

flutter:
  uses-material-design: true
  
  # Assets
  assets:
    - assets/images/
    - assets/icons/
    - assets/translations/
  
  # Fonts (if custom fonts needed)
  # fonts:
  #   - family: CustomFont
  #     fonts:
  #       - asset: fonts/CustomFont-Regular.ttf
```

### 3. Project Structure (DDD - Domain Driven Design)

```
admin_web/
├── lib/
│   ├── main.dart
│   ├── app/
│   │   ├── app.dart                    # Main app widget
│   │   ├── routes/
│   │   │   └── app_pages.dart          # GetX route definitions
│   │   └── bindings/
│   │       └── initial_binding.dart    # Initial bindings
│   │
│   ├── core/
│   │   ├── constants/
│   │   │   ├── api_constants.dart      # API endpoints
│   │   │   ├── app_constants.dart     # App-wide constants
│   │   │   └── storage_keys.dart       # Storage key constants
│   │   ├── theme/
│   │   │   ├── app_theme.dart         # Theme configuration
│   │   │   ├── app_colors.dart        # Color definitions
│   │   │   └── app_text_styles.dart   # Text styles
│   │   ├── utils/
│   │   │   ├── validators.dart        # Form validators
│   │   │   ├── formatters.dart        # Text formatters
│   │   │   ├── date_utils.dart        # Date utilities
│   │   │   └── responsive.dart        # Responsive utilities
│   │   ├── network/
│   │   │   ├── api_client.dart        # Dio client setup
│   │   │   ├── api_interceptor.dart   # Request/Response interceptors
│   │   │   └── api_exception.dart     # API exception handling
│   │   └── storage/
│   │       └── local_storage.dart     # GetStorage wrapper
│   │
│   ├── domain/
│   │   ├── entities/                  # Domain entities
│   │   │   ├── user.dart
│   │   │   ├── apartment.dart
│   │   │   ├── booking.dart
│   │   │   ├── notification.dart
│   │   │   └── transaction.dart
│   │   ├── repositories/              # Repository interfaces
│   │   │   ├── auth_repository.dart
│   │   │   ├── user_repository.dart
│   │   │   ├── apartment_repository.dart
│   │   │   ├── booking_repository.dart
│   │   │   └── notification_repository.dart
│   │   └── usecases/                  # Business logic
│   │       ├── auth/
│   │       │   ├── login_usecase.dart
│   │       │   └── logout_usecase.dart
│   │       ├── user/
│   │       │   ├── get_users_usecase.dart
│   │       │   ├── approve_user_usecase.dart
│   │       │   ├── reject_user_usecase.dart
│   │       │   └── delete_user_usecase.dart
│   │       └── dashboard/
│   │           └── get_statistics_usecase.dart
│   │
│   ├── data/
│   │   ├── models/                    # Data models (JSON serialization)
│   │   │   ├── user_model.dart
│   │   │   ├── apartment_model.dart
│   │   │   ├── booking_model.dart
│   │   │   ├── notification_model.dart
│   │   │   └── api_response_model.dart
│   │   ├── repositories/              # Repository implementations
│   │   │   ├── auth_repository_impl.dart
│   │   │   ├── user_repository_impl.dart
│   │   │   ├── apartment_repository_impl.dart
│   │   │   ├── booking_repository_impl.dart
│   │   │   └── notification_repository_impl.dart
│   │   └── datasources/
│   │       ├── remote/                # API data sources
│   │       │   ├── auth_remote_datasource.dart
│   │       │   ├── user_remote_datasource.dart
│   │       │   ├── apartment_remote_datasource.dart
│   │       │   ├── booking_remote_datasource.dart
│   │       │   └── notification_remote_datasource.dart
│   │       └── local/                 # Local storage data sources
│   │           └── auth_local_datasource.dart
│   │
│   ├── presentation/
│   │   ├── controllers/               # GetX controllers
│   │   │   ├── auth/
│   │   │   │   ├── login_controller.dart
│   │   │   │   └── auth_controller.dart
│   │   │   ├── dashboard/
│   │   │   │   └── dashboard_controller.dart
│   │   │   ├── registration/
│   │   │   │   ├── pending_registrations_controller.dart
│   │   │   │   └── registration_detail_controller.dart
│   │   │   ├── user/
│   │   │   │   ├── all_users_controller.dart
│   │   │   │   └── user_detail_controller.dart
│   │   │   ├── apartment/
│   │   │   │   └── all_apartments_controller.dart
│   │   │   ├── booking/
│   │   │   │   └── all_bookings_controller.dart
│   │   │   ├── notification/
│   │   │   │   └── notification_controller.dart
│   │   │   └── settings/
│   │   │       ├── profile_controller.dart
│   │   │       ├── language_controller.dart
│   │   │       └── theme_controller.dart
│   │   │
│   │   ├── widgets/                  # Reusable widgets
│   │   │   ├── common/
│   │   │   │   ├── app_button.dart
│   │   │   │   ├── app_text_field.dart
│   │   │   │   ├── app_dialog.dart
│   │   │   │   ├── loading_indicator.dart
│   │   │   │   ├── error_widget.dart
│   │   │   │   └── empty_state.dart
│   │   │   ├── layout/
│   │   │   │   ├── app_scaffold.dart
│   │   │   │   ├── sidebar.dart
│   │   │   │   ├── top_app_bar.dart
│   │   │   │   └── breadcrumb.dart
│   │   │   ├── tables/
│   │   │   │   ├── data_table_widget.dart
│   │   │   │   └── pagination_widget.dart
│   │   │   └── cards/
│   │   │       ├── stat_card.dart
│   │   │       └── user_card.dart
│   │   │
│   │   └── pages/                    # UI pages/screens
│   │       ├── auth/
│   │       │   └── login_page.dart
│   │       ├── dashboard/
│   │       │   └── dashboard_page.dart
│   │       ├── registration/
│   │       │   ├── pending_registrations_page.dart
│   │       │   └── registration_detail_page.dart
│   │       ├── user/
│   │       │   ├── all_users_page.dart
│   │       │   └── user_detail_panel.dart
│   │       ├── apartment/
│   │       │   └── all_apartments_page.dart
│   │       ├── booking/
│   │       │   └── all_bookings_page.dart
│   │       └── settings/
│   │           ├── profile_page.dart
│   │           ├── language_page.dart
│   │           └── theme_page.dart
│   │
│   └── localization/
│       ├── app_localizations.dart
│       ├── en/
│       │   └── translations.dart
│       └── ar/
│           └── translations.dart
│
├── assets/
│   ├── images/
│   ├── icons/
│   └── translations/
│
└── test/
    └── (test files)
```

## Implementation Steps

### Phase 1: Core Setup & Infrastructure

#### Step 1.1: Core Constants & Configuration

**File: `lib/core/constants/api_constants.dart`**
```dart
class ApiConstants {
  // Base URL - Update based on environment
  static const String baseUrl = 'http://localhost:8000/api';
  
  // Auth endpoints
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String me = '/auth/me';
  
  // Profile endpoints
  static const String profile = '/profile';
  static const String updateProfile = '/profile';
  static const String uploadPhoto = '/profile/upload-photo';
  static const String updateLanguage = '/profile/language';
  
  // Admin endpoints
  static const String dashboardStats = '/admin/dashboard/statistics';
  static const String pendingRegistrations = '/admin/registrations/pending';
  static const String registrationDetail = '/admin/registrations';
  static const String approveRegistration = '/admin/registrations';
  static const String rejectRegistration = '/admin/registrations';
  static const String allUsers = '/admin/users';
  static const String userDetail = '/admin/users';
  static const String deleteUser = '/admin/users';
  static const String userDeposit = '/admin/users';
  static const String userWithdraw = '/admin/users';
  static const String userTransactions = '/admin/users';
  static const String allApartments = '/admin/apartments';
  static const String allBookings = '/admin/bookings';
  
  // Notifications
  static const String notifications = '/notifications';
  static const String markNotificationRead = '/notifications';
  static const String markAllRead = '/notifications/read-all';
}
```

**File: `lib/core/constants/storage_keys.dart`**
```dart
class StorageKeys {
  static const String token = 'auth_token';
  static const String user = 'user_data';
  static const String language = 'language_preference';
  static const String theme = 'theme_mode';
  static const String rememberMe = 'remember_me';
}
```

#### Step 1.2: Network Layer Setup

**File: `lib/core/network/api_client.dart`**
```dart
import 'package:dio/dio.dart';
import 'package:get_storage/get_storage.dart';
import 'package:pretty_dio_logger/pretty_dio_logger.dart';
import '../constants/api_constants.dart';
import '../constants/storage_keys.dart';
import 'api_interceptor.dart';
import 'api_exception.dart';

class ApiClient {
  late Dio _dio;
  final GetStorage _storage = GetStorage();

  ApiClient() {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiConstants.baseUrl,
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ),
    );

    // Add interceptors
    _dio.interceptors.add(ApiInterceptor(_storage));
    
    // Add logger in debug mode
    if (kDebugMode) {
      _dio.interceptors.add(PrettyDioLogger(
        requestHeader: true,
        requestBody: true,
        responseBody: true,
        responseHeader: false,
        error: true,
        compact: true,
      ));
    }
  }

  Dio get dio => _dio;

  // Helper methods
  Future<Response> get(String path, {Map<String, dynamic>? queryParameters}) async {
    try {
      return await _dio.get(path, queryParameters: queryParameters);
    } catch (e) {
      throw ApiException.fromDioError(e);
    }
  }

  Future<Response> post(String path, {dynamic data, Map<String, dynamic>? queryParameters}) async {
    try {
      return await _dio.post(path, data: data, queryParameters: queryParameters);
    } catch (e) {
      throw ApiException.fromDioError(e);
    }
  }

  Future<Response> put(String path, {dynamic data, Map<String, dynamic>? queryParameters}) async {
    try {
      return await _dio.put(path, data: data, queryParameters: queryParameters);
    } catch (e) {
      throw ApiException.fromDioError(e);
    }
  }

  Future<Response> delete(String path, {Map<String, dynamic>? queryParameters}) async {
    try {
      return await _dio.delete(path, queryParameters: queryParameters);
    } catch (e) {
      throw ApiException.fromDioError(e);
    }
  }

  Future<Response> postFormData(String path, FormData formData) async {
    try {
      return await _dio.post(
        path,
        data: formData,
        options: Options(
          contentType: 'multipart/form-data',
        ),
      );
    } catch (e) {
      throw ApiException.fromDioError(e);
    }
  }
}
```

**File: `lib/core/network/api_interceptor.dart`**
```dart
import 'package:dio/dio.dart';
import 'package:get_storage/get_storage.dart';
import '../constants/storage_keys.dart';

class ApiInterceptor extends Interceptor {
  final GetStorage storage;

  ApiInterceptor(this.storage);

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    // Add auth token if available
    final token = storage.read<String>(StorageKeys.token);
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    super.onRequest(options, handler);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    // Handle 401 Unauthorized - logout user
    if (err.response?.statusCode == 401) {
      storage.remove(StorageKeys.token);
      storage.remove(StorageKeys.user);
      // Navigate to login (will be handled by auth guard)
    }
    super.onError(err, handler);
  }
}
```

**File: `lib/core/network/api_exception.dart`**
```dart
import 'package:dio/dio.dart';

class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final dynamic data;

  ApiException({
    required this.message,
    this.statusCode,
    this.data,
  });

  factory ApiException.fromDioError(dynamic error) {
    if (error is DioException) {
      switch (error.type) {
        case DioExceptionType.connectionTimeout:
        case DioExceptionType.sendTimeout:
        case DioExceptionType.receiveTimeout:
          return ApiException(
            message: 'Connection timeout. Please check your internet connection.',
            statusCode: error.response?.statusCode,
          );
        case DioExceptionType.badResponse:
          final statusCode = error.response?.statusCode;
          final data = error.response?.data;
          String message = 'An error occurred';
          
          if (data is Map && data.containsKey('message')) {
            message = data['message'] ?? message;
          } else if (data is Map && data.containsKey('error')) {
            message = data['error'] ?? message;
          }
          
          return ApiException(
            message: message,
            statusCode: statusCode,
            data: data,
          );
        case DioExceptionType.cancel:
          return ApiException(message: 'Request cancelled');
        case DioExceptionType.unknown:
        default:
          return ApiException(
            message: error.message ?? 'An unexpected error occurred',
            statusCode: error.response?.statusCode,
          );
      }
    }
    return ApiException(message: error.toString());
  }

  @override
  String toString() => message;
}
```

#### Step 1.3: Theme & Localization Setup

**File: `lib/core/theme/app_theme.dart`**
```dart
import 'package:flutter/material.dart';
import 'package:get_storage/get_storage.dart';
import '../constants/storage_keys.dart';
import 'app_colors.dart';

class AppTheme {
  static final GetStorage _storage = GetStorage();
  
  static ThemeMode get themeMode {
    final theme = _storage.read<String>(StorageKeys.theme);
    switch (theme) {
      case 'dark':
        return ThemeMode.dark;
      case 'light':
        return ThemeMode.light;
      default:
        return ThemeMode.light;
    }
  }

  static ThemeData lightTheme = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(
      seedColor: AppColors.primary,
      brightness: Brightness.light,
    ),
    scaffoldBackgroundColor: AppColors.backgroundLight,
    appBarTheme: const AppBarTheme(
      elevation: 0,
      centerTitle: false,
      backgroundColor: AppColors.primary,
      foregroundColor: Colors.white,
    ),
    cardTheme: CardTheme(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
    ),
  );

  static ThemeData darkTheme = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(
      seedColor: AppColors.primary,
      brightness: Brightness.dark,
    ),
    scaffoldBackgroundColor: AppColors.backgroundDark,
    appBarTheme: const AppBarTheme(
      elevation: 0,
      centerTitle: false,
      backgroundColor: AppColors.surfaceDark,
      foregroundColor: Colors.white,
    ),
    cardTheme: CardTheme(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
    ),
  );
}
```

**File: `lib/core/theme/app_colors.dart`**
```dart
import 'package:flutter/material.dart';

class AppColors {
  // Primary colors
  static const Color primary = Color(0xFF2196F3);
  static const Color primaryDark = Color(0xFF1976D2);
  static const Color primaryLight = Color(0xFF64B5F6);
  
  // Status colors
  static const Color success = Color(0xFF4CAF50);
  static const Color warning = Color(0xFFFF9800);
  static const Color error = Color(0xFFF44336);
  static const Color info = Color(0xFF2196F3);
  
  // Background colors
  static const Color backgroundLight = Color(0xFFF5F5F5);
  static const Color backgroundDark = Color(0xFF121212);
  static const Color surfaceLight = Colors.white;
  static const Color surfaceDark = Color(0xFF1E1E1E);
  
  // Text colors
  static const Color textPrimary = Color(0xFF212121);
  static const Color textSecondary = Color(0xFF757575);
  static const Color textLight = Colors.white;
  
  // Status badge colors
  static const Color pending = Color(0xFFFF9800);
  static const Color approved = Color(0xFF4CAF50);
  static const Color rejected = Color(0xFFF44336);
  static const Color active = Color(0xFF4CAF50);
  static const Color inactive = Color(0xFF9E9E9E);
}
```

### Phase 2: Domain Layer (Entities & Use Cases)

#### Step 2.1: Domain Entities

**File: `lib/domain/entities/user.dart`**
```dart
import 'package:equatable/equatable.dart';

class User extends Equatable {
  final int id;
  final String mobileNumber;
  final String firstName;
  final String lastName;
  final String personalPhoto;
  final DateTime? dateOfBirth;
  final String? idPhoto;
  final String role; // 'tenant', 'owner', 'admin'
  final String status; // 'pending', 'approved', 'rejected'
  final String languagePreference; // 'en', 'ar'
  final double balance;
  final DateTime createdAt;

  const User({
    required this.id,
    required this.mobileNumber,
    required this.firstName,
    required this.lastName,
    required this.personalPhoto,
    this.dateOfBirth,
    this.idPhoto,
    required this.role,
    required this.status,
    this.languagePreference = 'en',
    this.balance = 0.0,
    required this.createdAt,
  });

  String get fullName => '$firstName $lastName';
  
  bool get isApproved => status == 'approved';
  bool get isPending => status == 'pending';
  bool get isRejected => status == 'rejected';
  bool get isAdmin => role == 'admin';
  bool get isOwner => role == 'owner';
  bool get isTenant => role == 'tenant';

  @override
  List<Object?> get props => [
        id,
        mobileNumber,
        firstName,
        lastName,
        personalPhoto,
        dateOfBirth,
        idPhoto,
        role,
        status,
        languagePreference,
        balance,
        createdAt,
      ];
}
```

**File: `lib/domain/entities/apartment.dart`**
```dart
import 'package:equatable/equatable.dart';

class Apartment extends Equatable {
  final int id;
  final int ownerId;
  final String governorate;
  final String? governorateAr;
  final String city;
  final String? cityAr;
  final String address;
  final String? addressAr;
  final double nightlyPrice;
  final double monthlyPrice;
  final int bedrooms;
  final int bathrooms;
  final int livingRooms;
  final double size;
  final String? description;
  final String? descriptionAr;
  final List<String> photos;
  final List<String> amenities;
  final String status; // 'active', 'inactive', 'deleted'
  final DateTime createdAt;
  final DateTime updatedAt;

  const Apartment({
    required this.id,
    required this.ownerId,
    required this.governorate,
    this.governorateAr,
    required this.city,
    this.cityAr,
    required this.address,
    this.addressAr,
    required this.nightlyPrice,
    required this.monthlyPrice,
    required this.bedrooms,
    required this.bathrooms,
    required this.livingRooms,
    required this.size,
    this.description,
    this.descriptionAr,
    this.photos = const [],
    this.amenities = const [],
    required this.status,
    required this.createdAt,
    required this.updatedAt,
  });

  String get fullAddress => '$address, $city, $governorate';
  bool get isActive => status == 'active';

  @override
  List<Object?> get props => [
        id,
        ownerId,
        governorate,
        city,
        address,
        nightlyPrice,
        monthlyPrice,
        bedrooms,
        bathrooms,
        livingRooms,
        size,
        status,
      ];
}
```

**File: `lib/domain/entities/booking.dart`**
```dart
import 'package:equatable/equatable.dart';

class Booking extends Equatable {
  final int id;
  final int tenantId;
  final int apartmentId;
  final DateTime checkInDate;
  final DateTime checkOutDate;
  final int? numberOfGuests;
  final String paymentMethod;
  final double totalRent;
  final String status;
  final DateTime createdAt;
  final DateTime updatedAt;

  const Booking({
    required this.id,
    required this.tenantId,
    required this.apartmentId,
    required this.checkInDate,
    required this.checkOutDate,
    this.numberOfGuests,
    required this.paymentMethod,
    required this.totalRent,
    required this.status,
    required this.createdAt,
    required this.updatedAt,
  });

  int get durationInDays {
    return checkOutDate.difference(checkInDate).inDays;
  }

  bool get isPending => status == 'pending';
  bool get isApproved => status == 'approved';
  bool get isRejected => status == 'rejected';
  bool get isCancelled => status == 'cancelled';
  bool get isActive => status == 'approved' && 
      DateTime.now().isAfter(checkInDate) && 
      DateTime.now().isBefore(checkOutDate);
  bool get isCompleted => status == 'completed';

  @override
  List<Object?> get props => [
        id,
        tenantId,
        apartmentId,
        checkInDate,
        checkOutDate,
        numberOfGuests,
        paymentMethod,
        totalRent,
        status,
        createdAt,
        updatedAt,
      ];
}
```

**File: `lib/domain/entities/notification.dart`**
```dart
import 'package:equatable/equatable.dart';

class NotificationEntity extends Equatable {
  final int id;
  final int userId;
  final String type;
  final String title;
  final String? titleAr;
  final String message;
  final String? messageAr;
  final int? bookingId;
  final bool isRead;
  final DateTime createdAt;

  const NotificationEntity({
    required this.id,
    required this.userId,
    required this.type,
    required this.title,
    this.titleAr,
    required this.message,
    this.messageAr,
    this.bookingId,
    this.isRead = false,
    required this.createdAt,
  });

  String getLocalizedTitle(String language) {
    return language == 'ar' && titleAr != null ? titleAr! : title;
  }

  String getLocalizedMessage(String language) {
    return language == 'ar' && messageAr != null ? messageAr! : message;
  }

  @override
  List<Object?> get props => [
        id,
        userId,
        type,
        title,
        titleAr,
        message,
        messageAr,
        bookingId,
        isRead,
        createdAt,
      ];
}
```

#### Step 2.2: Repository Interfaces

**File: `lib/domain/repositories/auth_repository.dart`**
```dart
import '../entities/user.dart';

abstract class AuthRepository {
  Future<User> login(String mobileNumber, String password);
  Future<void> logout();
  Future<User> getCurrentUser();
  bool isAuthenticated();
  String? getToken();
}
```

**File: `lib/domain/repositories/user_repository.dart`**
```dart
import '../entities/user.dart';

abstract class UserRepository {
  Future<List<User>> getUsers({
    String? search,
    String? status,
    String? role,
    String? sort,
    int page = 1,
    int perPage = 20,
  });
  
  Future<User> getUserDetail(int userId);
  Future<void> approveUser(int userId);
  Future<void> rejectUser(int userId, {String? reason});
  Future<void> deleteUser(int userId);
  Future<void> depositMoney(int userId, double amount, {String? description});
  Future<void> withdrawMoney(int userId, double amount, {String? description});
}
```

### Phase 3: Data Layer (Models & Repositories)

#### Step 3.1: Data Models

**File: `lib/data/models/user_model.dart`**
```dart
import '../../domain/entities/user.dart';

class UserModel extends User {
  UserModel({
    required super.id,
    required super.mobileNumber,
    required super.firstName,
    required super.lastName,
    required super.personalPhoto,
    super.dateOfBirth,
    super.idPhoto,
    required super.role,
    required super.status,
    super.languagePreference,
    super.balance,
    required super.createdAt,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as int,
      mobileNumber: json['mobile_number'] as String,
      firstName: json['first_name'] as String,
      lastName: json['last_name'] as String,
      personalPhoto: json['personal_photo'] as String,
      dateOfBirth: json['date_of_birth'] != null
          ? DateTime.parse(json['date_of_birth'] as String)
          : null,
      idPhoto: json['id_photo'] as String?,
      role: json['role'] as String,
      status: json['status'] as String,
      languagePreference: json['language_preference'] as String? ?? 'en',
      balance: (json['balance'] as num?)?.toDouble() ?? 0.0,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'mobile_number': mobileNumber,
      'first_name': firstName,
      'last_name': lastName,
      'personal_photo': personalPhoto,
      'date_of_birth': dateOfBirth?.toIso8601String(),
      'id_photo': idPhoto,
      'role': role,
      'status': status,
      'language_preference': languagePreference,
      'balance': balance,
      'created_at': createdAt.toIso8601String(),
    };
  }
}
```

### Phase 4: Presentation Layer (Controllers & Pages)

#### Step 4.1: Auth Controller

**File: `lib/presentation/controllers/auth/auth_controller.dart`**
```dart
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../../../core/constants/storage_keys.dart';
import '../../../domain/entities/user.dart';
import '../../../domain/repositories/auth_repository.dart';

class AuthController extends GetxController {
  final AuthRepository authRepository;
  final GetStorage storage = GetStorage();

  AuthController(this.authRepository);

  final _isAuthenticated = false.obs;
  final _currentUser = Rxn<User>();

  bool get isAuthenticated => _isAuthenticated.value;
  User? get currentUser => _currentUser.value;

  @override
  void onInit() {
    super.onInit();
    checkAuthStatus();
  }

  Future<void> checkAuthStatus() async {
    final token = storage.read<String>(StorageKeys.token);
    if (token != null && authRepository.isAuthenticated()) {
      try {
        final user = await authRepository.getCurrentUser();
        _currentUser.value = user;
        _isAuthenticated.value = true;
      } catch (e) {
        await logout();
      }
    }
  }

  Future<bool> login(String mobileNumber, String password) async {
    try {
      final user = await authRepository.login(mobileNumber, password);
      _currentUser.value = user;
      _isAuthenticated.value = true;
      return true;
    } catch (e) {
      return false;
    }
  }

  Future<void> logout() async {
    try {
      await authRepository.logout();
    } catch (e) {
      // Log error but continue logout
    } finally {
      _currentUser.value = null;
      _isAuthenticated.value = false;
      storage.remove(StorageKeys.token);
      storage.remove(StorageKeys.user);
      Get.offAllNamed('/login');
    }
  }
}
```

#### Step 4.2: Login Page

**File: `lib/presentation/pages/auth/login_page.dart`**
```dart
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../controllers/auth/login_controller.dart';

class LoginPage extends StatelessWidget {
  const LoginPage({super.key});

  @override
  Widget build(BuildContext context) {
    return GetBuilder<LoginController>(
      init: LoginController(),
      builder: (controller) {
        return Scaffold(
          body: Row(
            children: [
              // Left Panel - Branding
              Expanded(
                child: Container(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [
                        Theme.of(context).colorScheme.primary,
                        Theme.of(context).colorScheme.primaryDark,
                      ],
                    ),
                  ),
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        // Logo
                        Icon(
                          Icons.apartment,
                          size: 120,
                          color: Colors.white,
                        ),
                        const SizedBox(height: 24),
                        Text(
                          'FindOut Admin',
                          style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                              ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Manage your apartment rental platform',
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                color: Colors.white70,
                              ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              // Right Panel - Login Form
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(48),
                  child: Center(
                    child: SingleChildScrollView(
                      child: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 400),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
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
                            // Mobile Number Field
                            TextField(
                              controller: controller.mobileNumberController,
                              keyboardType: TextInputType.phone,
                              decoration: const InputDecoration(
                                labelText: 'Mobile Number',
                                hintText: '+201234567890',
                                prefixIcon: Icon(Icons.phone),
                              ),
                            ),
                            const SizedBox(height: 24),
                            // Password Field
                            Obx(() => TextField(
                              controller: controller.passwordController,
                              obscureText: !controller.isPasswordVisible.value,
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
                              ),
                            )),
                            const SizedBox(height: 16),
                            // Remember Me & Forgot Password
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Obx(() => Checkbox(
                                  value: controller.rememberMe.value,
                                  onChanged: controller.toggleRememberMe,
                                )),
                                const Text('Keep me signed in'),
                                const Spacer(),
                                TextButton(
                                  onPressed: () {
                                    // TODO: Implement forgot password
                                  },
                                  child: const Text('Forgot Password?'),
                                ),
                              ],
                            ),
                            const SizedBox(height: 32),
                            // Login Button
                            Obx(() => ElevatedButton(
                              onPressed: controller.isLoading.value
                                  ? null
                                  : controller.login,
                              style: ElevatedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(vertical: 16),
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
                                  : const Text('Sign In'),
                            )),
                            const SizedBox(height: 24),
                            // Language Selector
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                TextButton(
                                  onPressed: () {
                                    // TODO: Change language
                                  },
                                  child: const Text('English'),
                                ),
                                const Text('|'),
                                TextButton(
                                  onPressed: () {
                                    // TODO: Change language
                                  },
                                  child: const Text('العربية'),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
```

**File: `lib/presentation/controllers/auth/login_controller.dart`**
```dart
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../../../domain/repositories/auth_repository.dart';
import '../../auth/auth_controller.dart';

class LoginController extends GetxController {
  final AuthRepository authRepository;
  final AuthController authController = Get.find();

  final mobileNumberController = TextEditingController();
  final passwordController = TextEditingController();
  final isPasswordVisible = false.obs;
  final rememberMe = false.obs;
  final isLoading = false.obs;

  LoginController() : authRepository = Get.find() {
    // Load saved credentials if remember me was checked
    final savedMobile = GetStorage().read('saved_mobile');
    if (savedMobile != null) {
      mobileNumberController.text = savedMobile;
    }
  }

  void togglePasswordVisibility() {
    isPasswordVisible.value = !isPasswordVisible.value;
  }

  void toggleRememberMe(bool? value) {
    rememberMe.value = value ?? false;
  }

  Future<void> login() async {
    if (!_validateInputs()) {
      return;
    }

    isLoading.value = true;
    try {
      final success = await authController.login(
        mobileNumberController.text.trim(),
        passwordController.text,
      );

      if (success) {
        // Save credentials if remember me
        if (rememberMe.value) {
          GetStorage().write('saved_mobile', mobileNumberController.text.trim());
        }

        Get.offAllNamed('/dashboard');
      } else {
        Get.snackbar(
          'Error',
          'Invalid mobile number or password',
          snackPosition: SnackPosition.BOTTOM,
          backgroundColor: Colors.red,
          colorText: Colors.white,
        );
      }
    } catch (e) {
      Get.snackbar(
        'Error',
        e.toString(),
        snackPosition: SnackPosition.BOTTOM,
        backgroundColor: Colors.red,
        colorText: Colors.white,
      );
    } finally {
      isLoading.value = false;
    }
  }

  bool _validateInputs() {
    if (mobileNumberController.text.trim().isEmpty) {
      Get.snackbar('Error', 'Please enter mobile number');
      return false;
    }
    if (passwordController.text.isEmpty) {
      Get.snackbar('Error', 'Please enter password');
      return false;
    }
    return true;
  }

  @override
  void onClose() {
    mobileNumberController.dispose();
    passwordController.dispose();
    super.onClose();
  }
}
```

### Phase 5: Main App Setup

**File: `lib/main.dart`**
```dart
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import 'app/app.dart';
import 'app/bindings/initial_binding.dart';
import 'app/routes/app_pages.dart';
import 'core/theme/app_theme.dart';
import 'localization/app_localizations.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize GetStorage
  await GetStorage.init();
  
  // Initialize GetX
  Get.put(InitialBinding());
  
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return GetMaterialApp(
      title: 'FindOut Admin',
      debugShowCheckedModeBanner: false,
      
      // Theme
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: AppTheme.themeMode,
      
      // Localization
      locale: const Locale('en', 'US'),
      fallbackLocale: const Locale('en', 'US'),
      localizationsDelegates: const [
        AppLocalizations.delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      supportedLocales: const [
        Locale('en', 'US'),
        Locale('ar', 'SA'),
      ],
      
      // Routing
      initialRoute: AppPages.INITIAL,
      getPages: AppPages.routes,
      
      // Initial binding
      initialBinding: InitialBinding(),
    );
  }
}
```

**File: `lib/app/routes/app_pages.dart`**
```dart
import 'package:get/get.dart';
import '../../presentation/pages/auth/login_page.dart';
import '../../presentation/pages/dashboard/dashboard_page.dart';
import '../../presentation/pages/registration/pending_registrations_page.dart';
import '../../presentation/pages/registration/registration_detail_page.dart';
import '../../presentation/pages/user/all_users_page.dart';
import '../../presentation/pages/apartment/all_apartments_page.dart';
import '../../presentation/pages/booking/all_bookings_page.dart';
import '../../presentation/pages/settings/profile_page.dart';
import '../../presentation/pages/settings/language_page.dart';
import '../../presentation/pages/settings/theme_page.dart';

class AppPages {
  AppPages._();

  static const INITIAL = '/login';

  static final routes = [
    GetPage(
      name: '/login',
      page: () => const LoginPage(),
    ),
    GetPage(
      name: '/dashboard',
      page: () => const DashboardPage(),
      // Add middleware for auth guard
    ),
    GetPage(
      name: '/pending-registrations',
      page: () => const PendingRegistrationsPage(),
    ),
    GetPage(
      name: '/registration-detail/:id',
      page: () => const RegistrationDetailPage(),
    ),
    GetPage(
      name: '/users',
      page: () => const AllUsersPage(),
    ),
    GetPage(
      name: '/apartments',
      page: () => const AllApartmentsPage(),
    ),
    GetPage(
      name: '/bookings',
      page: () => const AllBookingsPage(),
    ),
    GetPage(
      name: '/settings/profile',
      page: () => const ProfilePage(),
    ),
    GetPage(
      name: '/settings/language',
      page: () => const LanguagePage(),
    ),
    GetPage(
      name: '/settings/theme',
      page: () => const ThemePage(),
    ),
  ];
}
```

## Implementation Checklist

### Phase 1: Core Infrastructure ✅
- [x] Project setup
- [x] DDD structure
- [x] Network layer (Dio)
- [x] Theme configuration
- [x] Localization setup

### Phase 2: Authentication
- [ ] Login page
- [ ] Auth controller
- [ ] Auth repository
- [ ] Token management
- [ ] Auth guard middleware

### Phase 3: Dashboard
- [ ] Dashboard page
- [ ] Statistics cards
- [ ] Dashboard controller
- [ ] Statistics API integration

### Phase 4: Registration Management
- [ ] Pending registrations list
- [ ] Registration detail page
- [ ] Approve/Reject functionality
- [ ] Quick actions

### Phase 5: User Management
- [ ] All users page (master-detail)
- [ ] User detail panel
- [ ] Delete user functionality
- [ ] Balance management (deposit/withdraw)
- [ ] Transaction history

### Phase 6: Content Overview
- [ ] All apartments page
- [ ] All bookings page
- [ ] Filters and search

### Phase 7: Settings
- [ ] Profile page
- [ ] Language settings
- [ ] Theme settings

### Phase 8: Common Components
- [ ] Sidebar navigation
- [ ] Top app bar
- [ ] Data tables
- [ ] Modals and dialogs
- [ ] Loading states
- [ ] Error handling

## Notes

1. **API Endpoints**: Some endpoints may not be ready yet. Mark them with `// TODO: API not ready` comments.

2. **Notifications**: For web, we'll use in-app notifications (not FCM push). The notification system will poll the API or use WebSockets if available.

3. **Responsive Design**: Ensure all screens work on minimum 1024px width. Show message for smaller screens.

4. **RTL Support**: Implement proper RTL layout for Arabic language.

5. **State Management**: Use GetX for all state management. Controllers should extend `GetxController`.

6. **Error Handling**: Implement consistent error handling across all screens.

7. **Loading States**: Show appropriate loading indicators for all async operations.

8. **Validation**: Implement form validation using validators.

## Next Steps

1. Start with Phase 1 (Core Setup)
2. Implement Authentication (Phase 2)
3. Build Dashboard (Phase 3)
4. Continue with remaining phases in order

Each phase should be completed and tested before moving to the next.

