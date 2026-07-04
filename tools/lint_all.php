<?php
$root = realpath(__DIR__ . '/..');
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($it as $f) {
    if ($f->isFile() && strtolower($f->getExtension()) === 'php') {
        $path = $f->getPathname();
        echo "=== $path\n";
        passthru('php -l ' . escapeshellarg($path));
    }
}
