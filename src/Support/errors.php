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
