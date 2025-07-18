<p align="center"><img src="https://img.shields.io/badge/eSyncore-ERP%20System-blue" width="400" alt="eSyncore Logo"></p>

<p align="center">
<a href="#"><img src="https://img.shields.io/badge/Version-1.0.0-blue" alt="Version"></a>
<a href="#"><img src="https://img.shields.io/badge/Laravel-10.x-red" alt="Laravel Version"></a>
<a href="#"><img src="https://img.shields.io/badge/PHP-8.1+-green" alt="PHP Version"></a>
<a href="#"><img src="https://img.shields.io/badge/License-MIT-yellow" alt="License"></a>
</p>

## About eSyncore

eSyncore is a comprehensive Enterprise Resource Planning (ERP) system built with Laravel and Filament. It provides businesses with powerful tools to manage their operations efficiently, from inventory and purchase orders to sales and customer relations. eSyncore is designed to be flexible, user-friendly, and adaptable to various business needs.

## Core Features

### Products & Inventory Management
- **Product Catalog Management**: Add, edit, and manage product details with categories
- **Stock Tracking**: Real-time monitoring of inventory levels across warehouses
- **Stock Transfers**: Manage and track product movement between warehouses
- **Stock Adjustments**: Record inventory discrepancies and adjustments with approval workflow

### Supplier Management
- **Supplier Directory**: Comprehensive supplier database with contact information and payment terms
- **Performance Tracking**: Monitor supplier delivery times and quality metrics
- **Advanced Filtering**: Filter suppliers by status, country, and creation date
- **Bulk Operations**: Activate/deactivate multiple suppliers at once
- **Bank Information**: Track supplier banking details for payments

### Customer Management
- **Customer Profiles**: Detailed customer information with outstanding balances
- **Credit Management**: Track credit limits and payment history
- **Custom Fields**: Store additional customer-specific information
- **Contact History**: Log of all customer interactions

### Purchase Order Management
- **Multi-line Orders**: Create POs with multiple items and custom quantities
- **Approval Workflow**: Built-in approval process for purchase orders
- **Receiving Management**: Record and track partial or complete order receipts
- **PDF Generation**: Create professional PDF purchase orders for sending to suppliers

### Sales Order Management
- **Order Processing**: Create and track customer orders from creation to fulfillment
- **Invoice Generation**: Automatic invoice creation from sales orders
- **Discount Management**: Apply item-specific or order-wide discounts
- **Payment Tracking**: Record and monitor payment status

### Dashboard & Reporting
- **KPI Dashboard**: Real-time display of key performance indicators
- **Sales Analytics**: Visual representation of sales trends and data
- **Inventory Analytics**: Stock level insights and movement patterns
- **Pending Orders**: Quick view of all pending purchase and sales orders

### Company Settings
- **Multi-currency Support**: Configure base currency and formatting preferences
- **Localization**: Adapt to local business practices and formats
- **Company Profile**: Centralized company information management
- **Appearance Settings**: Customize the look and feel of documents and reports

## Technical Stack

eSyncore ERP is built on the following technologies:

- **Laravel 10.x**: Backend framework providing robust API and business logic
- **PHP 8.1+**: Latest PHP features for improved performance and security
- **Filament 3.x**: Admin panel framework for rapid UI development
- **PostgreSQL/MySQL**: Database support for both popular engines
- **Livewire**: Interactive UIs without complex JavaScript
- **Tailwind CSS**: Modern utility-first CSS framework

## Installation

```bash
# Clone the repository
git clone https://github.com/your-username/esyncore.git
cd esyncore

# Install PHP dependencies
composer install

# Copy environment file and configure your database
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed

# Install NPM dependencies and build assets
npm install
npm run build

# Serve the application
php artisan serve
```

## Development Setup

For a local development environment, follow these steps:

1. Set up your database connection in `.env` file
2. Configure your company settings through the admin panel
3. Run seeders to populate test data: `php artisan db:seed`
4. For hot-reload development: `npm run dev`

## Usage

Access the system through:

- **Main Portal**: `http://localhost:8000/portal`
- **Admin Panel**: `http://localhost:8000/admin` (if separate admin panel is enabled)

Default credentials:
- Email: `admin@example.com`
- Password: `password`

## Data Seeding

eSyncore comes with comprehensive seeders for:
- Companies
- Suppliers
- Customers
- Products & Categories
- Sample purchase orders
- Sample sales orders

Run any specific seeder with: `php artisan db:seed --class=SupplierSeeder`

## Coming Soon

- Employee management and payroll
- Advanced reporting module
- Multi-warehouse inventory management
- User roles and permissions
- Mobile-responsive interfaces
- API for external integrations

## License

eSyncore ERP is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
