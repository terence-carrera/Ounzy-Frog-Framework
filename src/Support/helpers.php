<?php

use Ounzy\FrogFramework\Http\Response;
use Ounzy\FrogFramework\Core\App;
use Ounzy\FrogFramework\Core\Container;
use Ounzy\FrogFramework\Core\Config;

if (!function_exists('response')) {
    function response(): Response
    {
        return new Response();
    }
}

if (!function_exists('view')) {
    function view(string $name, array $data = []): string
    {
        $viewsRoot = __DIR__ . '/../Views/';

        // Allow composite naming: name.layout => infer layout, or scan for name.<layout>.php if name.php absent
        $layout = $data['layout'] ?? null; // explicit override
        unset($data['layout']);

        $originalName = $name;
        $compositeLayout = null;
        if (str_contains($name, '.')) {
            // Provided composite; extract layout part
            $parts = explode('.', $name);
            if (count($parts) === 2) { // e.g. hello.base
                [$baseName, $layoutName] = $parts;
                $compositeLayout = $layoutName;
                $name = $baseName . '.' . $layoutName; // keep for path resolution
            }
        }

        $path = $viewsRoot . $name . '.php';

        if (!file_exists($path)) {
            // If no direct match and no extension provided, attempt composite detection
            if (!str_contains($originalName, '.')) {
                $matches = glob($viewsRoot . $originalName . '.*.php');
                // Filter out errors folder matches
                $matches = array_filter($matches, fn($m) => !str_contains($m, DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR));
                if (count($matches) === 1) {
                    $path = array_values($matches)[0];
                    $fileBase = basename($path, '.php'); // hello.base
                    $segments = explode('.', $fileBase);
                    if (count($segments) === 2) {
                        [$bn, $ln] = $segments;
                        if (strcasecmp($bn, $originalName) === 0) {
                            $compositeLayout = $ln;
                        }
                    }
                }
            }
        }

        if (!file_exists($path)) {
            throw new RuntimeException("View {$originalName} not found");
        }

        if (!$layout && $compositeLayout) {
            $layout = 'Layout/' . strtolower($compositeLayout);
        } elseif ($layout && !str_contains($layout, '/')) {
            // treat provided layout without slash as inside Layout/
            $layout = 'Layout/' . $layout;
        }

        static $compileCache = [];
        // Load & compile with blade-like directives (per-request in-memory cache)
        $compile = function (string $phpPath, array $vars, ?string &$foundExtends = null) use (&$compileCache): string {
            if (isset($compileCache[$phpPath])) {
                // Can't reuse extends info from cache; ignore (extends only needed first pass)
                return $compileCache[$phpPath];
            }
            $raw = file_get_contents($phpPath);
            // Detect and strip @extends('Layout/base') (single usage expected)
            if (preg_match('/@extends\(\'([^\']+)\'\)/', $raw, $m)) {
                $foundExtends = $m[1];
                $raw = str_replace($m[0], '', $raw);
            }

            // Basic directive compilation
            $patterns = [
                // Escaped & unescaped echoes
                '/{{\s*(.+?)\s*}}/s' => '<?= htmlspecialchars($1, ENT_QUOTES, "UTF-8") ?>',
                '/{!!\s*(.+?)\s*!!}/s' => '<?= $1 ?>',
                // Conditionals
                '/@if\s*\((.+?)\)/' => '<?php if ($1): ?>',
                '/@elseif\s*\((.+?)\)/' => '<?php elseif ($1): ?>',
                '/@else\b/' => '<?php else: ?>',
                '/@endif\b/' => '<?php endif; ?>',
                // Loops
                '/@foreach\s*\((.+?)\)/' => '<?php foreach ($1): ?>',
                '/@endforeach\b/' => '<?php endforeach; ?>',
                '/@for\s*\((.+?)\)/' => '<?php for ($1): ?>',
                '/@endfor\b/' => '<?php endfor; ?>',
                '/@while\s*\((.+?)\)/' => '<?php while ($1): ?>',
                '/@endwhile\b/' => '<?php endwhile; ?>',
            ];

            $raw = preg_replace(array_keys($patterns), array_values($patterns), $raw);

            // @include('partial')
            $raw = preg_replace_callback('/@include\(\'([^\']+)\'\)/', function ($m) {
                $partial = $m[1];
                return "<?= view('$partial', get_defined_vars()) ?>"; // pass scope
            }, $raw);

            // Init section + push stacks (insert at beginning once compiled)
            $init = <<<'PHP'
<?php if(!isset($sections)) $sections=[]; if(!isset($__section_stack)) $__section_stack=[]; if(!isset($stacks)) $stacks=[]; if(!isset($__push_stack)) $__push_stack=[]; ?>
PHP;
            $raw = $init . $raw;

            // Sections
            $raw = preg_replace('/@section\(\'([^\']+)\'\)/', '<?php $__section_stack[] = "$1"; ob_start(); ?>', $raw);
            $raw = preg_replace('/@endsection/', '<?php $name = array_pop($__section_stack); $sections[$name] = ob_get_clean(); ?>', $raw);

            // Push stacks
            $raw = preg_replace('/@push\(\'([^\']+)\'\)/', '<?php $__push_stack[] = "$1"; ob_start(); ?>', $raw);
            $raw = preg_replace('/@endpush/', '<?php $n = array_pop($__push_stack); ($stacks[$n] ??= [])[] = ob_get_clean(); ?>', $raw);
            $raw = preg_replace_callback('/@stack\(\'([^\']+)\'\)/', function ($m) {
                $n = $m[1];
                return "<?= isset(\$stacks['$n']) ? implode('', \$stacks['$n']) : '' ?>";
            }, $raw);

            // Yields
            $raw = preg_replace_callback('/@yield\(\'([^\']+)\'(?:,\s*\'([^\']*)\')?\)/', function ($m) {
                $sec = $m[1];
                $def = $m[2] ?? '';
                $defEsc = addslashes($def);
                return "<?= isset(\$sections['$sec']) ? \$sections['$sec'] : '$defEsc' ?>";
            }, $raw);

            return $compileCache[$phpPath] = $raw;
        };

        extract($data, EXTR_OVERWRITE);
        $sections = [];
        $__section_stack = [];
        $stacks = [];
        $__push_stack = [];

        // Compile and evaluate primary view
        $foundExtends = null;
        $compiled = $compile($path, get_defined_vars(), $foundExtends);
        ob_start();
        eval('?>' . $compiled);
        $content = ob_get_clean();

        // If @extends detected and no explicit layout yet, adopt it
        if (!$layout && $foundExtends) {
            // Normalize: allow 'Layout/base' or 'Layout/base.php' or 'base'
            $extends = str_replace('\\', '/', $foundExtends);
            $extends = preg_replace('~\.php$~', '', $extends);
            if (!str_contains($extends, '/')) {
                $extends = 'Layout/' . $extends;
            }
            $layout = $extends;
        }

        if ($layout) {
            $layoutPath = $viewsRoot . $layout . '.php';
            if (!file_exists($layoutPath)) {
                throw new RuntimeException("Layout {$layout} not found");
            }
            // Auto-fill common sections if not explicitly defined
            if (!isset($sections['content']) && isset($content)) $sections['content'] = $content;
            if (!isset($sections['header']) && isset($heading)) {
                $sections['header'] = '<h1>' . htmlspecialchars($heading ?? ($title ?? 'Frog Framework'), ENT_QUOTES, 'UTF-8') . '</h1>';
            }
            if (!isset($sections['footer'])) {
                $sections['footer'] = '<p>Powered by Frog Framework</p>';
            }
            // Provide $content, $sections within layout; also compile layout for directives
            $compiledLayout = $compile($layoutPath, get_defined_vars());
            ob_start();
            eval('?>' . $compiledLayout);
            return ob_get_clean();
        }
        return $content;
    }
}

// Simple helper to render a view with the base layout quickly
if (!function_exists('layout_view')) {
    function layout_view(string $name, array $data = [], string $layout = 'Layout/base'): string
    {
        $data['layout'] = $layout;
        return view($name, $data);
    }
}

if (!function_exists('app')) {
    function app(): App
    {
        return App::getInstance();
    }
}

if (!function_exists('container')) {
    function container(): Container
    {
        return app()->container();
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }
}

if (!function_exists('config')) {
    function config(string $key = null, mixed $default = null): mixed
    {
        /** @var Config $cfg */
        $cfg = container()->has(Config::class) ? container()->make(Config::class) : null;
        if (!$cfg) return $default;
        if ($key === null) return $cfg->all();
        return $cfg->get($key, $default);
    }
}

if (!function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        return app()->router()->url($name, $params);
    }
}

if (!function_exists('asset')) {
    /**
     * Generate a URL to a public asset.
     * Accepts paths relative to public root; trims leading slashes; preserves query/hash.
     */
    function asset(string $path, bool $bust = true): string
    {
        $path = ltrim($path, '/');
        // If already absolute (http/https) just return
        if (preg_match('~^https?://~i', $path)) return $path;
        // Basic base URL detection (can extend via config later)
        $base = rtrim($_SERVER['FROG_BASE_URL'] ?? '', '/');
        $url = ($base === '') ? '/' . $path : $base . '/' . $path;
        if (!$bust) return $url;
        if (str_contains($path, '?')) return $url; // already has query
        // Try mtime versioning from public directory
        $root = dirname(__DIR__, 2); // project root
        $publicFile = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
        if (is_file($publicFile)) {
            $mtime = @filemtime($publicFile);
            if ($mtime) {
                $url .= '?v=' . $mtime;
            }
        }
        return $url;
    }
}
