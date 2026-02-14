<?php // Base layout for Frog Framework standard pages 
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title ?? 'Frog Framework', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="<?= asset('favicon.ico') ?>">
    <link rel="shortcut icon" href="<?= asset('favicon.ico') ?>">
    <link rel="stylesheet" href="/css/app.css">
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
        <div class="page-container">
            @yield('header')
        </div>
    </header>
    <main>
        <div class="page-container">
            @yield('content')
        </div>
    </main>
    <footer style="margin-top:2rem;font-size:0.875rem;color:#666;">
        <div class="page-container">
            @yield('footer')
        </div>
    </footer>
    <?php if (!empty($scripts)): foreach ($scripts as $src): ?>
            <script src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach;
    endif; ?>
</body>

</html>