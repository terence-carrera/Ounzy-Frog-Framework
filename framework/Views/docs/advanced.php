@extends('docs.base')

@section('content')
<section id="changelog">
    <h2>Changelog</h2>
    <p>Stay current with recent improvements and fixes.</p>
    <pre><code>Current version: v<?= htmlspecialchars(config('app.version', '0.1.0'), ENT_QUOTES, 'UTF-8') ?></code></pre>
    <ul>
        <li>SQL Server support via <span class="token">sqlsrv</span></li>
        <li>New <span class="token">db:check</span> command</li>
        <li>Expanded docs with beginner-first walkthroughs and recipes</li>
    </ul>
</section>

<section id="mail">
    <h2>Mail</h2>
    <p>Mail drivers live in <span class="token">config/mail.php</span>. Send mail using <span class="token">mailer()</span>.</p>
    <pre><code>mailer()->send(
    'user@example.com',
    'Welcome',
    'Thanks for trying Frog!'
);</code></pre>
    <p>HTML templates and attachments are supported through <span class="token">sendMessage()</span> or <span class="token">sendHtml()</span>.</p>
    <pre><code>$html = view('emails.welcome', ['name' => 'Jamie']);

mailer()->sendHtml(
    'user@example.com',
    'Welcome',
    $html
);

mailer()->sendMessage(
    'user@example.com',
    'Invoice',
    'Please see the attached PDF.',
    $html,
    [
        'storage/invoices/2024-001.pdf',
        ['path' => 'storage/reports/summary.csv', 'name' => 'summary.csv'],
    ]
);</code></pre>
    <p>Use SMTP by setting <span class="token">MAIL_DRIVER=smtp</span> and configuring host/port/credentials.</p>
    <pre><code>MAIL_DRIVER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=
MAIL_PASSWORD=</code></pre>
    <p>If you are just testing locally, the <span class="token">mail</span> driver will use PHP's built-in mail setup.</p>
</section>

<section id="keycloak">
    <h2>Keycloak</h2>
    <p>Enable Keycloak in <span class="token">config/keycloak.php</span> and add <span class="token">KeycloakAuthMiddleware</span> to your routes.</p>
    <pre><code>$router->middleware([
    Frog\Http\Middleware\KeycloakAuthMiddleware::class,
]);</code></pre>
    <p>Requests must send an <span class="token">Authorization: Bearer</span> token. JWKS keys are cached via the configured cache store.</p>
    <p>When disabled, the middleware is a no-op, so you can keep the same routes across environments.</p>
</section>

<section id="use-cases">
    <h2>Use Cases</h2>
    <p>These examples are intentionally small so you can copy them as a starting point. Mix and match as your app grows.</p>
    <h3>Blog CRUD</h3>
    <pre><code>// routes
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::post('/posts', [PostController::class, 'store']);

// controller (simplified)
class PostController {
    public function index(): Response {
        $posts = db()->select('SELECT * FROM posts ORDER BY created_at DESC');
        return response()->html(view('posts.index', ['posts' => $posts]));
    }
}
</code></pre>
    <h3>API-Only Service</h3>
    <pre><code>Route::get('/api/health', fn() => response()->json(['ok' => true]));

Route::post('/api/posts', [ApiPostController::class, 'store'], [
    Frog\Http\Middleware\BearerTokenMiddleware::class,
]);</code></pre>
    <p>Generate and store your API token with:</p>
    <pre><code>php frog make:api-token --length=32</code></pre>
    <h3>Admin Dashboard (HTML)</h3>
    <pre><code>Route::get('/admin', [AdminController::class, 'index'], [
    Frog\Http\Middleware\AuthMiddleware::class,
]);</code></pre>
    <h3>Email Notifications</h3>
    <pre><code>mailer()->send(
    'user@example.com',
    'Welcome',
    'Thanks for signing up!'
);</code></pre>
    <h3>Caching for Performance</h3>
    <pre><code>$key = 'posts.latest';
$posts = cache()->get($key);
if (!$posts) {
    $posts = db()->select('SELECT * FROM posts ORDER BY created_at DESC LIMIT 10');
    cache()->set($key, $posts, 60);
}</code></pre>
</section>

<section id="troubleshooting">
    <h2>Troubleshooting</h2>
    <p>Common issues and quick fixes.</p>
    <pre><code># .env missing
# Fix: create .env from .env.example and set APP_KEY/DB settings as needed.

# SQL Server connection fails
# Fix: enable pdo_sqlsrv + sqlsrv extensions, then run: php frog db:check sqlsrv

# CSRF 419 error
# Fix: include csrf_field() in HTML forms or send X-CSRF-TOKEN header for JSON.

# 404 route not found
# Fix: confirm the route exists in bootstrap/routes.php and restart the dev server.</code></pre>
</section>

<section id="best-practices">
    <h2>Best Practices</h2>
    <ul>
        <li>Keep controllers thin and move business logic into services.</li>
        <li>Always validate and sanitize input before writing to the database.</li>
        <li>Use prepared statements with <span class="token">db()</span> to avoid SQL injection.</li>
        <li>Keep view templates free of business logic.</li>
        <li>Prefer configuration in <span class="token">.env</span> over hard-coded values.</li>
    </ul>
</section>

<section id="recipes">
    <h2>Recipes</h2>
    <h3>Pagination</h3>
    <pre><code>$page = (int)$request->query('page', 1);
$perPage = 10;
$offset = ($page - 1) * $perPage;
$rows = db()->select('SELECT * FROM posts ORDER BY id DESC LIMIT ? OFFSET ?', [$perPage, $offset]);</code></pre>
    <h3>Search</h3>
    <pre><code>$term = '%' . $request->query('q', '') . '%';
$rows = db()->select('SELECT * FROM posts WHERE title LIKE ?', [$term]);</code></pre>
    <h3>JSON Error Response</h3>
    <pre><code>return response()->status(422)->json([
    'error' => 'Validation failed',
]);</code></pre>
    <h3>Bearer Token Protection</h3>
    <pre><code>Route::get('/api/me', [ApiUserController::class, 'show'], [
    Frog\Http\Middleware\BearerTokenMiddleware::class,
]);</code></pre>
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
    <p>Use <span class="token">env()</span> for environment variables and override defaults in <span class="token">.env</span>.</p>
    <pre><code># .env sample
APP_NAME="Frog Web Application"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000</code></pre>
</section>

<section id="env-reference">
    <h2>Environment Reference</h2>
    <p>Common variables you can set in <span class="token">.env</span>:</p>
    <pre><code># App
APP_NAME="Frog Web Application"
APP_VERSION=1.0.0
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Security / CSRF
CSRF_ENABLED=true
CSRF_TOKEN_KEY=_csrf_token
CSRF_HEADER=X-CSRF-TOKEN
CSRF_FIELD=_token

# Database (SQLite/MySQL/Postgres/SQL Server)
DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_INSTANCE=
DB_ENCRYPT=false
DB_TRUST_SERVER_CERT=false

# Cache
CACHE_DRIVER=file
CACHE_PATH=

# Mail
MAIL_DRIVER=mail
MAIL_HOST=127.0.0.1
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=
MAIL_PASSWORD=

# Keycloak
KEYCLOAK_ENABLED=false
KEYCLOAK_BASE_URL=http://localhost:8080
KEYCLOAK_REALM=master
KEYCLOAK_CLIENT_ID=
KEYCLOAK_CLIENT_SECRET=
KEYCLOAK_JWKS_TTL=3600
KEYCLOAK_ISSUER=</code></pre>
</section>

<section id="env-profiles">
    <h2>Sample Environment Profiles</h2>
    <p>Pick one database profile and one mail profile to get started.</p>
    <pre><code># SQLite
DB_CONNECTION=sqlite
DB_DATABASE=storage/database.sqlite

# MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=frog
DB_USERNAME=root
DB_PASSWORD=

# PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=frog
DB_USERNAME=postgres
DB_PASSWORD=

# SQL Server
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=frog
DB_USERNAME=sa
DB_PASSWORD=secret
DB_INSTANCE=
DB_ENCRYPT=false
DB_TRUST_SERVER_CERT=false

# Mail (SMTP)
MAIL_DRIVER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=
MAIL_PASSWORD=

# Mail (PHP mail)
MAIL_DRIVER=mail</code></pre>
</section>

<section id="errors">
    <h2>Error Handling</h2>
    <p>In debug mode, errors render a detailed trace page. In production, a friendly error view is returned.</p>
    <pre><code>APP_ENV=production
APP_DEBUG=0</code></pre>
    <p>Customize error pages in <span class="token">framework/Views/errors</span>.</p>
</section>

<section id="testing-guide">
    <h2>Testing Guide</h2>
    <p>Basic tests live in <span class="token">tests/</span>. Run them via the CLI command.</p>
    <pre><code>php frog test</code></pre>
    <p>Tests use a lightweight runner with simple assertion helpers.</p>
    <pre><code>// tests/NoteTest.php
namespace Frog\Tests;

class NoteTest extends TestCase {
    public function run(): void {
        $this->assertTrue(true, 'Sanity check');
        $this->assertEquals(2, 1 + 1, 'Math still works');
    }
}</code></pre>
</section>

<section id="cli">
    <h2>CLI</h2>
    <p>Use the <span class="token">frog</span> CLI for scaffolding and convenience commands.</p>
    <pre><code>php frog list
php frog hop --host=localhost --port=8000
php frog hop --no-stdin
php frog hop --raw
php frog route:list
php frog make:controller About --resource
php frog make:scaffold Blog
php frog make:api-token --length=32
php frog db:check
php frog db:check sqlsrv
php frog assets:link
php frog assets:unlink --all
php frog test</code></pre>
    <p>Run <span class="token">php frog list</span> to see the full command list with descriptions.</p>
    <p>Tip: use <span class="token">--no-stdin</span> to disable the Q-to-stop listener on Windows shells, or use <span class="token">q + Enter</span> to stop cleanly.</p>
</section>

<section id="cli-reference">
    <h2>CLI Reference</h2>
    <p>Flags and arguments you can rely on:</p>
    <pre><code># hop
php frog hop --host=localhost --port=8000
php frog hop --raw
php frog hop --no-stdin
php frog hop --no-color
php frog hop --quiet

# make:controller
php frog make:controller About --resource

# make:scaffold
php frog make:scaffold Blog

# make:api-token
php frog make:api-token --length=32

# db:check
php frog db:check
php frog db:check --connection=sqlsrv

# assets
php frog assets:unlink --all
php frog assets:unlink --force</code></pre>
</section>

<section id="cli-cheatsheet">
    <h2>CLI Cheatsheet</h2>
    <p>Quick descriptions and exit codes:</p>
    <pre><code>hop          Start dev server (exit 0 on clean stop, non-zero on failure)
route:list   List registered routes
make:controller  Generate controller stub
make:scaffold    Generate controller + service + view + route
make:api-token   Create a secure API token (writes to .env)
db:check     Validate DB connection (exit 0 ok, 1 failed)
assets:link  Symlink resources into public
assets:unlink Remove symlinks (exit 0 ok, 1 failed)
test         Run tests (exit 0 all pass, 1 any fail)</code></pre>
</section>

<section id="production-checklist">
    <h2>Production Checklist</h2>
    <ul>
        <li>Set <span class="token">APP_ENV=production</span> and <span class="token">APP_DEBUG=false</span>.</li>
        <li>Ensure <span class="token">.env</span> exists with correct DB and mail credentials.</li>
        <li>Point the web server document root to <span class="token">public/</span>.</li>
        <li>Run <span class="token">php frog db:check</span> to confirm connectivity.</li>
        <li>Verify a health endpoint (for example, <span class="token">/api/health</span>).</li>
    </ul>
</section>

<section id="deployment">
    <h2>Deployment</h2>
    <p>Point your web server at <span class="token">public/</span> and ensure your environment variables are set. Disable debug in production.</p>
    <p>If you serve from a subdirectory, set <span class="token">FROG_BASE_URL</span> so assets resolve correctly.</p>
    <p>For shared hosting, upload the repository and set the document root to <span class="token">public/</span>. For Nginx or Apache, use a standard front controller rewrite to <span class="token">public/index.php</span>.</p>
</section>
@endsection