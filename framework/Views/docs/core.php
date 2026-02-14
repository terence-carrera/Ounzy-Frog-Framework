@extends('docs.base')

@section('content')
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
    <p>Attach middleware to specific routes to protect or shape behavior.</p>
    <pre><code>Route::get('/admin', [AdminController::class, 'index'], [AuthMiddleware::class]);</code></pre>
    <p>Middleware can also mutate the response before it returns:</p>
    <pre><code>class PoweredByMiddleware implements MiddlewareInterface {
    public function handle(Request $r, callable $next): Response {
        $response = $next($r);
        return $response->header('X-Powered-By', 'Frog');
    }
}</code></pre>
</section>

<section id="security">
    <h2>Security</h2>
    <div class="callout">CSRF is enabled by default for unsafe methods. Add <span class="token">&lt;?= csrf_field() ?&gt;</span> inside your forms.</div>
    <p>Sessions are started by middleware and configured in <span class="token">config/session.php</span>. You can disable CSRF in <span class="token">config/security.php</span>.</p>
    <pre><code>&lt;form method="post"&gt;
    &lt;?= csrf_field() ?&gt;
    &lt;input type="text" name="title"&gt;
&lt;/form&gt;</code></pre>
    <p>When sending JSON, include the token in the header:</p>
    <pre><code>fetch('/posts', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '...'
    },
    body: JSON.stringify({ title: 'Hello' })
});</code></pre>
</section>

<section id="requests">
    <h2>Requests</h2>
    <p>Use the <span class="token">Request</span> object for method, path, input, and headers.</p>
    <pre><code>$method = $request->method();
$path = $request->path();
$token = $request->header('X-API-TOKEN');
$name = $request->input('name');</code></pre>
    <p>Query string values are available via <span class="token">query()</span>. Route parameters arrive as the second argument in handlers.</p>
    <pre><code>Route::get('/users/{id}', function (Request $request, array $params) {
    $page = $request->query('page', 1);
    $id = $params['id'];
    return response()->json(['id' => $id, 'page' => $page]);
});</code></pre>
</section>

<section id="responses">
    <h2>Responses</h2>
    <p>Responses are fluent and can return HTML or JSON with status codes.</p>
    <pre><code>return response()
    ->status(201)
    ->json(['ok' => true]);</code></pre>
    <p>Set headers as needed for caching or content negotiation.</p>
    <pre><code>return response()
    ->status(200)
    ->header('X-Request-ID', $requestId)
    ->html(view('dashboard.base'));</code></pre>
    <p>Redirects are supported via status codes:</p>
    <pre><code>return response()
    ->status(302)
    ->header('Location', route('users.show', ['id' => 1]));</code></pre>
</section>

<section id="di">
    <h2>Dependency Injection</h2>
    <p>Type-hinted constructor dependencies are auto-resolved. You can also bind services manually.</p>
    <pre><code>app()->container()->singleton(
    Frog\App\Services\Cache::class,
    fn() => new Frog\App\Services\Cache()
);</code></pre>
    <p>Once bound, simply type-hint the class in controllers or middleware.</p>
    <pre><code>class ProfileController {
    public function __construct(private ProfileService $profiles) {}

    public function show(Request $req): Response {
        return response()->json($this->profiles->forUser(1));
    }
}</code></pre>
</section>

<section id="services">
    <h2>Services</h2>
    <p>Reusable services live in <span class="token">framework/App/Services</span> and are injected into controllers or middleware.</p>
    <pre><code>class GreetingService {
    public function greet(string $name): string {
        return "Hello {$name}";
    }
}</code></pre>
    <p>Services are a good place for business logic, so controllers can stay thin.</p>
</section>

<section id="database">
    <h2>Database</h2>
    <p>Multiple connections are supported via <span class="token">config/database.php</span>. Use <span class="token">db()</span> to query.</p>
    <pre><code>// Select using default connection
$rows = db()->select('SELECT * FROM users');

// Use a named connection
$audit = db('pgsql')->select('SELECT * FROM audit_log');</code></pre>
    <p>Write operations return row counts or inserted IDs.</p>
    <pre><code>$id = db()->insert(
    'INSERT INTO users (name, email) VALUES (?, ?)',
    [$name, $email]
);

$updated = db()->update('UPDATE users SET active = 1 WHERE id = ?', [$id]);</code></pre>
    <p>Transactions are available on the connection.</p>
    <pre><code>db()->transaction(function ($db) {
    $db->update('UPDATE accounts SET balance = balance - 50 WHERE id = ?', [1]);
    $db->update('UPDATE accounts SET balance = balance + 50 WHERE id = ?', [2]);
});</code></pre>
    <p>SQL Server is supported via the <span class="token">sqlsrv</span> driver (requires the <span class="token">pdo_sqlsrv</span> and <span class="token">sqlsrv</span> PHP extensions).</p>
    <pre><code># .env example for SQL Server
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=frog
DB_USERNAME=sa
DB_PASSWORD=secret
DB_INSTANCE=
DB_ENCRYPT=false
DB_TRUST_SERVER_CERT=false</code></pre>
    <p>Verify the connection with the built-in command:</p>
    <pre><code>php frog db:check
php frog db:check sqlsrv</code></pre>
</section>

<section id="cache">
    <h2>Caching</h2>
    <p>Cache stores are configured in <span class="token">config/cache.php</span>. Use <span class="token">cache()</span> to interact with the selected store.</p>
    <pre><code>cache()->set('health', 'ok', 60);
$status = cache()->get('health');</code></pre>
    <p>Additional helpers let you check, delete, or flush cache entries.</p>
    <pre><code>if (cache()->has('health')) {
    cache()->delete('health');
}

cache()->clear();</code></pre>
    <p>Cache is a great place to store API responses, rendered HTML fragments, or throttling counters.</p>
</section>
@endsection