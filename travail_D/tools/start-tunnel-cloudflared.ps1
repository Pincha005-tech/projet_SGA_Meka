# Tunnel HTTPS gratuit vers Laragon (Apache), sans compte Cloudflare.
# Usage : clic droit > Exécuter avec PowerShell, ou dans PowerShell :
#   cd C:\laragon\www\travail_D\tools
#   .\start-tunnel-cloudflared.ps1
# Optionnel : .\start-tunnel-cloudflared.ps1 8080   (si ton Apache écoute sur 8080)

param(
    [int]$Port = 80
)

$ErrorActionPreference = "Stop"
Write-Host ""
Write-Host "=== Tunnel HTTPS (Cloudflare Quick Tunnel) ===" -ForegroundColor Cyan
Write-Host "1. Démarre Laragon (Apache) avant de continuer."
Write-Host "2. Une URL du type https://xxxxx.trycloudflare.com s'affichera ci-dessous."
Write-Host "3. Sur le téléphone, ouvre : https://xxxxx.trycloudflare.com/travail_D/login.php"
Write-Host "   (remplace par ton URL exacte + chemin du dossier projet)"
Write-Host ""
Write-Host "Appuyez sur Ctrl+C pour arrêter le tunnel."
Write-Host ""

cloudflared tunnel --url "http://127.0.0.1:$Port"
