# add_php_extensions.ps1
# Ajoute ext-pdo et ext-pdo_mysql (+ mysqli) dans la section "require" de composer.json
# A executer depuis la racine du projet FARMMARKET (le dossier contenant composer.json)

$composerPath = "composer.json"

if (-not (Test-Path $composerPath)) {
    Write-Host "composer.json introuvable dans le dossier courant." -ForegroundColor Red
    Write-Host "Assure-toi d'executer ce script depuis la racine du projet." -ForegroundColor Red
    exit 1
}

# Sauvegarde avant modification
$backupPath = "composer.json.bak"
Copy-Item -Path $composerPath -Destination $backupPath -Force
Write-Host "Sauvegarde creee : $backupPath" -ForegroundColor Cyan

# Lecture et parsing JSON
$json = Get-Content -Path $composerPath -Raw | ConvertFrom-Json

# S'assurer que la propriete "require" existe
if (-not $json.PSObject.Properties.Match("require")) {
    $json | Add-Member -MemberType NoteProperty -Name "require" -Value (New-Object PSObject)
}

$extensions = @{
    "ext-pdo"       = "*"
    "ext-pdo_mysql" = "*"
    "ext-mysqli"    = "*"
}

$added = @()
$already = @()

foreach ($ext in $extensions.Keys) {
    if ($json.require.PSObject.Properties.Match($ext)) {
        $already += $ext
    } else {
        $json.require | Add-Member -MemberType NoteProperty -Name $ext -Value $extensions[$ext]
        $added += $ext
    }
}

# Reecriture du fichier avec une indentation propre (depth eleve pour ne rien tronquer)
$json | ConvertTo-Json -Depth 100 | Set-Content -Path $composerPath -Encoding UTF8

Write-Host ""
if ($added.Count -gt 0) {
    Write-Host "Extensions ajoutees : $($added -join ', ')" -ForegroundColor Green
} else {
    Write-Host "Aucune extension ajoutee (deja presentes)." -ForegroundColor Yellow
}
if ($already.Count -gt 0) {
    Write-Host "Deja presentes : $($already -join ', ')" -ForegroundColor DarkGray
}

Write-Host ""
Write-Host "Verifie le resultat avec : git diff composer.json" -ForegroundColor Cyan
Write-Host "Si tout est correct :" -ForegroundColor Cyan
Write-Host "  git add composer.json" -ForegroundColor White
Write-Host "  git commit -m 'fix: declare ext-pdo_mysql pour Railpack'" -ForegroundColor White
Write-Host "  git push" -ForegroundColor White
Write-Host ""
Write-Host "En cas de probleme, restaure la sauvegarde avec :" -ForegroundColor Cyan
Write-Host "  Copy-Item composer.json.bak composer.json -Force" -ForegroundColor White
