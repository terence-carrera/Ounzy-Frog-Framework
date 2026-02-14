@section('header')
<div class="docs-topbar">
    <div class="docs-topbar-inner page-container">
            <div class="docs-brand">
                <span class="docs-badge">Frog</span>
                <span>No Bullshit. Just Pure Framework</span>
            </div>
        <div class="docs-actions">
            <a href="/">Home</a>
            <a href="https://github.com" target="_blank" rel="noopener">GitHub</a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="docs-shell page-container">
    <aside class="docs-nav">
        <h4>Getting Started</h4>
        <a href="#quickstart">Quick Start</a>
        <a href="#routing">Routing</a>
        <a href="#controllers">Controllers</a>
        <a href="#views">Views</a>
        <h4>Core Features</h4>
        <a href="#middleware">Middleware</a>
        <a href="#security">Security</a>
        <a href="#requests">Requests</a>
        <a href="#responses">Responses</a>
        <a href="#di">Dependency Injection</a>
        <a href="#services">Services</a>
        <a href="#extensibility">Extensibility</a>
        <a href="#config">Configuration</a>
        <a href="#errors">Error Handling</a>
        <a href="#testing">Testing</a>
        <a href="#cli">CLI</a>
        <a href="#deployment">Deployment</a>
    </aside>

    <div class="docs-main">
        <section id="quickstart" class="docs-hero">
            <h1>Frog Framework</h1>
            <p>Small, readable PHP framework focused on routing, middleware, DI, and clean views. This guide covers the essentials in one page.</p>
        </section>

        <div class="card-grid">
            <div class="card">
                <h3>Install</h3>
                <p>Use Composer to install dependencies.</p>
            </div>
            <div class="card">
                <h3>Run</h3>
                <p>Start the dev server with a single command.</p>
            </div>
            <div class="card">
                <h3>Ship</h3>
                <p>Keep the core tiny and extend when needed.</p>
            </div>
        </div>

        <pre><code>composer install
composer start</code></pre>

        <section id="routing">
            <h2>Routing</h2>
            <p>Routes live in <span class="token">bootstrap/routes.php</span>. Use closures or controllers and name routes for URL generation. You can also group routes with prefixes and middleware.</p>
            <pre><code>Route::get('/hello/{name}', function ($request, $params) {
    return response()->html('Hello ' . htmlspecialchars($params['name']));
})->name('hello');</code></pre>
        </section>

        <section id="controllers">
            <h2>Controllers</h2>
            <p>Controllers are plain classes. Constructor dependencies are auto-resolved from the container, keeping handlers clean and testable.</p>
            <pre><code>class AboutController {
    public function __construct(private Logger $log) {}

    public function index(Request $req): Response {
        $this->log->info('about viewed');
        return response()->html(view('about.base'));
    }
}

Route::get('/about', [AboutController::class, 'index']);</code></pre>
        </section>

        <section id="views">
            <h2>Views</h2>
            <p>Views are simple PHP templates. Use <span class="token">name.layout.php</span> to auto-apply layouts or declare <span class="token">@extends</span> for explicit layout control.</p>
            <pre><code>// framework/Views/about.base.php
@section('content')
    &lt;h2&gt;About Frog&lt;/h2&gt;
@endsection</code></pre>
        </section>

        <section id="middleware">
            <h2>Middleware</h2>
            <p>Middleware sits in the request pipeline and can short-circuit requests. Global middleware runs before route middleware in the order registered.</p>
            <pre><code>class LogMiddleware implements MiddlewareInterface {
    public function handle(Request $r, callable $next): Response {
        error_log('Incoming: ' . $r->path());
        return $next($r);
    }
}

$router->middleware([LogMiddleware::class]);</code></pre>
        </section>

        <section id="security">
            <h2>Security</h2>
            <div class="callout">CSRF is enabled by default for unsafe methods. Add <span class="token">&lt;?= csrf_field() ?&gt;</span> inside your forms.</div>
            <p>Sessions are started by middleware and configured in <span class="token">config/session.php</span>. You can disable CSRF in <span class="token">config/security.php</span>.</p>
        </section>

        <section id="requests">
            <h2>Requests</h2>
            <p>Use the <span class="token">Request</span> object for method, path, input, and headers.</p>
            <pre><code>$method = $request->method();
$path = $request->path();
$token = $request->header('X-API-TOKEN');
$name = $request->input('name');</code></pre>
        </section>

        <section id="responses">
            <h2>Responses</h2>
            <p>Responses are fluent and can return HTML or JSON with status codes.</p>
            <pre><code>return response()
    ->status(201)
    ->json(['ok' => true]);</code></pre>
        </section>

        <section id="di">
            <h2>Dependency Injection</h2>
            <p>Type-hinted constructor dependencies are auto-resolved. You can also bind services manually.</p>
            <pre><code>app()->container()->singleton(
    Frog\App\Services\Cache::class,
    fn() => new Frog\App\Services\Cache()
);</code></pre>
        </section>

        <section id="services">
            <h2>Services</h2>
            <p>Reusable services live in <span class="token">framework/App/Services</span> and are injected into controllers or middleware.</p>
            <pre><code>class GreetingService {
    public function greet(string $name): string {
        return "Hello {$name}";
    }
}</code></pre>
        </section>

        <section id="extensibility">
            <h2>Extensibility</h2>
            <p>Frog is intentionally small. Extend it by layering features instead of modifying core classes.</p>
            <pre><code>// 1) Bind a new service
app()->container()->singleton(
    Frog\App\Services\Mailer::class,
    fn() => new Frog\App\Services\Mailer()
);

// 2) Add middleware globally
$router->middleware([
    Frog\Http\Middleware\LoggingMiddleware::class,
]);

// 3) Add a feature module (example)
// framework/App/Modules/FeatureX with routes, services, and views
</code></pre>
            <p>Prefer composition: add middleware, services, and modules; keep the core minimal.</p>
        </section>

        <section id="config">
            <h2>Configuration</h2>
            <p>All files in <span class="token">config/</span> are loaded on bootstrap and available via <span class="token">config('key')</span>.</p>
            <pre><code>// config/app.php
return [
    'name' =&gt; env('APP_NAME', 'Frog'),
    'debug' =&gt; filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
];</code></pre>
        </section>

        <section id="errors">
            <h2>Error Handling</h2>
            <p>In debug mode, errors render a detailed trace page. In production, a friendly error view is returned.</p>
            <pre><code>APP_ENV=production
APP_DEBUG=0</code></pre>
        </section>

        <section id="testing">
            <h2>Testing</h2>
            <p>Basic tests live in <span class="token">tests/</span>. Run them via the CLI command.</p>
            <pre><code>php frog test</code></pre>
        </section>

        <section id="cli">
            <h2>CLI</h2>
            <p>Use the <span class="token">frog</span> CLI for scaffolding and convenience commands.</p>
            <pre><code>php frog list
php frog route:list
php frog make:controller About</code></pre>
        </section>

        <section id="deployment">
            <h2>Deployment</h2>
            <p>Point your web server at <span class="token">public/</span> and ensure your environment variables are set. Disable debug in production.</p>
        </section>
    </div>

</div>
@endsection

@section('footer')
<div class="docs-footer">
    <div class="docs-footer-inner page-container">Frog Framework docs inspired by modern PHP framework documentation.</div>
</div>
@endsection