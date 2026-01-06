<?php

namespace JacquesTredoux\VueLoginFlow\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-login-flow:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Vue Login Flow files (Vue component, controller, middleware, routes)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $config = config('vue-login-flow');

        if (!$config) {
            $this->error('Configuration not found. Please run: composer require jacquestredoux/vue-login-flow');
            return Command::FAILURE;
        }

        $guardName = $config['guard_name'];
        $model = $config['model'];
        $urlPrefix = $config['url_prefix'];
        $routePrefix = $config['route_prefix'] ?? ltrim($urlPrefix, '/');
        $createLoginPage = $config['create_login_page'] ?? true;
        $createMiddleware = $config['create_middleware'] ?? false;

        // Extract model class name from full namespace
        $modelClass = class_basename($model);
        $namespace = Str::studly($guardName);
        $controllerClass = 'LoginController';
        $middlewareClass = Str::studly($guardName) . 'HandleInertiaRequest';

        $this->info("Installing Vue Login Flow for guard: {$guardName}");

        // Copy Vue component
        if ($createLoginPage) {
            $this->copyVueComponent($guardName, $urlPrefix);
        }

        // Copy controller
        $this->copyController($namespace, $controllerClass, $guardName, $model, $urlPrefix);

        // Copy middleware
        if ($createMiddleware) {
            $this->copyMiddleware($middlewareClass, $guardName);
            $this->registerMiddleware($middlewareClass);
        }

        // Add routes
        $this->addRoutes($guardName, $urlPrefix, $routePrefix, $namespace, $controllerClass, $createMiddleware, $middlewareClass);

        $this->info('Installation complete!');
        $this->info('Next steps:');
        $this->info('1. Run: npm run dev (to compile Vue assets)');
        $this->info("2. Visit: {$urlPrefix}/login");

        return Command::SUCCESS;
    }

    protected function copyVueComponent(string $guardName, string $urlPrefix): void
    {
        $source = __DIR__ . '/../Resources/js/Pages/Login.vue';
        $destination = resource_path("js/Pages/{$guardName}/Login.vue");

        if (!File::exists(dirname($destination))) {
            File::makeDirectory(dirname($destination), 0755, true);
        }

        if (File::exists($destination)) {
            if (!$this->confirm("File {$destination} already exists. Overwrite?", true)) {
                return;
            }
        }

        $content = File::get($source);
        $content = str_replace('{GUARD_NAME}', $guardName, $content);
        $content = str_replace('{URL_PREFIX}', $urlPrefix, $content);

        File::put($destination, $content);
        $this->info("✓ Vue component copied to: {$destination}");
    }

    protected function copyController(string $namespace, string $controllerClass, string $guardName, string $model, string $urlPrefix): void
    {
        $source = __DIR__ . '/../Controllers/LoginController.php';
        $destination = app_path("Http/Controllers/{$namespace}/{$controllerClass}.php");

        if (!File::exists(dirname($destination))) {
            File::makeDirectory(dirname($destination), 0755, true);
        }

        if (File::exists($destination)) {
            if (!$this->confirm("File {$destination} already exists. Overwrite?", true)) {
                return;
            }
        }

        $content = File::get($source);
        $content = str_replace('{NAMESPACE}', $namespace, $content);
        $content = str_replace('{CONTROLLER_CLASS}', $controllerClass, $content);
        $content = str_replace('{GUARD_NAME}', $guardName, $content);
        $content = str_replace('{MODEL_CLASS}', $model, $content);
        $content = str_replace('{URL_PREFIX}', $urlPrefix, $content);

        File::put($destination, $content);
        $this->info("✓ Controller copied to: {$destination}");
    }

    protected function copyMiddleware(string $middlewareClass, string $guardName): void
    {
        $source = __DIR__ . '/../Middleware/HandleInertiaRequest.php';
        $destination = app_path("Http/Middleware/{$middlewareClass}.php");

        if (!File::exists(dirname($destination))) {
            File::makeDirectory(dirname($destination), 0755, true);
        }

        if (File::exists($destination)) {
            if (!$this->confirm("File {$destination} already exists. Overwrite?", true)) {
                return;
            }
        }

        $content = File::get($source);
        $content = str_replace('{MIDDLEWARE_CLASS}', $middlewareClass, $content);
        $content = str_replace('{GUARD_NAME}', $guardName, $content);

        File::put($destination, $content);
        $this->info("✓ Middleware copied to: {$destination}");
    }

    protected function registerMiddleware(string $middlewareClass): void
    {
        $kernelPath = app_path('Http/Kernel.php');

        if (!File::exists($kernelPath)) {
            $this->warn("Kernel.php not found. Please manually register the middleware: {$middlewareClass}");
            return;
        }

        $content = File::get($kernelPath);
        $aliasName = Str::kebab($middlewareClass);

        // Check if already registered
        if (strpos($content, $middlewareClass) !== false) {
            $this->info("✓ Middleware already registered in Kernel.php");
            return;
        }

        // Add to middlewareAliases
        $pattern = "/protected\s+\$middlewareAliases\s*=\s*\[/";
        $replacement = "protected \$middlewareAliases = [\n        '{$aliasName}' => \\App\\Http\\Middleware\\{$middlewareClass}::class,";

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content, 1);
            File::put($kernelPath, $content);
            $this->info("✓ Middleware registered in Kernel.php as: {$aliasName}");
        } else {
            $this->warn("Could not auto-register middleware. Please add to Kernel.php manually.");
        }
    }

    protected function addRoutes(string $guardName, string $urlPrefix, string $routePrefix, string $namespace, string $controllerClass, bool $createMiddleware, string $middlewareClass): void
    {
        $routesPath = base_path('routes/web.php');

        if (!File::exists($routesPath)) {
            $this->warn("routes/web.php not found. Please add routes manually.");
            return;
        }

        $content = File::get($routesPath);

        // Check if routes already exist
        $routePattern = "/Route::.*{$urlPrefix}\/login/";
        if (preg_match($routePattern, $content)) {
            if (!$this->confirm("Routes for {$urlPrefix} already exist. Add anyway?", false)) {
                return;
            }
        }

        $middlewareAlias = $createMiddleware ? Str::kebab($middlewareClass) : null;
        $middlewareLine = $middlewareAlias ? "->middleware('{$middlewareAlias}')" : '';

        $routes = "\n// Vue Login Flow routes for {$guardName} guard\n";
        $routes .= "Route::prefix('{$routePrefix}')->group(function () {\n";
        $routes .= "    Route::get('/login', [\\App\\Http\\Controllers\\{$namespace}\\{$controllerClass}::class, 'showLoginForm'])->name('{$guardName}.login');\n";
        $routes .= "    Route::post('/login', [\\App\\Http\\Controllers\\{$namespace}\\{$controllerClass}::class, 'login'])->name('{$guardName}.login.post');\n";
        $routes .= "    Route::post('/logout', [\\App\\Http\\Controllers\\{$namespace}\\{$controllerClass}::class, 'logout'])->name('{$guardName}.logout');\n";
        $routes .= "}){$middlewareLine};\n";

        File::append($routesPath, $routes);
        $this->info("✓ Routes added to routes/web.php");
    }
}

