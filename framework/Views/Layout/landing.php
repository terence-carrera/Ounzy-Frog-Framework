<?php // Landing page layout for Frog Framework 
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?= htmlspecialchars($title ?? 'Frog Framework – Lightweight PHP', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="icon" href="<?= asset('favicon.ico') ?>" />
    <link rel="stylesheet" href="/css/app.css">
    @stack('meta')
    @stack('styles')
</head>

<body class="landing-body">
    <header class="landing-header page-container">
        <img src="<?= asset('images/logo.png') ?>" alt="Frog Logo" />
        <div>
            <h1 class="landing-title">No Bullshit. Just Pure Framework</h1>
            <div class="landing-tagline">Clean, readable, and focused on the essentials.</div>
        </div>
    </header>
    <main class="landing-main page-container">
        <section class="landing-hero">
            @yield('hero')
        </section>
        <section>
            @yield('content')
        </section>
        <section>
            @yield('extra')
        </section>
        <section class="landing-features">
            @yield('features')
        </section>
    </main>
    <footer class="landing-footer">
        <div class="page-container">
            @yield('footer')
        </div>
    </footer>
    @stack('scripts')
</body>

</html>