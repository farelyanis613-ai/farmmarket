<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit. Ce script est réservé à la CLI.');
}

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    fwrite(STDERR, "Fichier .env introuvable à la racine du projet.\n");
    exit(1);
}

$content = file_get_contents($envPath);
if ($content === false) {
    fwrite(STDERR, "Impossible de lire le fichier .env.\n");
    exit(1);
}

$content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
$hasBom = $content !== file_get_contents($envPath);
if ($hasBom) {
    fwrite(STDOUT, "UTF-8 BOM détecté dans .env. Il est recommandé de le supprimer.\n");
}

$lines = preg_split('/\r\n|\n|\r/', $content);
$keys = [];
$duplicates = [];
$invalid = [];

foreach ($lines as $index => $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
        continue;
    }

    if (strpos($line, '=') === false) {
        $invalid[] = [$index + 1, $line];
        continue;
    }

    list($key, $value) = array_map('trim', explode('=', $line, 2));
    if ($key === '') {
        $invalid[] = [$index + 1, $line];
        continue;
    }

    if (isset($keys[$key])) {
        $duplicates[] = [$key, $keys[$key], $index + 1];
    }
    $keys[$key] = $index + 1;
}

fwrite(STDOUT, "Analyse de .env finalisée.\n");
fwrite(STDOUT, sprintf("Clés uniques trouvées : %d\n", count($keys)));
if (!empty($duplicates)) {
    fwrite(STDOUT, sprintf("Duplications détectées : %d\n", count($duplicates)));
    foreach ($duplicates as [$key, $first, $second]) {
        fwrite(STDOUT, sprintf("  - %s : lignes %d et %d\n", $key, $first, $second));
    }
}
if (!empty($invalid)) {
    fwrite(STDOUT, sprintf("Lignes invalides détectées : %d\n", count($invalid)));
    foreach ($invalid as [$line, $text]) {
        fwrite(STDOUT, sprintf("  - ligne %d : %s\n", $line, $text));
    }
}
if ($hasBom) {
    fwrite(STDOUT, "Recommandation : sauvegardez votre .env sans BOM et relancez l'application.\n");
}

if (empty($duplicates) && empty($invalid) && !$hasBom) {
    fwrite(STDOUT, "Aucun problème détecté dans .env.\n");
}
