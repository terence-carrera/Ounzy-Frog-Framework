@extends('docs.base')

@section('content')
<section id="quickstart" class="docs-hero">
    <h1>Getting Started</h1>
    <p>Small, readable PHP framework focused on routing, middleware, DI, and clean views. This guide covers the essentials to ship quickly, even if this is your first time with Frog.</p>
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

<p>By default the dev server runs at <span class="token">http://localhost:8000</span>. If you prefer to run it directly:</p>
<pre><code>php frog hop
php frog hop --host=localhost --port=8000
php frog hop --no-stdin</code></pre>

<p>Next, open <span class="token">bootstrap/routes.php</span> and add your first route.</p>

<section id="first-app">
    <h2>First App in 10 Minutes</h2>
    <p>This walkthrough builds a tiny notes app with a list page and a create form. You can finish it in one sitting.</p>
    <h3>1) Add routes</h3>
    <pre><code>// bootstrap/routes.php
Route::get('/notes', [NoteController::class, 'index']);
Route::post('/notes', [NoteController::class, 'store']);</code></pre>
    <h3>2) Create a controller</h3>
    <pre><code>// framework/App/Controllers/NoteController.php
namespace Frog\App\Controllers;

use Frog\Http\Request;
use Frog\Http\Response;

class NoteController {
    public function index(): Response {
        $notes = db()->select('SELECT id, body FROM notes ORDER BY id DESC');
        return response()->html(view('notes.index', ['notes' => $notes]));
    }

    public function store(Request $request): Response {
        $body = trim((string)$request->input('body'));
        if ($body !== '') {
            db()->insert('INSERT INTO notes (body) VALUES (?)', [$body]);
        }
        return response()->status(302)->header('Location', '/notes');
    }
}</code></pre>
    <h3>3) Create a view</h3>
    <pre><code>// framework/Views/notes.index.php
&lt;h1&gt;Notes&lt;/h1&gt;
&lt;form method="post"&gt;
    &lt;?= csrf_field() ?&gt;
    &lt;input type="text" name="body" placeholder="Write a note"&gt;
    &lt;button type="submit"&gt;Add&lt;/button&gt;
&lt;/form&gt;

&lt;ul&gt;
    &lt;?php foreach ($notes as $note): ?&gt;
        &lt;li&gt;&lt;?= htmlspecialchars($note['body']) ?&gt;&lt;/li&gt;
    &lt;?php endforeach; ?&gt;
&lt;/ul&gt;</code></pre>
    <h3>4) Create the table</h3>
    <pre><code>CREATE TABLE notes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    body TEXT NOT NULL
);</code></pre>
    <p>Refresh <span class="token">/notes</span> and you should be able to add entries.</p>
</section>

<section id="routing">
    <h2>Routing</h2>
    <p>Routes live in <span class="token">bootstrap/routes.php</span>. Use closures or controllers and name routes for URL generation. You can also group routes with prefixes and middleware.</p>
    <pre><code>Route::get('/hello/{name}', function ($request, $params) {
    return response()->html('Hello ' . htmlspecialchars($params['name']));
})->name('hello');</code></pre>
    <p>Use <span class="token">Route::post()</span>, <span class="token">Route::put()</span>, and <span class="token">Route::delete()</span> for other HTTP methods.</p>
    <p>Route parameters are extracted into the <span class="token">$params</span> array. Use <span class="token">route()</span> to generate URLs from names.</p>
    <pre><code>Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

$url = route('users.show', ['id' => 42]);</code></pre>
    <p>Group routes to apply a shared prefix or middleware stack.</p>
    <pre><code>Route::group([
    'prefix' => 'admin',
    'middleware' => [AuthMiddleware::class],
], function () {
    Route::get('/users', [AdminController::class, 'users']);
    Route::post('/users', [AdminController::class, 'store']);
});</code></pre>
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
    <p>If you prefer scaffolding, use the CLI:</p>
    <pre><code>php frog make:controller About --resource
php frog make:scaffold Blog</code></pre>
    <p>Return a <span class="token">Response</span> object for full control, or return a string and it will be treated as HTML.</p>
</section>

<section id="views">
    <h2>Views</h2>
    <p>Views are simple PHP templates. Use <span class="token">name.layout.php</span> to auto-apply layouts or declare <span class="token">@extends</span> for explicit layout control.</p>
    <pre><code>// framework/Views/about.base.php
&#64;section('content')
    &lt;h2&gt;About Frog&lt;/h2&gt;
&#64;endsection</code></pre>
    <p>Common directives include <span class="token">@include</span>, <span class="token">@section</span>, and <span class="token">@yield</span>.</p>
    <pre><code>// framework/Views/Layout/base.php
&lt;main&gt;
    &#64;yield('content')
&lt;/main&gt;

// framework/Views/partials/alert.php
&lt;div class="alert"&gt;&#123;!! $message !!&#125;&lt;/div&gt;

// framework/Views/page.base.php
&#64;section('content')
    &#64;include('partials.alert')
&#64;endsection</code></pre>
    <p>To pass data into a view:</p>
    <pre><code>return response()->html(view('about.base', [
    'title' => 'About',
    'team' => ['Sam', 'Riley', 'Kai'],
]));</code></pre>
</section>
@endsection