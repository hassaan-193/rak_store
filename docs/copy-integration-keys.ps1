# Copies the two shared integration secrets from the store .env into the
# portal .env, without printing them. Run once:
#   powershell -ExecutionPolicy Bypass -File D:\FTSITS\store\docs\copy-integration-keys.ps1
$storeEnv  = 'D:\FTSITS\store\.env'
$portalEnv = 'D:\FTSITS\ft_portal_base\FTS-Portal-dev\FTS-Portal-dev\.env'

$store = Get-Content $storeEnv
$s1 = ($store | Where-Object { $_ -like 'PORTAL_TO_STORE_SECRET=*' }) -replace '^PORTAL_TO_STORE_SECRET=', ''
$s2 = ($store | Where-Object { $_ -like 'STORE_TO_PORTAL_SECRET=*' }) -replace '^STORE_TO_PORTAL_SECRET=', ''
if (-not $s1 -or -not $s2) { throw 'Secrets not found in the store .env' }

if (Select-String -Path $portalEnv -Pattern '^STORE_INTEGRATION_ENABLED' -Quiet) {
    Write-Host 'Portal .env already has the store keys - nothing changed.'
    exit
}

$block = @"

# RAK store link (local)
STORE_INTEGRATION_ENABLED=true
STORE_URL=http://127.0.0.1:8088
PORTAL_TO_STORE_KEY_ID=portal
PORTAL_TO_STORE_SECRET=$s1
STORE_TO_PORTAL_KEY_ID=store
STORE_TO_PORTAL_SECRET=$s2
STORE_ALLOWED_IPS=
"@
[IO.File]::AppendAllText($portalEnv, $block)
Write-Host 'Done. Keys copied into the portal .env.'
