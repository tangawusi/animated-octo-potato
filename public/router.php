<?php

$requestUri = $_SERVER['REQUEST_URI'];
$filename = __DIR__ . parse_url($requestUri, PHP_URL_PATH);

if (is_file($filename)) {
    return false;
}

require_once __DIR__ . '/index.php';
