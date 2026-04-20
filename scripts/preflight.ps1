param(
    [string]$OSPanelRoot = "C:\OSPanel",
    [string]$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path,
    [string]$ExpectedPhpEngine = "PHP-8.5",
    [string]$ExpectedIp = "127.0.0.1",
    [int]$ExpectedPort = 9010
)

$ErrorActionPreference = "Stop"
$errors = New-Object System.Collections.Generic.List[string]
$warnings = New-Object System.Collections.Generic.List[string]

function Add-Fail([string]$message) {
    $errors.Add($message)
    Write-Host "[FAIL] $message" -ForegroundColor Red
}

function Add-Warn([string]$message) {
    $warnings.Add($message)
    Write-Host "[WARN] $message" -ForegroundColor Yellow
}

function Add-Pass([string]$message) {
    Write-Host "[PASS] $message" -ForegroundColor Green
}

Write-Host "=== Preflight checks ==="
Write-Host "ProjectRoot: $ProjectRoot"
Write-Host "OSPanelRoot: $OSPanelRoot"

$projectIniPath = Join-Path $ProjectRoot ".osp\project.ini"
if (-not (Test-Path $projectIniPath)) {
    Add-Fail "Missing file: $projectIniPath"
} else {
    $projectIni = Get-Content $projectIniPath -Raw
    $phpEngineMatch = [regex]::Match($projectIni, 'php_engine\s*=\s*(.+)')
    if (-not $phpEngineMatch.Success) {
        Add-Fail "Cannot read php_engine from .osp/project.ini"
    } else {
        $phpEngine = $phpEngineMatch.Groups[1].Value.Trim()
        if ($phpEngine -ne $ExpectedPhpEngine) {
            Add-Warn "php_engine is '$phpEngine', expected '$ExpectedPhpEngine'"
        } else {
            Add-Pass "php_engine is $phpEngine"
        }
    }
}

$phpModulePath = Join-Path $OSPanelRoot "modules\$ExpectedPhpEngine"
$phpExe = Join-Path $phpModulePath "php.exe"
if (-not (Test-Path $phpExe)) {
    Add-Fail "PHP executable not found: $phpExe"
} else {
    Add-Pass "Found PHP executable: $phpExe"
}

$settingsPath = Join-Path $OSPanelRoot "config\$ExpectedPhpEngine\default\settings.ini"
if (-not (Test-Path $settingsPath)) {
    Add-Fail "Missing settings.ini: $settingsPath"
} else {
    $ipLine = Select-String -Path $settingsPath -Pattern '^\s*ip\s*=' | Select-Object -First 1
    $portLine = Select-String -Path $settingsPath -Pattern '^\s*port\s*=' | Select-Object -First 1

    if ($null -eq $ipLine -or $null -eq $portLine) {
        Add-Fail "Cannot parse ip/port from settings.ini"
    } else {
        $actualIp = ($ipLine.Line -split '=', 2)[1].Trim()
        $actualPort = [int](($portLine.Line -split '=', 2)[1].Trim())

        if ($actualIp -ne $ExpectedIp -or $actualPort -ne $ExpectedPort) {
            Add-Warn "FastCGI is ${actualIp}:${actualPort}, expected ${ExpectedIp}:${ExpectedPort}"
        } else {
            Add-Pass "FastCGI configured as ${actualIp}:${actualPort}"
        }
    }
}

# If port is busy, ensure it is owned by php-cgi or OSPanel-managed process.
$portLines = & netstat -ano | Select-String ":$ExpectedPort"
if ($portLines.Count -gt 0) {
    $firstPid = ($portLines[0].ToString().Trim() -split '\s+')[-1]
    $proc = Get-Process -Id $firstPid -ErrorAction SilentlyContinue
    if ($null -eq $proc) {
        Add-Warn "Port $ExpectedPort is occupied by PID $firstPid (process not accessible)"
    } elseif ($proc.ProcessName -notmatch "php-cgi|php") {
        Add-Warn "Port $ExpectedPort is occupied by '$($proc.ProcessName)' (PID $firstPid)"
    } else {
        Add-Pass "Port $ExpectedPort is currently used by $($proc.ProcessName) (expected if PHP is running)"
    }
} else {
    Add-Pass "Port $ExpectedPort is free"
}

$templateIniPath = Join-Path $OSPanelRoot "config\$ExpectedPhpEngine\default\templates\php.ini"
if (-not (Test-Path $templateIniPath)) {
    Add-Fail "Missing php.ini template: $templateIniPath"
} else {
    $templateIni = Get-Content $templateIniPath -Raw
    if ($templateIni -notmatch '(?m)^extension\s*=\s*pdo_pgsql\s*$') {
        Add-Fail "Template php.ini does not enable pdo_pgsql"
    } else {
        Add-Pass "Template php.ini enables pdo_pgsql"
    }

    if ($templateIni -notmatch '(?m)^extension\s*=\s*pgsql\s*$') {
        Add-Fail "Template php.ini does not enable pgsql"
    } else {
        Add-Pass "Template php.ini enables pgsql"
    }
}

$requiredDlls = @(
    "libssl-3-x64.dll",
    "libcrypto-3-x64.dll",
    "libpq.dll",
    "libintl-9.dll",
    "libiconv-2.dll",
    "libwinpthread-1.dll",
    "zlib1.dll"
)

foreach ($dll in $requiredDlls) {
    $dllPath = Join-Path $phpModulePath $dll
    if (-not (Test-Path $dllPath)) {
        Add-Fail "Missing DLL: $dllPath"
    } else {
        Add-Pass "Found DLL: $dll"
    }
}

if (Test-Path $phpExe) {
    $env:PHPRC = $phpModulePath
    foreach ($module in @("curl", "openssl", "pdo_pgsql", "pgsql")) {
        $loaded = (& $phpExe -r "echo extension_loaded('$module') ? '1' : '0';" 2>&1 | Out-String).Trim()
        if ($loaded -ne "1") {
            Add-Fail "PHP module '$module' is not loaded"
        } else {
            Add-Pass "PHP module '$module' loaded"
        }
    }

    $moduleText = (& $phpExe -m 2>&1 | Out-String)

    if ($moduleText -match "Warning: PHP Startup") {
        Add-Fail "PHP Startup warnings detected. Check extension dependencies."
    }
}

Write-Host ""
if ($warnings.Count -gt 0) {
    Write-Host "Warnings: $($warnings.Count)"
}

if ($errors.Count -gt 0) {
    Write-Host "Preflight result: FAILED ($($errors.Count) errors)" -ForegroundColor Red
    exit 1
}

Write-Host "Preflight result: PASSED" -ForegroundColor Green
exit 0

