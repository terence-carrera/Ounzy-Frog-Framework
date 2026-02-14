<?php

use Frog\Infrastructure\App; // may be useful for future additions

if (!function_exists('frog_debug_render')) {
    function frog_debug_render(Throwable $e): string
    {
        $escape = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

        // Build exception chain
        $chain = [];
        $cur = $e;
        while ($cur) {
            $chain[] = $cur;
            $cur = $cur->getPrevious();
        }

        $renderSnippet = function (string $file, int $line) use ($escape): string {
            if (!is_file($file) || !is_readable($file)) return '';
            $lines = file($file, FILE_IGNORE_NEW_LINES);
            if ($lines === false) return '';
            $start = max($line - 6, 0);
            $end = min($line + 5, count($lines) - 1);
            $out = "<table class=code><tbody>";
            for ($i = $start; $i <= $end; $i++) {
                $content = $escape($lines[$i]);
                $highlight = ($i + 1) === $line ? ' class="hl"' : '';
                $out .= "<tr$highlight><td class=ln>" . ($i + 1) . "</td><td class=src>$content</td></tr>";
            }
            $out .= '</tbody></table>';
            return $out;
        };

        $traceToArray = function (Throwable $ex): array {
            $frames = $ex->getTrace();
            // Prepend the exception origin
            array_unshift($frames, [
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'function' => '(thrown)',
                'class' => get_class($ex),
                'type' => ''
            ]);
            return $frames;
        };

        $primary = $chain[0];
        $title = $escape(get_class($primary)) . ' : ' . $escape($primary->getMessage());

        $frames = $traceToArray($primary);

        $traceHtml = '';
        foreach ($frames as $idx => $frame) {
            $file = $frame['file'] ?? '[internal]';
            $line = $frame['line'] ?? 0;
            $func = ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '');
            $traceHtml .= '<div class="frame">'
                . '<div class="meta"><span class="index">#' . $idx . '</span> '
                . '<span class="func">' . $escape($func) . '</span>'
                . '<span class="loc">' . $escape($file) . ($line ? ':' . $line : '') . '</span></div>';
            if (isset($frame['file']) && isset($frame['line'])) {
                $traceHtml .= '<div class="snippet">' . $renderSnippet($frame['file'], (int)$frame['line']) . '</div>';
            }
            $traceHtml .= '</div>';
        }

        $chainHtml = '';
        if (count($chain) > 1) {
            $chainHtml .= '<ul class="chain">';
            foreach ($chain as $i => $ex) {
                $chainHtml .= '<li>' . $escape(get_class($ex)) . ': ' . $escape($ex->getMessage()) . '</li>';
            }
            $chainHtml .= '</ul>';
        }

        $env = [
            'PHP' => PHP_VERSION,
            'SAPI' => PHP_SAPI,
            'Time' => date('c'),
        ];
        $envHtml = '<table class="env"><tbody>';
        foreach ($env as $k => $v) {
            $envHtml .= '<tr><th>' . $escape($k) . '</th><td>' . $escape($v) . '</td></tr>';
        }
        $envHtml .= '</tbody></table>';

        $css = <<<'CSS'
body {margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#111;color:#ddd;}
header {background:#c62828;color:#fff;padding:16px 24px;}
header h1 {margin:0;font-size:20px;line-height:1.4;word-break:break-word;}
main {padding:20px 24px;display:grid;grid-template-columns: 1fr 300px;gap:24px;}
.frames {max-width:100%;}
.frame {border:1px solid #333;margin-bottom:14px;border-radius:6px;overflow:hidden;background:#1b1b1b;}
.frame .meta {font-size:13px;padding:8px 10px;background:#262626;display:flex;gap:10px;flex-wrap:wrap;}
.frame .index {color:#ffab40;font-weight:600;}
.frame .func {color:#4fc3f7;}
.frame .loc {color:#bdbdbd;margin-left:auto;}
table.code {width:100%;border-collapse:collapse;font-family: SFMono-Regular,Consolas,Menlo,monospace;font-size:12px;}
table.code td {padding:2px 8px;vertical-align:top;}
table.code td.ln {width:50px;text-align:right;color:#666;border-right:1px solid #2c2c2c;user-select:none;}
table.code tr.hl {background:#2e1f1f;}
table.code tr.hl td.ln {color:#ffeb3b;font-weight:bold;}
table.code tr.hl td.src {color:#fff;}
ul.chain {list-style:square;margin:8px 0 24px 18px;padding:0;font-size:13px;}
section.panel {background:#1b1b1b;border:1px solid #333;border-radius:6px;padding:14px;margin-bottom:24px;}
section.panel h2 {margin:0 0 10px;font-size:15px;color:#ffab40;text-transform:uppercase;letter-spacing:1px;}
table.env {width:100%;border-collapse:collapse;font-size:13px;}
table.env th {text-align:left;padding:4px 8px;background:#222;width:90px;font-weight:600;}
table.env td {padding:4px 8px;border-bottom:1px solid #222;}
footer {padding:14px 24px;font-size:12px;color:#555;text-align:center;border-top:1px solid #222;margin-top:40px;}
a {color:#64b5f6;text-decoration:none;}a:hover{text-decoration:underline;}
@media (max-width:900px){main{grid-template-columns:1fr;} .frame .loc{flex-basis:100%;margin-left:0;}}
CSS;

        return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8" />'
            . '<meta name="viewport" content="width=device-width,initial-scale=1" />'
            . '<title>' . $title . '</title>'
            . '<style>' . $css . '</style>'
            . '</head><body>'
            . '<header><h1>' . $title . '</h1></header>'
            . '<main>'
            . '<div class="frames">'
            . ($chainHtml ?: '')
            . $traceHtml
            . '</div>'
            . '<aside>'
            . '<section class="panel"><h2>Environment</h2>' . $envHtml . '</section>'
            . '<section class="panel"><h2>Help</h2><p>Exception debugging page generated by Frog.</p></section>'
            . '</aside>'
            . '</main>'
            . '<footer>Frog Framework &copy; ' . $escape(date('Y')) . '</footer>'
            . '</body></html>';
    }
}


