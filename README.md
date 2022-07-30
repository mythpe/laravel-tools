# MyTh Laravel tools

Laravel framework Tools. useful to development API applications.
Support for JS framework such as vuejs & vuetify.
All documentation under building.

## Installation

---

#### Using composer: 
    
    composer require 4myth/laravel-tools

#### Publish:

    php artisan vendor:publish --provider="Myth\LaravelTools\Providers\ServiceProvider"

### Tool Middleware

- Convert Arabic numbers to English.
- `postman` use to create postman documentation.
- `permission` use to make permissions for routes. only have auth middleware

---


##### Convert Arabic numbers to English:

Edit file `app/Http/Kernel.php`.

    use Myth\LaravelTools\Http\Middleware\ArToEnMiddleware;

    protected $middleware = [
        ...Your Middleware
        ArToEnMiddleware::class
    ]

---

##### Postman documentation Example:

Define your routes. for example in : `routes/api.php`

    Route::group(['middleware' => ['postman']], function(Router $router){
        your routes here
    });

Edit `app/Http/Kernel.php` to define route middleware in kernel.

    use Myth\LaravelTools\Http\Middleware\PostmanMiddleware;

    protected $routeMiddleware = [
        ...Your Middleware
        'postman' => PostmanMiddleware::class,
    ]

---

##### Permissions Example:


Define your routes. for example in : `routes/api.php`

    Route::group(['middleware' => ['auth:sanctum', 'permission']], function(Router $router){
        Route::get('route-path', [UserController::class, 'example'])->name('example');
        Route::get('route-path', [UserController::class, 'example'])->name('Model.DeleteFile');
        Route::get('', [UserController::class, 'index'])->name('User.index');
    });

The name of the permissions depend on the route name. an example: `example` , `User.index` and `Model.DeleteFile`

Edit `app/Http/Kernel.php` to define route middleware in kernel.

    use Myth\LaravelTools\Http\Middleware\PermissionMiddleware;

    protected $routeMiddleware = [
        ...Your Middleware
        'permission' => PermissionMiddleware::class,
    ]

---

### Tool commands

- `php artisan myth:postman` Postman API documentation. Only routes has middleware `postman`
- `php artisan myth:js-lang` Export language files to JS
- `php artisan myth:model` Make crud of model

---

### Laravel File system
This tool use the laravel file system `config/filesystems.php`

Types: `root`, `app`, `setup`, `logs`, `media`, `pdf`, `excel` and `qr`

    'disks' => [
        ... your disks

        'root' => [
            'driver' => 'local',
            'root'   => base_path(),
            'throw'  => false,
        ],

        'app' => [
            'driver' => 'local',
            'root'   => app_path(),
            'throw'  => false,
        ],

        'logs' => [
            'driver' => 'local',
            'root'   => storage_path('logs'),
            'throw'  => false,
        ],

        'setup' => [
            'driver' => 'local',
            'root'   => resource_path('setup'),
            'throw'  => false,
        ],

        'media' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public/media'),
            'url'        => env('APP_URL').'/storage/media',
            'visibility' => 'public',
            'throw'      => false,
        ],

        'pdf' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public/pdf'),
            'url'        => env('APP_URL').'/storage/pdf',
            'visibility' => 'public',
            'throw'      => false,
        ],

        'excel' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public/excel'),
            'url'        => env('APP_URL').'/storage/excel',
            'visibility' => 'public',
            'throw'      => false,
        ],

        'qr' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public/qr'),
            'url'        => env('APP_URL').'/storage/qr',
            'visibility' => 'public',
            'throw'      => false,
        ],
    ]

