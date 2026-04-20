param(
    [string]$OSPanelRoot = "C:\OSPanel",
    [string]$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path,
    [string]$BaseUrl = "http://agent-pay-system",
    [switch]$SkipHttp
)

$ErrorActionPreference = "Stop"

function Run-Step([string]$name, [scriptblock]$action) {
    Write-Host ""
    Write-Host "=== $name ===" -ForegroundColor Cyan
    & $action
    if ($LASTEXITCODE -ne 0) {
        throw "Step failed: $name"
    }
}

Run-Step "Preflight" {
    powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot "preflight.ps1") -OSPanelRoot $OSPanelRoot -ProjectRoot $ProjectRoot
}

$phpEngine = "PHP-8.5"
$projectIniPath = Join-Path $ProjectRoot ".osp\project.ini"
if (Test-Path $projectIniPath) {
    $projectIni = Get-Content $projectIniPath -Raw
    $engineMatch = [regex]::Match($projectIni, 'php_engine\s*=\s*(.+)')
    if ($engineMatch.Success) {
        $phpEngine = $engineMatch.Groups[1].Value.Trim()
    }
}

$phpExe = Join-Path $OSPanelRoot "modules\$phpEngine\php.exe"
if (-not (Test-Path $phpExe)) {
    throw "PHP executable not found: $phpExe"
}

Run-Step "Application smoke" {
    & $phpExe (Join-Path $ProjectRoot "scripts\smoke.php")
}

if (-not $SkipHttp) {
    Write-Host ""
    Write-Host "=== HTTP smoke ===" -ForegroundColor Cyan

    $urls = @(
        "$BaseUrl/",
        "$BaseUrl/login"
    )

    foreach ($url in $urls) {
        try {
            $response = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 10
            if ($response.StatusCode -ge 200 -and $response.StatusCode -lt 500) {
                Write-Host "[PASS] $url -> HTTP $($response.StatusCode)" -ForegroundColor Green
            } else {
                Write-Host "[FAIL] $url -> HTTP $($response.StatusCode)" -ForegroundColor Red
                exit 1
            }
        } catch {
            Write-Host "[FAIL] $url -> $($_.Exception.Message)" -ForegroundColor Red
            exit 1
        }
    }
}

Write-Host ""
Write-Host "Stability result: PASSED" -ForegroundColor Green
exit 0

