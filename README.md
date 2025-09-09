# Frog Framework 🐸

Frog is a tiny, readable PHP micro‑framework you can grasp in one sitting. Five core ideas:

1. Front Controller (`public/index.php`)
2. Routes (`bootstrap/routes.php` via `Route::get()` / `Route::post()`)
3. Controllers (plain classes, auto dependency injection)
4. Views (PHP templates + light Blade‑like directives + layouts)
5. Helpers / CLI (ergonomic dev shortcuts)

Everything else is optional sugar layered thinly so you can still see the PHP underneath.

## Quick Start

```bash
composer install            # install deps
php frog hop                # start dev server (defaults :8000)
```

Open: <http://localhost:8000>

Add a route in `bootstrap/routes.php`:

```php
Route::get('/hello/{name}', fn($p) => response()->html("Hello ".htmlspecialchars($p['name'])));
```

Or return a view:

```php
Route::get('/about', fn() => response()->html(view('about.base'))); // auto-uses Layout/base
```

## Project Layout (You’ll Only Need These Early On)

```text
public/              Front controller & public assets
bootstrap/routes.php  Define routes (Route::get/post)
src/Controllers/      Your controllers
src/Views/            Views (hello.base.php -> uses Layout/base)
src/Views/Layout/     Layouts (base.php, auth.php...)
src/Support/          Helpers (view(), asset(), route(), config(), ...)
frog                  CLI launcher (commands)
```

## Core Helpers

| Helper                 | Purpose                                   |
|------------------------|-------------------------------------------|
| `response()`           | Build an HTTP response (html/json/status) |
| `view(name)`           | Render a view file                        |
| `layout_view()`        | Shortcut to render with a layout          |
| `route(name, [params])`| URL for a named route                     |
| `asset('images/logo.png')` | Public asset URL (with optional base)  |
| `config('key')`        | Read config (if bound)                    |
| `app()` / `container()`| Access application / DI container         |

## Views & Layouts

Filenames can embed the layout: `index.base.php` → uses `Layout/base.php` automatically.

Directives (mini Blade‑like):

```text
{{ $var }}   {!! $raw !!}
@if / @elseif / @else / @endif
@foreach / @endforeach  (@for, @while ...)
@section('body') ... @endsection
@yield('body','default')
@include('partials/nav')
```
Sections collected in the view become `@yield()` spots inside the layout.

Example layout: `src/Views/Layout/base.php`

```php
<!doctype html><html><head><title><?= htmlspecialchars($title??'Frog') ?></title></head>
<body>
    <main>@yield('body', $content ?? '')</main>
</body></html>
```

Example view: `src/Views/home.base.php`

```php
@section('body')
    <h1>Hello Frog 🐸</h1>
    <img src="<?= asset('images/logo.png') ?>" alt="Logo">
@endsection
```

## Controllers & DI

Any constructor or method parameter type‑hint is auto‑resolved via a light reflection container.

```php
class AboutController {
    public function __construct(private Logger $log) {}
    public function show(Request $req): Response {
        $this->log->info('about viewed');
        return response()->html(view('about.base'));
    }
}
Route::get('/about', [AboutController::class, 'show'])->name('about');
```

## CLI Commands

List: `php frog list`

| Command               | Description                           |
|-----------------------|---------------------------------------|
| `hop`                 | Dev server                            |
| `route:list`          | Show registered routes                |
| `make:controller`     | Generate controller stub              |
| `make:scaffold Name`  | Controller + service + view + route   |
| `make:api-token`      | Generate & hash API token (.env)      |
| `assets:link`         | Symlink resources/* into public/*     |
| `assets:unlink`       | Remove those links                    |
| `test`                | Run lightweight tests                 |

## Assets

Place raw source under `resources/`. Link them:

```bash
php frog assets:link   # creates public/images -> resources/images
```

Use in views: `<img src="<?= asset('images/logo.png') ?>" alt="">`

## Error & Debug

Fails gracefully with custom 401/404/500 pages. In debug mode (set `APP_DEBUG=1` in `.env`) an informative stack trace page is shown.

## Concept Map (Mental Model)

```text
Request -> Router -> (Middleware) -> Controller/Closure -> Response -> Sent
                               \-> view() -> layout -> HTML
```
Everything is plain PHP arrays / objects—no magic global state beyond the single App instance.

## One-Sitting Learning Path

1. Open `public/index.php` – see bootstrap + dispatch.
2. Open `bootstrap/routes.php` – add a simple closure route.
3. Create a view `home.base.php` + layout section.
4. Add a controller method; inject Request; return response()->html().
5. Run `php frog route:list` to confirm.

Done. You now know 90%.

## Tailwind (Optional)

Build CSS (already configured):

```bash
composer run dev:css    # watch
composer run build:css  # production
```
Include: `<link rel="stylesheet" href="/assets/app.css">`

## Advanced (Optional / Skip at First)

Middleware, more robust logging, route groups, named routes, bearer token auth, section directives, and DI container internals are all readable in `src/` if you want to dive deeper later.

## Cheat Sheet

See: `docs/cheatsheet.md` (generated for quick recall).

## License

MIT

