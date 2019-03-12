<?php


declare(strict_types=1);

set_exception_handler(function (Throwable $exception): void {
    echo "$exception";
    error_log("$exception");
    exit(1);
});
set_error_handler(function (int $errtype, string $errstr, string $errfile, int $errlin): void {
    throw new ErrorException($errstr, $errtype, 1, $errfile, $errlin);
});
register_shutdown_function(function (): void {
    $lastError = error_get_last();
    if ($lastError === null) {
        return;
    }

    $exception = new ErrorException($lastError['message'], $lastError['type'], 1, $lastError['file'], $lastError['line']);
    echo "$exception";
    error_log("$exception");
});


