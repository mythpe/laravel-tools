## MyTh Laravel tools

Laravel framework Tools. useful to development API applications.
Support for JS framework such as vuejs & vuetify.
All documentation under building.

## Tool Middleware

- Convert Arabic numbers to English.
- `postman` used for create postman documentation.
- `permission` make permissions for routes. Only have auth middleware

### Examples:

#### Convert Arabic numbers to English:

Edit file `app/Http/Kernel.php`.

    use Myth\LaravelTools\Http\Middleware\ArToEnMiddleware;

    protected $middleware = [
        ...Your Middleware
        ArToEnMiddleware::class
    ]

#### Postman documentation Example:

Define your routes. for example in : `routes/api.php`

    Route::group(['middleware' => ['postman']], function(Router $router){
        your routes here
    });

edit `app/Http/Kernel.php` to define route middleware in kernel.

    use Myth\LaravelTools\Http\Middleware\PostmanMiddleware;

    protected $routeMiddleware = [
        ...Your Middleware
        'postman' => PostmanMiddleware::class,
    ]

#### Permissions Example:


Define your routes. for example in : `routes/api.php`

    Route::group(['middleware' => ['auth:sanctum', 'permission']], function(Router $router){
        Route::get('route-path', [UserController::class, 'example'])->name('example');
        Route::get('route-path', [UserController::class, 'example'])->name('Model.DeleteFile');
        Route::get('', [UserController::class, 'index'])->name('User.index');
    });

The name of the permissions depend on the route name. an example: `example` , `User.index` and `Model.DeleteFile`

edit `app/Http/Kernel.php` to define route middleware in kernel.

    use Myth\LaravelTools\Http\Middleware\PermissionMiddleware;

    protected $routeMiddleware = [
        ...Your Middleware
        'permission' => PermissionMiddleware::class,
    ]



## Tool commands

- `php artisan myth:postman` Postman API documentation. Only routes has middleware `postman`
- `php artisan myth:js-lang` Export language files to JS

### Laravel File system
This tool uses the laravel file system `config/filesystems.php`

Types: `root`, `setup`, `media`, `pdf`, `excel` and `qr`

    'disks' => [
        ... your disks

        'root' => [
            'driver' => 'local',
            'root'   => base_path(),
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

