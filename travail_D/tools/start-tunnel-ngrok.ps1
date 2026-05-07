# Tunnel HTTPS avec ngrok (nécessite un jeton gratuit une fois).
# 1. Crée un compte sur https://dashboard.ngrok.com et copie ton "Authtoken".
# 2. Dans PowerShell : ngrok config add-authtoken COLLE_TON_TOKEN
# 3. Lance ce script :
#   cd C:\laragon\www\travail_D\tools
#   .\start-tunnel-ngrok.ps1

param(
    [int]$Port = 80
)

$ErrorActionPreference = "Stop"
Write-Host ""
Write-Host "=== Tunnel HTTPS (ngrok) ===" -ForegroundColor Cyan
Write-Host "Si erreur 'authtoken' : ngrok config add-authtoken VOTRE_TOKEN"
Write-Host "Sur le téléphone : https://URL-AFFICHEE/travail_D/login.php"
Write-Host ""

ngrok http $Port --log=stdout
