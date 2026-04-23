<?php

namespace App\Controllers;

use App\Models\Payment;
use App\Services\AuthService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;

class AgentController extends Controller
{
    public function index(AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if ($auth->hasAnyRole(['admin', 'accountant', 'dispatcher'])) {
            return redirect('/agents');
        }

        if ((string) $user->role !== 'agent') {
            $_SESSION['app_error'] = 'Раздел агентов доступен только пользователям с ролью "Агент".';

            return redirect('/dashboard');
        }

        $emptySummary = [
            'total_amount'   => 0.0,
            'paid_amount'    => 0.0,
            'pending_amount' => 0.0,
            'failed_amount'  => 0.0,
            'payments_count' => 0,
        ];

        try {
            $paymentSummary = Payment::summaryForAgentUser((int) $user->id);
        } catch (\Throwable) {
            $paymentSummary = $emptySummary;
        }

        return $this->view('cabinet.index', [
            'title'          => 'Кабинет агента',
            'agent'          => $user,
            'agentFullName'  => $user->fullName() !== '' ? $user->fullName() : '—',
            'paymentSummary' => $paymentSummary,
        ]);
    }
}

