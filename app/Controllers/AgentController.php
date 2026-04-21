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

        try {
            $paymentSummary = Payment::summaryForAgentUser((int) $user->id);
            $latestPayments = Payment::latestForAgentUser((int) $user->id, 5);
        } catch (\Throwable) {
            $_SESSION['app_error'] = 'Раздел платежей временно недоступен. Выполните миграции базы данных.';

            return $this->view('agents.show', [
                'title' => 'Кабинет агента',
                'agent' => $user,
                'paymentSummary' => [
                    'total_amount' => 0.0,
                    'paid_amount' => 0.0,
                    'pending_amount' => 0.0,
                    'failed_amount' => 0.0,
                    'payments_count' => 0,
                ],
                'latestPayments' => new \Framework\Core\Collection([]),
            ]);
        }

        return $this->view('agents.show', [
            'title' => 'Кабинет агента',
            'agent' => $user,
            'paymentSummary' => $paymentSummary,
            'latestPayments' => $latestPayments,
        ]);
    }

}



