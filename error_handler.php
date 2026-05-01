<?php
// Custom error handler for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function customErrorHandler($errno, $errstr, $errfile, $errline) {
    echo "<div style='background: #ffcccc; border: 1px solid #ff0000; padding: 10px; margin: 10px;'>";
    echo "<strong>Error:</strong> [$errno] $errstr<br>";
    echo "<strong>File:</strong> $errfile<br>";
    echo "<strong>Line:</strong> $errline<br>";
    echo "</div>";
    return true;
}

function customExceptionHandler($exception) {
    echo "<div style='background: #ffcccc; border: 1px solid #ff0000; padding: 10px; margin: 10px;'>";
    echo "<strong>Uncaught Exception:</strong> " . $exception->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $exception->getLine() . "<br>";
    echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    echo "</div>";
}

// Set error handlers
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');

// Start output buffering to catch any errors
ob_start();
?>
