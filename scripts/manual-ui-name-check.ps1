$ErrorActionPreference = 'Stop'

$baseUrl = 'http://agent-pay-system'
$psql = 'C:\OSPanel\modules\PostgreSQL-17\bin\psql.exe'
$php = 'C:\OSPanel\modules\PHP-8.5\php.exe'
$env:PGPASSWORD = 'postgres'
$dbArgs = @('-h', '127.0.1.36', '-p', '5432', '-U', 'postgres', '-d', 'agent_pay_system', '-P', 'pager=off', '-t', '-A', '-F', "`t")

function Query-Rows([string] $sql) {
    $output = & $psql @dbArgs -c $sql
    $lines = @($output -split "`r?`n" | Where-Object { $_.Trim() -ne '' })
    return ,$lines
}

function Query-One([string] $sql) {
    $rows = @(Query-Rows $sql)
    if ($rows.Count -eq 0) {
        return ''
    }

    return ([string] $rows[0]).Trim()
}

function Try-Login([string] $login, [string] $password) {
    $session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $loginPage = Invoke-WebRequest -Uri "$baseUrl/login" -WebSession $session -UseBasicParsing
    $csrf = ''
    if ($loginPage.Content -match 'name="_token"\s+value="([^"]+)"') {
        $csrf = $matches[1]
    }

    $payload = @{ login = $login; password = $password }
    if ($csrf -ne '') {
        $payload['_token'] = $csrf
    }

    Invoke-WebRequest -Uri "$baseUrl/login" -Method Post -Body $payload -WebSession $session -UseBasicParsing -MaximumRedirection 5 | Out-Null

    $dashboard = Invoke-WebRequest -Uri "$baseUrl/dashboard" -WebSession $session -UseBasicParsing -MaximumRedirection 5
    if ($dashboard.BaseResponse.ResponseUri.AbsoluteUri -like '*/login*') {
        return $null
    }

    return @{
        Session = $session
        Dashboard = $dashboard
    }
}

function Set-KnownAdminPassword([string] $login) {
    $hash = (& $php -r "echo password_hash('12345', PASSWORD_BCRYPT);")
    $hash = ([string] $hash).Trim()

    if ($hash -eq '') {
        throw 'Unable to generate bcrypt hash for local login check.'
    }

    $safeLogin = $login.Replace("'", "''")
    $safeHash = $hash.Replace("'", "''")
    & $psql @dbArgs -c "UPDATE users SET password = '$safeHash' WHERE login = '$safeLogin';" | Out-Null
}

function Assert-Contains {
    param(
        [string] $Html,
        [string] $Needle,
        [string] $Label
    )

    if ($Html.Contains($Needle)) {
        Write-Output ("PASS: " + $Label + " => " + $Needle)
        return $true
    }

    Write-Output ("FAIL: " + $Label + " => missing: " + $Needle)
    return $false
}

$failures = @()

$adminRows = Query-Rows "SELECT login, email FROM users WHERE role = 'admin' ORDER BY id"
$agentUserId = Query-One "SELECT id FROM users WHERE role = 'agent' ORDER BY id LIMIT 1"
$agentFullName = Query-One "SELECT TRIM(COALESCE(last_name, '') || ' ' || COALESCE(first_name, '') || ' ' || COALESCE(middle_name, '')) FROM users WHERE role = 'agent' ORDER BY id LIMIT 1"

if ($agentUserId -eq '') {
    throw 'No agent user found in DB.'
}

if ($agentFullName -eq '') {
    throw 'Expected agent full name is empty; cannot validate UI FIO rendering.'
}

$requestAgentUserId = Query-One "SELECT agent_user_id FROM requests WHERE COALESCE(taken_by_name, '') <> '' ORDER BY id DESC LIMIT 1"
if ($requestAgentUserId -eq '') {
    $requestAgentUserId = $agentUserId
}

$requestAgentFullName = Query-One "SELECT TRIM(COALESCE(last_name, '') || ' ' || COALESCE(first_name, '') || ' ' || COALESCE(middle_name, '')) FROM users WHERE id = $requestAgentUserId"
if ($requestAgentFullName -eq '') {
    $requestAgentFullName = $agentFullName
}

$takenByName = Query-One "SELECT COALESCE(taken_by_name, '') FROM requests WHERE agent_user_id = $requestAgentUserId AND COALESCE(taken_by_name, '') <> '' ORDER BY id DESC LIMIT 1"
$takenByFullName = Query-One "SELECT TRIM(COALESCE(u.last_name, '') || ' ' || COALESCE(u.first_name, '') || ' ' || COALESCE(u.middle_name, '')) FROM requests r JOIN users u ON u.id = r.taken_by_user_id WHERE r.agent_user_id = $requestAgentUserId AND r.taken_by_user_id IS NOT NULL ORDER BY r.id DESC LIMIT 1"
if ($takenByFullName -eq '') {
    $takenByFullName = $takenByName
}

$credentials = @()
foreach ($row in $adminRows) {
    $parts = $row -split "`t"
    if ($parts[0] -ne '') { $credentials += @{ Login = $parts[0]; Password = '12345' } }
    if ($parts.Count -gt 1 -and $parts[1] -ne '') { $credentials += @{ Login = $parts[1]; Password = '12345' } }
}

$auth = $null
foreach ($cred in $credentials) {
    Write-Output ("TRY_LOGIN=" + $cred['Login'])
    $auth = Try-Login -login $cred['Login'] -password $cred['Password']
    if ($auth -ne $null) {
        Write-Output ("LOGIN_OK=" + $cred['Login'])
        break
    }
}

if ($auth -eq $null) {
    $firstAdminLogin = ''
    if ($credentials.Count -gt 0) {
        $firstAdminLogin = [string] $credentials[0]['Login']
    }

    if ($firstAdminLogin -eq '') {
        throw 'Unable to determine admin login for local UI check.'
    }

    Write-Output ('RESET_ADMIN_PASSWORD=' + $firstAdminLogin)
    Set-KnownAdminPassword -login $firstAdminLogin
    $auth = Try-Login -login $firstAdminLogin -password '12345'

    if ($auth -eq $null) {
        throw 'Unable to login even after setting known local admin password.'
    }

    Write-Output ('LOGIN_OK=' + $firstAdminLogin + ' (after password reset)')
}

$session = $auth['Session']
$dashboardHtml = $auth['Dashboard'].Content
if (-not (Assert-Contains -Html $dashboardHtml -Needle '/agents' -Label 'dashboard opens')) { $failures += 'dashboard' }

$adminUsers = Invoke-WebRequest -Uri "$baseUrl/admin/users" -WebSession $session -UseBasicParsing -MaximumRedirection 5
$agents = Invoke-WebRequest -Uri "$baseUrl/agents" -WebSession $session -UseBasicParsing -MaximumRedirection 5
$requests = Invoke-WebRequest -Uri "$baseUrl/requests?agent_user_id=$requestAgentUserId" -WebSession $session -UseBasicParsing -MaximumRedirection 5
$payments = Invoke-WebRequest -Uri "$baseUrl/payments?agent_user_id=$agentUserId" -WebSession $session -UseBasicParsing -MaximumRedirection 5
$balance = Invoke-WebRequest -Uri "$baseUrl/history?agent_user_id=$agentUserId" -WebSession $session -UseBasicParsing -MaximumRedirection 5

if (-not (Assert-Contains -Html $adminUsers.Content -Needle '/admin/users/create' -Label '/admin/users opens')) { $failures += '/admin/users open' }
if (-not (Assert-Contains -Html $adminUsers.Content -Needle $agentFullName -Label '/admin/users shows FIO')) { $failures += '/admin/users fio' }

if (-not (Assert-Contains -Html $agents.Content -Needle ('/history?agent_user_id=' + $agentUserId) -Label '/agents opens')) { $failures += '/agents open' }
if (-not (Assert-Contains -Html $agents.Content -Needle $agentFullName -Label '/agents shows FIO')) { $failures += '/agents fio' }

if (-not (Assert-Contains -Html $requests.Content -Needle ('<strong>' + $requestAgentFullName + '</strong>') -Label '/requests header FIO')) { $failures += '/requests header' }
if ($takenByFullName -ne '') {
    if (-not (Assert-Contains -Html $requests.Content -Needle $takenByFullName -Label '/requests taken_by FIO')) { $failures += '/requests taken_by' }
} else {
    Write-Output 'INFO: no taken_by_name rows found; skipped taken_by content check'
}

if (-not (Assert-Contains -Html $payments.Content -Needle ('<strong>' + $agentFullName + '</strong>') -Label '/payments header FIO')) { $failures += '/payments header' }
if (-not (Assert-Contains -Html $payments.Content -Needle ('/history?agent_user_id=' + $agentUserId) -Label '/payments opens')) { $failures += '/payments open' }

if (-not (Assert-Contains -Html $balance.Content -Needle ('<strong>' + $agentFullName + '</strong>') -Label '/history header FIO')) { $failures += '/history header' }
if (-not (Assert-Contains -Html $balance.Content -Needle ('/payments?agent_user_id=' + $agentUserId) -Label '/history opens')) { $failures += '/history open' }

Write-Output ('SELECTED_AGENT=' + $agentUserId)
Write-Output ('EXPECTED_AGENT_FIO=' + $agentFullName)
if ($requestAgentUserId -ne $agentUserId) {
    Write-Output ('REQUEST_AGENT=' + $requestAgentUserId)
    Write-Output ('REQUEST_AGENT_FIO=' + $requestAgentFullName)
}
if ($takenByName -ne '') {
    Write-Output ('TAKEN_BY_NAME=' + $takenByName)
}
if ($takenByFullName -ne '') {
    Write-Output ('TAKEN_BY_EXPECTED_FIO=' + $takenByFullName)
}

if ($failures.Count -gt 0) {
    Write-Output ('UI manual check FAILED: ' + ($failures -join ', '))
    exit 1
}

Write-Output 'UI manual check PASSED'
exit 0











