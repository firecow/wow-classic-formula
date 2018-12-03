<?
declare(strict_types=1);

/**
 * @param int $errtype
 * @param string $errstr
 * @param string $errfile
 * @param int $errlin
 * @throws ErrorException
 */
function errorHandler(int $errtype, string $errstr, string $errfile, int $errlin)
{
    throw new ErrorException($errstr, $errtype, 1, $errfile, $errlin);
}

function exceptionHandler(Throwable $exception)
{
    while (ob_get_length()) {
        ob_get_clean();
    }
    header("HTTP/1.1 500 Internal Server Error");
    header("Content-Type: text/plain;charset=utf-8");
    echo("$exception");
    error_log("$exception");
}

function shutdownFunction() {
    $error = error_get_last();
    if ($error !== null) {
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];
        $errtype  = $error["type"];

        $exception = new ErrorException($errstr, $errtype, 1, $errfile, $errline);

        while (ob_get_length()) {
            ob_get_clean();
        }
        header("HTTP/1.1 500 Internal Server Error");
        header("Content-Type: text/plain;charset=utf-8");
        echo("$exception");
        error_log("$exception");
    }
}

set_exception_handler('exceptionHandler');
set_error_handler('errorHandler');
register_shutdown_function('shutdownFunction');