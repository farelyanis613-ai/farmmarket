# protect_tools.ps1
# Ajoute une garde "CLI-only" en tete de chaque fichier PHP des dossiers tools/ et migrations/
# A executer depuis la racine du projet FARMMARKET (le dossier contenant tools/ et migrations/)

$guard = @"
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Acces interdit.');
}
"@

$folders = @("tools", "migrations")
$modified = 0
$skipped = 0

foreach ($folder in $folders) {
    if (-not (Test-Path $folder)) {
        Write-Host "Dossier introuvable, ignore : $folder" -ForegroundColor Yellow
        continue
    }

    $files = Get-ChildItem -Path $folder -Filter "*.php" -Recurse -File

    foreach ($file in $files) {
        $content = Get-Content -Path $file.FullName -Raw

        # Deja protege : on saute
        if ($content -match "php_sapi_name\(\)\s*!==\s*'cli'") {
            Write-Host "Deja protege, ignore : $($file.FullName)" -ForegroundColor DarkGray
            $skipped++
            continue
        }

        # Le fichier doit commencer par <?php
        if ($content -notmatch "^\s*<\?php") {
            Write-Host "Pas de balise <?php en tete, ignore manuellement : $($file.FullName)" -ForegroundColor Red
            continue
        }

        # Insertion juste apres la premiere occurrence de <?php
        $newContent = $content -replace "(^\s*<\?php\s*)", "`$1`r`n$guard`r`n"

        Set-Content -Path $file.FullName -Value $newContent -NoNewline
        Write-Host "Protege : $($file.FullName)" -ForegroundColor Green
        $modified++
    }
}

Write-Host ""
Write-Host "Termine. $modified fichier(s) modifie(s), $skipped deja protege(s)." -ForegroundColor Cyan
Write-Host "Verifie le resultat avec 'git diff' avant de committer." -ForegroundColor Cyan
