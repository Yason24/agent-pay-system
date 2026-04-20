# OPS Stability Runbook

This runbook is the practical baseline to keep the local stack stable on OSPanel.

## What is protected

- FastCGI bind conflicts for PHP-8.5
- Missing or mismatched OpenSSL/cURL dependencies
- Missing `pgsql` and `pdo_pgsql` extensions after OSPanel config regeneration
- Broken DB bootstrap and missing auth routes

## Files that matter

- `C:\OSPanel\config\PHP-8.5\default\settings.ini`
- `C:\OSPanel\config\PHP-8.5\default\templates\php.ini`
- `C:\OSPanel\modules\PHP-8.5\libssl-3-x64.dll`
- `C:\OSPanel\modules\PHP-8.5\libcrypto-3-x64.dll`
- `C:\OSPanel\home\agent-pay-system\.osp\project.ini`

## One-command health checks

```powershell
composer stability:check
```

If your web server is down and you only need environment and app checks:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/stability-check.ps1 -SkipHttp
```

## Manual quick checks

```powershell
C:\OSPanel\modules\PHP-8.5\php.exe -m | findstr /I "curl openssl pgsql pdo_pgsql"
netstat -ano | findstr ":9010"
```

## Recovery when OSPanel rewrites configs

1. Ensure `settings.ini` keeps:
   - `ip = 127.0.0.1`
   - `port = 9010`
2. Ensure template `php.ini` keeps:
   - `extension = pdo_pgsql`
   - `extension = pgsql`
3. Restart only PHP-8.5 module in OSPanel.
4. Run `composer stability:check`.

## Recommended routine

- Before starting work: `composer stability:preflight`
- Before release or big merges: `composer stability:check`
- After OSPanel update: run full check and verify DLL/module status

