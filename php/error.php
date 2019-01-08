<?php


declare(strict_types=1);

function logAndRespondThrowable(Throwable $throwable): void
{
    header("HTTP/1.1 500 Internal Server Error");
    //if (getenv("PRINT_ERRORS_TO_CLIENT_ENABLED") === "true") {
        header("Content-type: text/plain;charset=UTF-8");
        echo "$throwable";
    //} else {
        //header("Content-type: text/html;charset=UTF-8");
        /** @noinspection PhpIncludeInspection */
        //require "../src/Layout/Pages/error.php";
    //}
    error_log("$throwable");
}

set_exception_handler(function (Throwable $exception): void {
    logAndRespondThrowable($exception);
    exit;
});
set_error_handler(function (int $errtype, string $errstr, string $errfile, int $errlin): void {
    $exception = new ErrorException($errstr, $errtype, 1, $errfile, $errlin);
    logAndRespondThrowable($exception);
    exit;
});

