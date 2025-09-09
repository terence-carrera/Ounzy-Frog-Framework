<?php // Base layout for Frog Framework standard pages 
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title ?? 'Frog Framework', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Inter & Inter Mono fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@400;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= asset('favicon.ico') ?>">
    <link rel="shortcut icon" href="<?= asset('favicon.ico') ?>">
    <style>
        :root {
            --ff-sans: 'Inter', 'Inter Tight', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            --ff-mono: 'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
        }

        html {
            font-family: var(--ff-sans);
            -webkit-font-smoothing: antialiased;
        }

        code,
        pre,
        kbd,
        samp {
            font-family: var(--ff-mono);
            font-size: 0.95em;
        }

        body {
            margin: 0;
            padding: 0 1.25rem 2rem;
            line-height: 1.55;
        }

        header {
            margin: 1.25rem 0 1.5rem;
        }

        main {
            max-width: 62rem;
        }
    </style>
    <?php if (!empty($meta)): foreach ($meta as $m): ?>
            <meta name="<?= htmlspecialchars($m['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" content="<?= htmlspecialchars($m['content'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach;
    endif; ?>
    <?php if (!empty($styles)): foreach ($styles as $href): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach;
    endif; ?>
</head>

<body>
    <?php // Provide graceful fallback if sections not defined by view 
    ?>
    <header>
        @yield('header')
    </header>
    <main>
        @yield('content')
    </main>
    <footer style="margin-top:2rem;font-size:0.875rem;color:#666;">
        @yield('footer')
    </footer>
    <?php if (!empty($scripts)): foreach ($scripts as $src): ?>
            <script src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach;
    endif; ?>
</body>

</html>