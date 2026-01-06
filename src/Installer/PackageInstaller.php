<?php

namespace JacquesTredoux\VueLoginFlow\Installer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvents;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class PackageInstaller implements PluginInterface, EventSubscriberInterface
{
    protected $composer;
    protected $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // Nothing to do
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // Nothing to do
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'onPostInstall',
            ScriptEvents::POST_UPDATE_CMD => 'onPostUpdate',
        ];
    }

    public function onPostInstall(Event $event): void
    {
        $this->install();
    }

    public function onPostUpdate(Event $event): void
    {
        $this->install();
    }

    protected function install(): void
    {
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        $rootDir = dirname($vendorDir);

        // Check if this is a Laravel project
        if (!file_exists($rootDir . '/artisan')) {
            return;
        }

        // Check if already configured
        if (file_exists($rootDir . '/config/vue-login-flow.php')) {
            return;
        }

        $this->io->write('<info>Setting up Vue Login Flow...</info>');

        // Prompt for configuration
        $model = $this->io->ask('Enter the Model name (e.g., User, Admin): ', 'User');
        $guardName = $this->io->ask('Enter the Guard name (e.g., admin, staff): ', 'admin');
        $urlPrefix = $this->io->ask('Enter the URL prefix (e.g., /admin, /staff): ', '/admin');
        $createLoginPage = $this->io->askConfirmation('Do you want to create a Login page? (yes/no) [yes]: ', true);
        $createMiddleware = $this->io->askConfirmation('Do you want to create a HandleInertiaRequest Middleware? (yes/no) [no]: ', false);

        // Ensure URL prefix starts with /
        if (substr($urlPrefix, 0, 1) !== '/') {
            $urlPrefix = '/' . $urlPrefix;
        }

        // Remove leading slash for route prefix
        $routePrefix = ltrim($urlPrefix, '/');

        // Save configuration
        $config = [
            'model' => 'App\\Models\\' . $model,
            'guard_name' => $guardName,
            'url_prefix' => $urlPrefix,
            'route_prefix' => $routePrefix,
            'create_login_page' => $createLoginPage,
            'create_middleware' => $createMiddleware,
            'auth_type' => 'session',
        ];

        $this->writeConfigFile($rootDir, $config);
        $this->updateAuthConfig($rootDir, $guardName, $model);

        $this->io->write('<info>Configuration saved! Run: php artisan vue-login-flow:install</info>');
    }

    protected function writeConfigFile(string $rootDir, array $config): void
    {
        $configPath = $rootDir . '/config/vue-login-flow.php';
        $configContent = "<?php\n\nreturn [\n";
        
        foreach ($config as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } else {
                $value = "'" . addslashes($value) . "'";
            }
            $configContent .= "    '{$key}' => {$value},\n";
        }
        
        $configContent .= "];\n";

        if (!is_dir(dirname($configPath))) {
            mkdir(dirname($configPath), 0755, true);
        }

        file_put_contents($configPath, $configContent);
    }

    protected function updateAuthConfig(string $rootDir, string $guardName, string $model): void
    {
        $authConfigPath = $rootDir . '/config/auth.php';
        
        if (!file_exists($authConfigPath)) {
            return;
        }

        $content = file_get_contents($authConfigPath);
        
        // Check if guard already exists
        if (strpos($content, "'{$guardName}'") !== false) {
            return;
        }
        
        // Add guard to guards array
        $guardPattern = "/(\'guards\'\s*=>\s*\[)/";
        $guardReplacement = "$1\n        '{$guardName}' => [\n            'driver' => 'session',\n            'provider' => '{$guardName}',\n        ],";
        
        if (preg_match($guardPattern, $content)) {
            $content = preg_replace($guardPattern, $guardReplacement, $content, 1);
        }
        
        // Add provider to providers array
        $providerPattern = "/(\'providers\'\s*=>\s*\[)/";
        $providerReplacement = "$1\n        '{$guardName}' => [\n            'driver' => 'eloquent',\n            'model' => App\\Models\\{$model}::class,\n        ],";
        
        if (preg_match($providerPattern, $content)) {
            $content = preg_replace($providerPattern, $providerReplacement, $content, 1);
        }

        file_put_contents($authConfigPath, $content);
    }
}

