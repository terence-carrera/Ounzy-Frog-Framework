<?php

namespace Ounzy\FrogFramework\Console\Commands;

use Ounzy\FrogFramework\Console\Command;

class MakeController extends Command
{
    protected string $signature = 'make:controller';
    protected string $description = 'Generate a new controller class';

    public function handle(array $arguments = []): int
    {
        if (empty($arguments)) {
            $this->error('Controller name required. Usage: frog make:controller Name [--resource]');
            return 1;
        }

        // Extract flags
        $flags = array_filter($arguments, fn($a) => str_starts_with($a, '--'));
        $argsOnly = array_values(array_filter($arguments, fn($a) => !str_starts_with($a, '--')));
        $rawName = $argsOnly[0] ?? null;
        $isResource = in_array('--resource', $flags, true);

        if (!$rawName) {
            $this->error('Controller name missing.');
            return 1;
        }

        // Normalize separators and StudlyCase segments
        $segments = preg_split('#[\\\\/]+#', trim($rawName, '\\//'));
        $segments = array_map(function ($seg) {
            // Remove invalid chars
            $seg = preg_replace('/[^A-Za-z0-9_]/', '', $seg);
            return str_replace(' ', '', ucwords(preg_replace('/[_-]+/', ' ', $seg)));
        }, $segments);
        if (!$segments) {
            $this->error('Invalid controller name.');
            return 1;
        }
        $last = array_pop($segments);
        if (!str_ends_with($last, 'Controller')) {
            $last .= 'Controller';
        }
        $className = $last;
        $namespaceBase = 'Ounzy\\FrogFramework\\Controllers';
        $subNamespace = $segments ? ('\\' . implode('\\', $segments)) : '';
        $namespace = $namespaceBase . $subNamespace;

        $baseDir = realpath(__DIR__ . '/../../../src/Controllers') ?: (__DIR__ . '/../../../src/Controllers');
        $targetDir = $baseDir . ($segments ? ('/' . implode('/', $segments)) : '');
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
            $this->error('Failed to create directory: ' . $targetDir);
            return 1;
        }
        $path = $targetDir . '/' . $className . '.php';
        if (file_exists($path)) {
            $this->error('Controller already exists: ' . $namespace . '\\' . $className);
            return 1;
        }

        $body = $isResource ? $this->resourceMethods() : $this->defaultMethod($className);

        $template = <<<PHP
<?php

namespace $namespace;

use Ounzy\\FrogFramework\\Http\\Request;
use Ounzy\\FrogFramework\\Http\\Response;

class $className
{
$body
}
PHP;
        file_put_contents($path, $template);
        $this->info('Controller created: ' . $path);
        if ($isResource) {
            $this->line("Add routes, e.g.:\n  $" . "router->get('/resource', [{$namespace}\\{$className}::class, 'index']);");
        }
        return 0;
    }

    protected function defaultMethod(string $className): string
    {
        $code = "    public function index(Request $" . "request): Response\n";
        $code .= "    {\n        return response()->html('Controller {$className} index action');\n    }\n";
        return $code;
    }

    protected function resourceMethods(): string
    {
        return <<<'CODE'
    public function index(Request $request): Response
    {
        return response()->html('Listing resources');
    }

    public function create(Request $request): Response
    {
        return response()->html('Show create form');
    }

    public function store(Request $request): Response
    {
        return response()->json(['status' => 'stored']);
    }

    public function show(Request $request): Response
    {
        return response()->html('Show single resource');
    }

    public function edit(Request $request): Response
    {
        return response()->html('Show edit form');
    }

    public function update(Request $request): Response
    {
        return response()->json(['status' => 'updated']);
    }

    public function destroy(Request $request): Response
    {
        return response()->json(['status' => 'deleted']);
    }
CODE;
    }
}
