<?php

namespace Ounzy\FrogFramework\Console\Commands;

use Ounzy\FrogFramework\Console\Command;

class Scaffold extends Command
{
    protected string $signature = 'make:scaffold';
    protected string $description = 'Generate controller, service, and view for a given name';

    public function handle(array $arguments = []): int
    {
        $name = $arguments[0] ?? null;
        if (!$name) {
            $this->error('Usage: frog make:scaffold Name');
            return 1;
        }

        $studly = str_replace(' ', '', ucwords(preg_replace('/[_-]+/', ' ', $name)));
        $controller = $studly . 'Controller';
        $service = $studly . 'Service';
        $view = strtolower($studly);

        // Simple confirmation helper
        $confirm = function (string $question): bool {
            $this->line($question . ' [y/N]: ');
            $answer = strtolower(trim(fgets(STDIN)));
            return in_array($answer, ['y', 'yes']);
        };

        // Project root (Frog)
        $projectRoot = dirname(__DIR__, 3);
        // src directory
        $srcDir = dirname(__DIR__, 2);

        // Service (inside src)
        $serviceDir = $srcDir . '/Services';
        if (!is_dir($serviceDir)) {
            mkdir($serviceDir, 0777, true);
        }
        $servicePath = $serviceDir . '/' . $service . '.php';
        $serviceCode = <<<PHP
    <?php

    namespace Ounzy\FrogFramework\Services;

    class {$service}
    {
        public function example(): string
        {
        return '{$studly} service working';
        }
    }
    PHP;
        if (!file_exists($servicePath)) {
            file_put_contents($servicePath, $serviceCode);
            $this->info('Created service: ' . $servicePath);
        } else {
            $this->warn('Service exists: ' . $servicePath);
            if ($confirm('Overwrite service file?')) {
                file_put_contents($servicePath, $serviceCode);
                $this->info('Overwritten service: ' . $servicePath);
            }
        }

        // View (inside src/Views)
        $viewDir = $srcDir . '/Views';
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0777, true);
        }
        $viewPath = $viewDir . '/' . $view . '.php';
        $viewHtml = <<<HTML
    <h1>{$studly} View</h1>
    <p>Generated scaffold.</p>
    <p>Service example output: <?= htmlspecialchars(
        app()->container()->make(Ounzy\FrogFramework\Services\\$service::class)->example(), ENT_QUOTES, 'UTF-8'
    ) ?></p>
    HTML;
        if (!file_exists($viewPath)) {
            file_put_contents($viewPath, $viewHtml);
            $this->info('Created view: ' . $viewPath);
        } else {
            $this->warn('View exists: ' . $viewPath);
            if ($confirm('Overwrite view file?')) {
                file_put_contents($viewPath, $viewHtml);
                $this->info('Overwritten view: ' . $viewPath);
            }
        }

        // Controller (inside src)
        $controllerDir = $srcDir . '/Controllers';
        if (!is_dir($controllerDir)) {
            mkdir($controllerDir, 0777, true);
        }
        $controllerPath = $controllerDir . '/' . $controller . '.php';
        $controllerCode = <<<PHP
    <?php

    namespace Ounzy\FrogFramework\Controllers;

    use Ounzy\FrogFramework\Http\Request;
    use Ounzy\FrogFramework\Http\Response;
    use Ounzy\FrogFramework\Services\{$service};

    class {$controller}
    {
        public function __construct(protected {$service} \$service) {}

        public function index(Request \$request): Response
        {
        return response()->html(view('{$view}', [
            'service' => \$this->service->example(),
        ]));
        }
    }
    PHP;
        if (!file_exists($controllerPath)) {
            file_put_contents($controllerPath, $controllerCode);
            $this->info('Created controller: ' . $controllerPath);
        } else {
            $this->warn('Controller exists: ' . $controllerPath);
            if ($confirm('Overwrite controller file?')) {
                file_put_contents($controllerPath, $controllerCode);
                $this->info('Overwritten controller: ' . $controllerPath);
            }
        }
        // Auto-add route to bootstrap/routes.php using Route facade
        $routesFile = $projectRoot . '/bootstrap/routes.php';
        $routeLine = "Route::get('/{$view}', [\\Ounzy\\FrogFramework\\Controllers\\{$controller}::class, 'index'])->name('{$view}');";
        if (is_file($routesFile) && is_writable($routesFile)) {
            $routesContent = file_get_contents($routesFile) ?: '';
            if (!str_contains($routesContent, $routeLine)) {
                // Ensure Route facade imported
                if (!str_contains($routesContent, 'use Ounzy\\FrogFramework\\Routing\\Route;')) {
                    // Insert after last existing use statement
                    $routesLines = explode("\n", $routesContent);
                    $lastUseIndex = -1;
                    foreach ($routesLines as $i => $l) {
                        if (preg_match('/^use\\s+.+;$/', trim($l))) {
                            $lastUseIndex = $i;
                        }
                    }
                    if ($lastUseIndex >= 0) {
                        array_splice($routesLines, $lastUseIndex + 1, 0, 'use Ounzy\\FrogFramework\\Routing\\Route;');
                        $routesContent = implode("\n", $routesLines);
                    } else {
                        // Fallback: add after opening tag
                        $routesContent = preg_replace('/<\?php/', "<?php\nuse Ounzy\\FrogFramework\\Routing\\Route;", $routesContent, 1);
                    }
                }
                // Append route at end
                if (!str_ends_with($routesContent, "\n")) {
                    $routesContent .= "\n";
                }
                $routesContent .= $routeLine . "\n";
                file_put_contents($routesFile, $routesContent);
                $this->info('Route added to bootstrap/routes.php');
            } else {
                $this->warn('Route already exists in routes file');
            }
        } else {
            $this->warn('Could not modify routes file to add route automatically');
            $this->line('Add this route manually:');
            $this->line('  ' . $routeLine);
        }

        return 0;
    }
}
