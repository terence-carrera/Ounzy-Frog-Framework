<?php $message = $message ?? 'CSRF Token Mismatch';
$description = $description ?? 'Your session has expired or the form token is invalid. Please try again.';
include __DIR__ . '/generic.php';
