# POS System Backend

This is a robust and secure backend for a Point of Sale (POS) system, built with **Laravel 12**. It provides a solid foundation for managing users, permissions, authentication, and API documentation, ready to be connected to any modern frontend application.

## Key Features

-   **Modern Framework**: Built on the latest **Laravel 12** and PHP 8.2+.
-   **Token-Based Authentication**: Secure, stateless API authentication using **Laravel Sanctum**.
-   **Granular Permission System**: A flexible role-agnostic permission system to control user access to specific API endpoints.
-   **Performance-Oriented**: Implements caching for user permissions to minimize database queries.
-   **API Documentation**: Auto-generated, interactive API documentation powered by **Swagger/OpenAPI** (`l5-swagger`).
-   **CORS Support**: Pre-configured for seamless and secure communication with frontend applications (e.g., React, Vue, Angular) on different domains.
-   **Database Seeders**: Includes pre-built seeders for default permissions and user roles (Admin, Cashier) for immediate testing and setup.
-   **Automated Tests**: Comes with a suite of feature tests to ensure API authentication and permission middleware are working correctly.

---

## Prerequisites

Before you begin, ensure you have the following installed on your local machine:

-   PHP >= 8.2
-   Composer
-   Node.js & npm (for frontend or other tooling)
-   A database server (e.g., MySQL, MariaDB, or PostgreSQL)

---

## 🚀 Installation & Setup Instructions

Follow these steps to get the project up and running on your local machine.

### 1. Clone the Repository

```bash
git clone https://your-repository-url.com/pos-backend.git
cd pos-backend

```

### 2. Install Dependencies
Install the required PHP packages using Composer.
```bash
composer install
```

### 3. Set Up Environment File
Copy the example environment file and generate the application key.
```bash
cp .env.example .env
php artisan key:generate
```
### 4. Configure the Database
Open the .env file and update the DB_ variables to match your local database credentials.
```bash

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_backend
DB_USERNAME=root
DB_PASSWORD=
```
### 5. Configure the Frontend URL for CORS
In the same .env file, specify the URL of the frontend application that will be communicating with this API. For local development, this is typically a localhost address.
```bash
FRONTEND_URL=http://localhost:3000
(Adjust the port if your frontend uses a different one, e.g., 5173 for Vite).

```
### 6. Run Migrations and Seed the Database
This is the most important step. This single command will create all the necessary database tables and populate them with default permissions and users.
```bash
php artisan migrate:fresh --seed
This command will:
Drop all existing tables.
Run all database migrations.
Run the PermissionSeeder and UserSeeder.
```

▶️ Running the Application
Start the Development Server
To start the local Laravel development server, run:
```bash
php artisan serve
```
The API will now be available at http://localhost:8000.

API Documentation (Swagger UI)
The full API documentation is available to view and test in your browser. Navigate to:
http://127.0.0.1:8000/api/documentation
If you make changes to the API annotations in the code, regenerate the documentation by running:

```bash
php artisan l5-swagger:generate
```

🔑 Default Seeded Users
The database seeder creates the following users for you to use immediately for testing:
| Role    | Email                | Password | Key Permissions                      |
|---------|----------------------|----------|--------------------------------------|
| Admin   | admin@example.com    | password | Has all permissions.                 |
| Cashier | cashier@example.com  | password | `sales.create`, `products.view`      |
| Guest   | guest@example.com    | password | Has no permissions.                  |
