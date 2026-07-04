<?php

function serveFarmerImage()
{
    $file = $_GET['file'] ?? '';
    $file = basename($file); // prevent directory traversal
    $dir = __DIR__ . '/../public/images/farmers/';
    $path = realpath($dir . $file);

    // ensure path is inside the farmers dir
    if (!$path || strpos($path, realpath($dir)) !== 0 || !is_file($path)) {
        header('HTTP/1.0 404 Not Found');
        echo 'Image introuvable';
        exit;
    }

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = 'application/octet-stream';
    switch ($ext) {
        case 'jpg': case 'jpeg': $mime = 'image/jpeg'; break;
        case 'png': $mime = 'image/png'; break;
        case 'gif': $mime = 'image/gif'; break;
        case 'webp': $mime = 'image/webp'; break;
        default: $mime = 'application/octet-stream';
    }

    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=86400');
    readfile($path);
    exit;
}
