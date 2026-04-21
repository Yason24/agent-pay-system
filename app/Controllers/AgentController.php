<?php

namespace App\Controllers;

use App\Models\Payment;
use App\Services\AuthService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class AgentController extends Controller
{
    public function index(AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        if ($auth->hasRole('admin')) {
            return Response::redirect('/admin/agents');
        }

        if ((string) $user->role !== 'agent') {
            $_SESSION['app_error'] = 'Раздел агентов доступен только пользователям с ролью "Агент".';

            return Response::redirect('/dashboard');
        }

        $payments = Payment::forAgentUser((int) $user->id);
        $totalAmount = 0.0;
        $paidAmount = 0.0;
        $pendingAmount = 0.0;
        $failedAmount = 0.0;

        foreach ($payments as $payment) {
            $amount = (float) ($payment->amount ?? 0);
            $totalAmount += $amount;

            $status = (string) ($payment->status ?? '');

            if ($status === 'paid') {
                $paidAmount += $amount;
            } elseif ($status === 'pending') {
                $pendingAmount += $amount;
            } elseif ($status === 'failed') {
                $failedAmount += $amount;
            }
        }

        $paymentSummary = [
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'pending_amount' => $pendingAmount,
            'failed_amount' => $failedAmount,
            'payments_count' => $payments->count(),
        ];

        return $this->view('agents.show', [
            'title' => 'Кабинет агента',
            'agent' => $user,
            'paymentSummary' => $paymentSummary,
            'latestPayments' => $payments,
        ]);
    }

    public function create(AuthService $auth): Response
    {
        return $this->legacyDisabled($auth);
    }

    public function store(Request $request, AuthService $auth): Response
    {
        return $this->legacyDisabled($auth);
    }

    public function show(Request $request, AuthService $auth): Response
    {
        return $this->legacyDisabled($auth);
    }

    public function edit(Request $request, AuthService $auth): Response
    {
        return $this->legacyDisabled($auth);
    }

    public function update(Request $request, AuthService $auth): Response
    {
        return $this->legacyDisabled($auth);
    }

    public function destroy(Request $request, AuthService $auth): Response
    {
        return $this->legacyDisabled($auth);
    }

    private function legacyDisabled(AuthService $auth): Response
    {
        if ($auth->guest()) {
            return Response::redirect('/login');
        }

        $_SESSION['app_error'] = 'Управление legacy-агентами отключено. Используйте раздел платежей.';

        return Response::redirect('/payments');
    }
}


