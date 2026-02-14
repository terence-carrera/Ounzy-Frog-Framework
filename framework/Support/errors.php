<?php

if (!function_exists('frog_error_view')) {
    function frog_error_view(int $code, array $data = []): string
    {
        $base = __DIR__ . '/../Views/errors';
        $file = $base . '/' . $code . '.php';
        if (!is_file($file)) {
            // fallback generic
            $file = $base . '/generic.php';
        }
        extract($data, EXTR_SKIP);
        $status = $code; // expose
        ob_start();
        include $file;
        return ob_get_clean();
    }
}

if (!function_exists('frog_error_response')) {
    function frog_error_response(int $code, array $data = []): \Frog\Http\Response
    {
        return response()->status($code)->html(frog_error_view($code, $data));
    }
}

if (!function_exists('frog_register_error_handlers')) {
    function frog_register_error_handlers(): void
    {
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        register_shutdown_function(function (): void {
            $err = error_get_last();
            if (!$err) {
                return;
            }
            $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
            if (!in_array($err['type'], $fatalTypes, true)) {
                return;
            }
            error_log(sprintf('[Frog] Fatal error: %s in %s:%d', $err['message'], $err['file'], $err['line']));

            if (headers_sent()) {
                return;
            }

            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            if (str_contains($accept, 'application/json')) {
                echo response()->status(500)->json(['error' => 'Server Error'])->getContent();
                return;
            }
            echo frog_error_response(500, ['description' => 'Unexpected fatal error.'])->getContent();
        });
    }
}

