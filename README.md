# WHMCS Addon Module Framework (WAM)

WAM is a powerful framework designed to simplify the creation and management of WHMCS addon modules. It provides a structured approach with modern PHP practices and helpful CLI commands.

## Installation

To create a new WHMCS addon module, use Composer:

```bash
composer create-project asamserver/wam your-addon-name dev-main
```

This will create a new directory with your addon name and install all necessary dependencies.

## Directory Structure

After installation, your project will have the following structure:

```
your-addon-name/
├── app/
│   ├── Controllers/
│   ├── Dispatcher/
│   ├── Helper/
│   └── Models/
├── commands/
├── database/
├── resource/
│   ├── css/
│   ├── js/
│   └── views/
└── routes/
```

## Available Commands

WAM comes with several CLI commands to help you build your addon module:

### 1. Create Addon Module

```bash
php whmcs make:addon YourAddonName
```

This command will:
- Create the main addon file
- Set up the basic directory structure
- Generate necessary dispatcher files
- Create a base controller
- Set up routing configuration

**Output:**
```
Created app directory: /path/to/your/addon/app
Created addon file: /path/to/your/addon/YourAddonName.php
Created Application file: /path/to/your/addon/app/Application.php
Created Helper directory: /path/to/your/addon/app/Helper
Created helper file: /path/to/your/addon/app/Helper/Helper.php
Created adminDispatcher directory: /path/to/your/addon/app/Dispatcher
Created AdminDispatcher file: /path/to/your/addon/app/Dispatcher/AdminDispatcher.php
Created AdminDispatcher file: /path/to/your/addon/app/Dispatcher/ClientDispatcher.php
Created Router file: /path/to/your/addon/app/Router.php
Created BaseController directory: /path/to/your/addon/app/Controllers
Created BaseController file: /path/to/your/addon/app/Controllers/BaseController.php
```

### 2. Create Controller

```bash
php whmcs make:controller Admin/DashboardController
```

Creates a new controller with basic structure and routing.

**Output:**
```
Created controller: /path/to/your/addon/app/Controllers/Admin/DashboardController.php
```

### 3. Create Model

```bash
php whmcs make:model User
```

Generates a new Eloquent model class.

**Output:**
```
Created model: /path/to/your/addon/app/Models/User.php
```

### 4. Create Migration

```bash
php whmcs make:migration users
```

Creates a new database migration file.

**Output:**
```
Created database directory: /path/to/your/addon/database
Created migration file: /path/to/your/addon/database/YYYY_MM_DD_HHMMSS_create_users_table.php
```

### 5. Create Environment File

```bash
php whmcs make:env
```

Creates a .env file from .env.example if it doesn't exist.

**Output:**
```
.env file successfully created from .env.example
```

## Configuration

### Environment Variables

The following environment variables can be configured in your `.env` file:

```env
MODULE=YourAddonName
AUTHOR=Your Name
VERSION=1.0.0
APP_ENABLE=true
DELETE_TABLES=false
```

### Routes

Define your routes in `routes/web.php`:

```php
return [
    // Admin routes
    'admin/dashboard' => [
        'controller' => DashboardController::class,
        'action' => 'index',
    ],
    
    // Client routes
    'client/dashboard' => [
        'controller' => ClientDashboardController::class,
        'action' => 'index',
    ],
];
```

## Views and Assets

### Views

Place your view files in `resource/views/`. The BaseController provides a `renderView` method:

```php
public function index()
{
    return $this->renderView('dashboard', [
        'title' => 'Dashboard',
        'data' => $someData
    ]);
}
```

### CSS and JavaScript

- CSS files go in `resource/css/`
- JavaScript files go in `resource/js/`
- Access them using `renderCss()` and `renderJs()` methods in BaseController

## Helper Functions

The framework provides several helper functions in `app/Helper/Helper.php`:

- `generateRandomNumber()`: Generates a random number
- `getClientId()`: Gets the current client ID
- `getControllerClass()`: Gets the fully qualified controller class name

## Best Practices

1. Always use the provided CLI commands to generate new files
2. Follow PSR-4 autoloading standards
3. Use migrations for database changes
4. Keep controllers thin and move business logic to models
5. Use environment variables for configuration
6. Follow WHMCS security best practices

## Requirements

- PHP >= 8.0
- WHMCS installation
- Composer

## Dependencies

The framework includes:
- illuminate/database
- symfony/console
- vlucas/phpdotenv

## License

This project is licensed under the MIT License.