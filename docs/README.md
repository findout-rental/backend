# FindOut - Project Documentation

[![Project](https://img.shields.io/badge/Project-FindOut-blue)](https://github.com/findout-rental)
[![Status](https://img.shields.io/badge/Status-In%20Development-yellow)]()

## 📚 Overview

This repository contains the complete documentation for **FindOut**, an apartment rental application that connects tenants with apartment owners through a seamless mobile and web experience.

## 📁 Documentation Files

| File | Description |
|------|-------------|
| **[SRS.md](SRS.md)** | Software Requirements Specification - Complete functional and non-functional requirements |
| **[ERD.md](ERD.md)** | Entity-Relationship Diagram - Database design and relationships |
| **[project-requirements.md](project-requirements.md)** | Original project requirements and grading criteria |
| **[database.sql](database.sql)** | MySQL database schema - Ready to execute SQL script |
| **[database.dbml](database.dbml)** | DBML format for [dbdiagram.io](https://dbdiagram.io/) visualization |
| **[findout-erd.pdf](findout-erd.pdf)** | Visual ERD diagram in PDF format |
| **[customer-app-screens.md](customer-app-screens.md)** | Complete screen specifications for the customer mobile app (Tenants & Owners) |
| **[admin-web-screens.md](admin-web-screens.md)** | Complete screen specifications for the admin web application |
| **[payment-system-implementation.md](payment-system-implementation.md)** | Payment system implementation guide with flows, calculations, and API recommendations |

## 🏗️ Project Architecture

FindOut consists of **4 main components**:

1. **📱 Customer Mobile App** (Flutter)
   - For Tenants and Apartment Owners
   - Android & iOS support
   - Repository: [customer-app](https://github.com/findout-rental/customer-app) ✅

2. **🌐 Admin Web Application** (Flutter Web)
   - For System Administrators
   - Browser-based dashboard
   - Repository: [admin-web](https://github.com/findout-rental/admin-web) ✅

3. **⚙️ Backend API** (Laravel)
   - RESTful API
   - MySQL Database
   - Repository: [backend](https://github.com/findout-rental/backend) ✅

4. **📖 Documentation** (This Repository)
   - All project documentation
   - Database schemas
   - Requirements specifications
   - Repository: [docs](https://github.com/findout-rental/docs) ✅

## 🎯 Key Features

### For Tenants
- ✅ Browse and search apartments with filters
- ✅ Book apartments for specific periods
- ✅ Virtual balance management
- ✅ View transaction history
- ✅ Rate and review apartments
- ✅ Favorites list
- ✅ In-app messaging with owners
- ✅ Booking management (modify/cancel)
- ✅ Multi-language support (Arabic/English)
- ✅ Dark/Light theme

### For Apartment Owners
- ✅ Add and manage apartments with flexible pricing (nightly + monthly rates)
- ✅ Approve/reject booking requests
- ✅ Virtual balance management
- ✅ View transaction history
- ✅ Communicate with tenants
- ✅ View booking statistics
- ✅ Multi-language support (Arabic/English)
- ✅ Dark/Light theme

### For Administrators
- ✅ Approve/reject user registrations
- ✅ Manage users (view/delete)
- ✅ Manage user balances (add money to tenants, withdraw from owners)
- ✅ View user transaction history
- ✅ View system statistics
- ✅ Dashboard with insights
- ✅ Multi-language support (Arabic/English)
- ✅ Dark/Light theme

## 🗄️ Database Schema

The application uses **MySQL** with the following main entities:

- **Users** - All user types (tenants, owners, admins) with account balances
- **Apartments** - Property listings with nightly and monthly pricing
- **Bookings** - Rental bookings with status tracking and rent calculation
- **Transactions** - Payment transaction history (deposits, withdrawals, rent payments, refunds)
- **Ratings** - Apartment reviews and ratings
- **Favorites** - Tenant's saved apartments
- **Messages** - In-app messaging system
- **Notifications** - System notifications
- **OTP Verifications** - Authentication codes

View the complete schema in [database.sql](database.sql) or visualize it using [database.dbml](database.dbml) on [dbdiagram.io](https://dbdiagram.io/).

## 🛠️ Technology Stack

| Component | Technology |
|-----------|------------|
| Customer Mobile App | Flutter (Dart) |
| Admin Web App | Flutter Web (Dart) |
| Backend API | Laravel (PHP 8.1+) |
| Database | MySQL 8.0+ |
| Authentication | OTP via SMS |
| Notifications | Push Notifications (FCM/APNS) |

## 💳 Payment System

FindOut includes a virtual balance and payment system:

- **Balance Management**: All users (tenants and owners) have account balances
- **Pricing Model**: Apartments use **nightly price + monthly price** with automatic rate optimization
  - For bookings ≤ 30 nights: Uses nightly rate
  - For bookings > 30 nights: Compares daily vs monthly, uses cheaper option
- **Payment Flow**: Rent is transferred from tenant to owner when booking is created
- **Refunds**: Full refund for rejected bookings, partial refund (80%) for cancellations
- **Admin Controls**: Admins can add money to tenant balances and withdraw from owner balances
- **Transaction History**: Complete audit trail of all financial transactions

See [payment-system-implementation.md](payment-system-implementation.md) for detailed implementation guide.

## 📖 How to Use This Documentation

1. **Start with** [project-requirements.md](project-requirements.md) for project overview
2. **Read** [SRS.md](SRS.md) for detailed requirements
3. **Review** [ERD.md](ERD.md) or [findout-erd.pdf](findout-erd.pdf) for database design
4. **Read** [payment-system-implementation.md](payment-system-implementation.md) for payment system details
5. **Review** [customer-app-screens.md](customer-app-screens.md) for mobile app UI specifications
6. **Review** [admin-web-screens.md](admin-web-screens.md) for admin panel UI specifications
7. **Use** [database.sql](database.sql) to create the database
8. **Visualize** [database.dbml](database.dbml) on [dbdiagram.io](https://dbdiagram.io/)

## 📝 Documentation Standards

- All documents are written in **Markdown** format
- Database schemas follow **MySQL 8.0+** standards
- DBML files compatible with [dbdiagram.io](https://dbdiagram.io/)
- SQL files use **InnoDB** engine with **UTF8MB4** charset

## 🤝 Contributing

This is an academic project. For any questions or suggestions, please contact the project maintainer.

## 👨‍💻 Project Maintainer

**Abd Alazez Alboga**
- GitHub: [@abd-alazez-alboga](https://github.com/abd-alazez-alboga)
- Organization: [FindOut Rental](https://github.com/findout-rental)

## 📄 License

This project is part of an academic assignment. All rights reserved.

---

**Last Updated:** December 2024  
**Version:** 1.1

## 📋 Recent Updates

- ✅ **Payment System**: Added virtual balance management and transaction tracking
- ✅ **Pricing Model**: Implemented nightly + monthly pricing with automatic rate optimization
- ✅ **Screen Specifications**: Complete UI/UX documentation for mobile and web apps
- ✅ **Database Schema**: Updated with balance and transaction tables

