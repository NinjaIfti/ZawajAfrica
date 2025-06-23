# README.md

# ZawajAfrica - Full Stack Web Application

A comprehensive web platform for therapy booking and matrimonial services with integrated chat, payment processing, and multi-language support.

## 🚀 Tech Stack

### **Backend Framework**

- **Laravel 12.16.0** - Modern PHP framework for robust backend development
- **Laravel Breeze** - Authentication scaffolding with API support
- **Laravel Sanctum** - API token authentication for secure mobile/SPA access

### **Frontend Framework**

- **Vue.js 3** - Progressive JavaScript framework for building user interfaces
- **Inertia.js** - Modern monolith approach connecting Laravel and Vue seamlessly
- **Tailwind CSS** - Utility-first CSS framework for rapid UI development
- **Vite** - Fast build tool and development server

### **Database**

- **SQLite** (Development) - Lightweight database for local development
- **MySQL/PostgreSQL** (Production) - Scalable database for production deployment

### **Third-Party Integrations**

#### **Communication & Email**

- **Zoho Mail** - Professional email service for platform communications
- **Zoho Calendar/Bookings** - Appointment scheduling and management
- **Zoho Chat (SalesIQ/Cliq)** - Live support and chat features

#### **Payment Processing**

- **Paystack** - Secure payment gateway for VIP subscriptions and therapist bookings
- Supports multiple payment methods and mobile-friendly transactions

#### **Translation Services**

- **Azure Translator API** - Microsoft's translation service for multi-language support
- Real-time translation of profiles, chats, and messages

#### **Additional Features**

- **Pre-built Chatbot** - AI-powered customer support and user guidance
- **Custom Booking System** - Multi-platform therapy sessions (Zoom, WhatsApp, Telegram, Google Meet)

## 🏗️ Architecture

### **Monolithic SPA Architecture**

- **Laravel** handles all backend logic, API endpoints, and business logic
- **Vue.js** manages the frontend user interface and user interactions
- **Inertia.js** bridges the gap, providing SPA experience without API complexity

### **Key Benefits**

- ✅ Single codebase deployment
- ✅ Server-side rendering capabilities
- ✅ Seamless data flow between backend and frontend
- ✅ No need for separate API documentation
- ✅ Built-in CSRF protection and security features

## 📱 Features

### **Core Functionality**

- **User Authentication** - Registration, login, password reset, email verification
- **Therapist Booking System** - Schedule appointments with preferred communication platforms
- **Multi-language Support** - Real-time translation powered by Azure Translator
- **Payment Integration** - Secure payments for VIP subscriptions and services
- **Live Chat Support** - Integrated chatbot and live support system

### **Admin Features**

- **User Management** - Comprehensive user and therapist administration
- **Booking Management** - Appointment scheduling and calendar integration
- **Payment Tracking** - Transaction monitoring and subscription management
- **Analytics Dashboard** - User engagement and platform statistics

## 🛠️ Development Setup

### **Prerequisites**

- PHP 8.2+
- Composer
- Node.js 18+
- npm or yarn

### **Installation**

```bash
# Clone the repository
git clone [repository-url]
cd zawaj-africa

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Build frontend assets
npm run build
```

### **Development Servers**

```bash
# Terminal 1: Laravel backend
php artisan serve

# Terminal 2: Vite frontend (for hot reloading)
npm run dev
```

## 🌍 Deployment

### **Production Environment**

- **Hosting**: DigitalOcean
- **Domain**: Registered with QServers.ng
- **DNS**: Managed through QServers
- **Email**: Zoho Mail with custom MX records

### **Deployment Steps**

1. Configure production environment variables
2. Set up database and run migrations
3. Configure Zoho Mail MX records
4. Set up SSL certificates
5. Configure web server (Nginx/Apache)
6. Build and deploy frontend assets

## 🔧 Configuration

### **Environment Variables**

```env
# Application
APP_NAME=ZawajAfrica
APP_ENV=production
APP_KEY=[generated-key]
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zawaj_africa
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mail (Zoho)
MAIL_MAILER=smtp
MAIL_HOST=smtp.zoho.com
MAIL_PORT=587
MAIL_USERNAME=your_zoho_email
MAIL_PASSWORD=your_zoho_password

# Paystack
PAYSTACK_PUBLIC_KEY=your_paystack_public_key
PAYSTACK_SECRET_KEY=your_paystack_secret_key

# Azure Translator
AZURE_TRANSLATOR_KEY=your_azure_key
AZURE_TRANSLATOR_REGION=your_region
```

## 📚 API Integrations

### **Zoho Services**

- **Mail**: Automated email notifications and communications
- **Calendar**: Appointment scheduling and reminders
- **Chat**: Customer support and live assistance

### **Payment Gateway**

- **Paystack**: Secure payment processing with webhook support
- **Supported Methods**: Card payments, bank transfers, mobile money

### **Translation**

- **Azure Translator**: Real-time text translation
- **Supported Languages**: Multiple language pairs for global accessibility

## 🔒 Security Features

- **CSRF Protection** - Built-in Laravel security
- **SQL Injection Prevention** - Eloquent ORM protection
- **XSS Protection** - Vue.js template security
- **Authentication Throttling** - Rate limiting for login attempts
- **Secure Headers** - Security headers for production deployment

## 🧪 Testing

```bash
# Run PHP tests
php artisan test

# Run JavaScript tests
npm run test

# Code quality checks
./vendor/bin/pint
```

## 📖 Documentation

- **Laravel Documentation**: https://laravel.com/docs
- **Vue.js Documentation**: https://vuejs.org/guide/
- **Inertia.js Documentation**: https://inertiajs.com/
- **Tailwind CSS Documentation**: https://tailwindcss.com/docs

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is proprietary software. All rights reserved.

## 📞 Support

For technical support or questions about the platform:

- Email: support@zawajafrica.com
- Documentation: [Internal Wiki/Documentation]
- Development Team: [Contact Information]

---

**Built with ❤️ using Laravel, Vue.js, and modern web technologies.**
