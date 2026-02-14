<?php // Docs layout template ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title ?? 'Frog Framework Docs', ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="<?= asset('favicon.ico') ?>">
    <link rel="shortcut icon" href="<?= asset('favicon.ico') ?>">
    <link rel="stylesheet" href="/css/app.css">
</head>

<body>
    <div class="docs-topbar">
        <div class="docs-topbar-inner page-container">
            <div class="docs-brand">
                <a href="/" style="display: flex; align-items: center; gap: 0.75rem;">
                    <img src="<?= asset('images/logo.png') ?>" alt="Frog Logo" style="height: 48px; width: auto;" />
                    <span>Frog Framework</span>
                </a>
            </div>
            <div class="docs-actions">
                <a href="https://github.com/terence-carrera/frog-framework" target="_blank" rel="noopener">Visit GitHub Repository</a>
            </div>
        </div>
    </div>

    <div class="docs-shell page-container">
        <aside class="docs-nav">
            <h4>Docs</h4>
            <a href="/docs/getting-started" data-docs-link>Getting Started</a>
            <a href="/docs/core" data-docs-link>Core Features</a>
            <a href="/docs/advanced" data-docs-link>Advanced</a>
        </aside>

        <div class="docs-main">
            @yield('content')
        </div>

        <?php if (!empty($nav)) : ?>
            <aside class="docs-toc">
                <h4>On This Page</h4>
                <?php foreach ($nav as $item) : ?>
                    <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>" data-toc-link><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
            </aside>
        <?php endif; ?>
    </div>

    <div class="docs-footer">
        <div class="docs-footer-inner page-container">
            <div class="docs-footer-meta">
                <span>Copyright &copy; <?php echo date('Y'); ?> Frog Framework</span>
            </div>
            <div class="docs-footer-links">
                <a href="mailto:support@frogframework.com">support@frogframework.com</a>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var path = window.location.pathname || '';
            if (path === '/docs') path = '/docs/getting-started';
            document.querySelectorAll('[data-docs-link]').forEach(function (link) {
                var href = link.getAttribute('href') || '';
                if (href === path) {
                    link.classList.add('is-active');
                    link.setAttribute('aria-current', 'page');
                }
            });

            var tocLinks = Array.prototype.slice.call(document.querySelectorAll('[data-toc-link]'));
            if (!tocLinks.length) return;

            var byId = {};
            tocLinks.forEach(function (link) {
                var href = link.getAttribute('href') || '';
                if (href.charAt(0) === '#') {
                    byId[href.slice(1)] = link;
                }
            });

            var sections = Object.keys(byId).map(function (id) {
                return document.getElementById(id);
            }).filter(Boolean);

            if (!sections.length) return;

            var setActive = function (id) {
                tocLinks.forEach(function (link) {
                    if (link.getAttribute('href') === '#' + id) {
                        link.classList.add('is-active');
                        link.setAttribute('aria-current', 'true');
                    } else {
                        link.classList.remove('is-active');
                        link.removeAttribute('aria-current');
                    }
                });
            };

            var isMobile = window.matchMedia && window.matchMedia('(max-width: 900px)').matches;
            var rootMargin = isMobile ? '-10% 0px -55% 0px' : '-20% 0px -65% 0px';
            var observer = new IntersectionObserver(function (entries) {
                var visible = entries.filter(function (e) { return e.isIntersecting; });
                if (!visible.length) return;
                visible.sort(function (a, b) { return b.intersectionRatio - a.intersectionRatio; });
                var id = visible[0].target.getAttribute('id');
                if (id) setActive(id);
            }, { rootMargin: rootMargin, threshold: [0, 0.25, 0.5, 0.75, 1] });

            sections.forEach(function (section) { observer.observe(section); });
        })();
    </script>
</body>

</html>
