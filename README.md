# WHMCS Addon Module Framework

A Laravel-inspired framework for building WHMCS addon modules with modern PHP practices and a familiar structure. This framework provides a robust foundation for developing WHMCS addons with features like routing, MVC architecture, database migrations, and hooks management.

## Features

- MVC Architecture
- Route Management
- Database Migrations
- Model Generation
- Controller Generation
- Hook System
- CLI Commands
- Admin & Client Area Support
- Helper Functions
- Resource Management (CSS, JS, Views)

## Installation

1. Create a new directory for your addon in the WHMCS modules/addons directory:
```bash
cd modules/addons
mkdir your_addon_name
cd your_addon_name
```

2. Clone this repository:
```bash
git clone https://github.com/yourusername/wam.git .
```

3. Install dependencies:
```bash
composer install
```

4. Set up your environment:
```bash
cp .env.example .env
```

5. Configure your .env file with appropriate database settings:
```
DB_PREFIX=mod_youraddonname
APPENV=local
```



### Quick Installation

1. Navigate to your WHMCS modules/addons directory:
```bash
cd modules/addons
```

2. Create a new addon using Composer:
```bash
composer create-project asamserver/wam your_addon_name dev-main
```

## Command Line Interface

The framework provides a CLI tool named `asam` for generating various components. Here are the available commands:

### Basic Commands

```bash
# Create a new addon
php asam make:addon YourAddonName

# Generate a controller
php asam make:controller Admin/DashboardController

# Create a model
php asam make:model User

# Generate a migration
php asam make:migration users

# Run migrations
php asam migrate

# Create a hook
php asam make:hook ClientLogin

# Generate web routes
php asam make:web
```

## Directory Structure

```
your_addon_name/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Hooks/
│   ├── Dispatcher/
│   ├── Helper/
│   └── Application.php
├── database/
├── resource/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
│   └── web.php
├── storage/
│   └── logs/
└── vendor/
```

## Creating Controllers

Controllers handle the business logic of your addon. To create a new controller:

```bash
php asam make:controller Admin/DashboardController
```

Example controller structure:

```php
namespace WHMCS\Module\Addon\YourAddon\app\Controllers;

class DashboardController extends BaseController
{
    public function index(array $vars)
    {
        return $this->renderView('admin.dashboard', [
            'title' => 'Dashboard'
        ]);
    }
}
```

## Defining Routes

Routes are defined in the `routes/web.php` file:

```php
use WHMCS\Module\Addon\YourAddon\app\Controllers\Admin\DashboardController;

$this->get('/admin/dashboard', DashboardController::class, 'index');
$this->get('/client/dashboard', ClientDashboardController::class, 'index');
```

## Working with Models

Create models to interact with your database:

```bash
php asam make:model User
```

Example model usage:

```php
namespace WHMCS\Module\Addon\YourAddon\app\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'email'];
}
```

## Database Migrations

Create and run database migrations:

```bash
# Create a migration
php asam make:migration create_users_table

# Run migrations
php asam migrate
```

Example migration:

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
    }
}
```

## Working with Hooks

Create and manage WHMCS hooks:

```bash
php asam make:hook ClientLogin
```

Example hook:

```php
namespace WHMCS\Module\Addon\YourAddon\app\Hooks;

class ClientLoginHook
{
    public function handle(array $params)
    {
        // Hook logic here
    }
}
```

## Views and Templates

Views are stored in the `resource/views` directory. Render views from your controllers:

```php
public function index(array $vars)
{
    return $this->renderView('admin.dashboard', [
        'title' => 'Dashboard',
        'data' => $someData
    ]);
}
```

## Helper Functions

The framework provides various helper functions in `app/Helper/Helper.php`:

```php
// Get current client ID
$clientId = YourAddon_getClientId();

// Generate random number
$random = YourAddon_generateRandomNumber();
```

## Best Practices

1. Always use namespaces as defined in the framework
2. Follow the MVC pattern
3. Use migrations for database changes
4. Keep controllers thin and move business logic to services
5. Use dependency injection where possible
6. Log important events using the built-in logging system

## Error Handling

The framework includes built-in error handling:

```php
try {
    // Your code here
} catch (\Exception $e) {
    $this->hooksManager->log("Error: " . $e->getMessage(), 'ERROR');
}
```

## Contributing

Please read CONTRIBUTING.md for details on our code of conduct and the process for submitting pull requests.

## License

This project is licensed under the MIT License - see the LICENSE.md file for details

## Support

For support, please open an issue in the GitHub repository or contact the maintainers.

