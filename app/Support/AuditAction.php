<?php

namespace App\Support;

final class AuditAction
{
    public const LOGIN_SUCCESS      = 'login_success';
    public const LOGIN_FAILED       = 'login_failed';
    public const LOGOUT             = 'logout';

    public const USER_CREATE        = 'user.create';
    public const USER_UPDATE        = 'user.update';
    public const USER_DELETE        = 'user.delete';
    public const USER_ROLE_CHANGED  = 'user.role_changed';

    public const AGENT_CREATE       = 'agent.create';
    public const AGENT_UPDATE       = 'agent.update';
    public const AGENT_DELETE       = 'agent.delete';

    public const PAYMENT_CREATE     = 'payment.create';
    public const PAYMENT_UPDATE     = 'payment.update';
    public const PAYMENT_DELETE     = 'payment.delete';

    private function __construct()
    {
    }
}

