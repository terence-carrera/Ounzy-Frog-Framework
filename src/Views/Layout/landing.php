<?php // Landing page layout for Frog Framework 
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?= htmlspecialchars($title ?? 'Frog Framework – Lightweight PHP', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="icon" href="<?= asset('favicon.ico') ?>" />
    @stack('meta')
    @stack('styles')
    <style>
        :root {
            --bg: #0f172a;
            --bg-accent: #1e293b;
            --fg: #f1f5f9;
            --fg-muted: #94a3b8;
            --brand: #16a34a;
            --brand-accent: #4ade80;
            --radius: 14px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, 'Inter', 'Segoe UI', Roboto, Arial, sans-serif;
            background: var(--bg);
            color: var(--fg);
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: var(--brand-accent);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        header {
            padding: 1.1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        header img {
            height: 48px;
            width: auto;
            display: block;
        }

        header h1 {
            font-size: 1.15rem;
            margin: 0;
            font-weight: 600;
            letter-spacing: .5px;
        }

        .tagline {
            font-size: .8rem;
            color: var(--fg-muted);
            margin-top: .15rem;
        }

        main {
            padding: 0 1.5rem 3rem;
        }

        .hero {
            margin: 1.75rem auto 2.5rem;
            max-width: 860px;
            text-align: center;
        }

        .hero h2 {
            font-size: clamp(2.2rem, 5vw, 3.3rem);
            line-height: 1.05;
            margin: .2rem 0 .65rem;
            font-weight: 700;
            background: linear-gradient(90deg, #16a34a, #4ade80);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero p {
            font-size: 1.05rem;
            line-height: 1.55;
            margin: 0 auto 1.4rem;
            max-width: 620px;
            color: var(--fg-muted);
        }

        .cta-row {
            display: flex;
            justify-content: center;
            gap: .9rem;
            flex-wrap: wrap;
        }

        .btn {
            --btn-bg: var(--brand);
            --btn-fg: #fff;
            display: inline-block;
            padding: .85rem 1.25rem;
            border-radius: var(--radius);
            font-weight: 600;
            letter-spacing: .5px;
            background: var(--btn-bg);
            color: var(--btn-fg);
            box-shadow: 0 4px 16px -4px rgba(16, 185, 129, .35);
            transition: .25s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px -6px rgba(16, 185, 129, .45);
        }

        .btn.secondary {
            --btn-bg: var(--bg-accent);
            --btn-fg: var(--fg);
            box-shadow: 0 2px 10px -4px rgba(0, 0, 0, .4);
        }

        .btn.secondary:hover {
            box-shadow: 0 6px 18px -6px rgba(0, 0, 0, .55);
        }

        .features {
            display: grid;
            gap: 1.2rem;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            max-width: 1100px;
            margin: 0 auto 2.75rem;
        }

        .feature {
            background: var(--bg-accent);
            padding: 1rem 1.1rem 1.15rem;
            border-radius: var(--radius);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.04);
        }

        .feature h3 {
            margin: .25rem 0 .6rem;
            font-size: 1rem;
            letter-spacing: .4px;
        }

        .feature p {
            margin: 0;
            font-size: .8rem;
            line-height: 1.35;
            color: var(--fg-muted);
        }

        footer {
            padding: 2.2rem 1.5rem 3rem;
            text-align: center;
            font-size: .75rem;
            color: var(--fg-muted);
        }

        code.inline {
            background: var(--bg-accent);
            padding: .25rem .45rem;
            border-radius: 6px;
            font-size: .75rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        @media (max-width:600px) {
            header {
                flex-direction: row;
            }
        }
    </style>
</head>

<body>
    <header>
        <img src="<?= asset('images/logo.png') ?>" alt="Frog Logo" />
        <div>
            <h1>Frog Framework</h1>
            <div class="tagline">Tiny • Readable • Productive</div>
        </div>
    </header>
    <main>
        <section class="hero">
            @yield('hero')
        </section>
        <section class="content">
            @yield('content')
        </section>
        <section class="extra">
            @yield('extra')
        </section>
        <section class="features">
            @yield('features')
        </section>
    </main>
    <footer>
        @yield('footer')
    </footer>
    @stack('scripts')
</body>

</html>