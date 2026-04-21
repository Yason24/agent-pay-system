<?php

namespace App\Controllers;

use App\Models\Request as AgentRequest;
use App\Models\User;
use App\Services\AuthService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class RequestController extends Controller
{
    // -------------------------------------------------------------------------
    // Agent: GET /my/requests
    // -------------------------------------------------------------------------

    public function myIndex(AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if ((string) $user->role !== 'agent') {
            return new Response('Forbidden', 403);
        }

        $requests = AgentRequest::forAgentUser((int) $user->id);

        return $this->view('requests.index', [
            'title'       => 'Мои заявки',
            'requests'    => $requests,
            'isAgentMode' => true,
            'agentUserId' => (int) $user->id,
            'agent'       => $user,
        ]);
    }

    // -------------------------------------------------------------------------
    // Staff: GET /requests?agent_user_id=
    // -------------------------------------------------------------------------

    public function index(Request $request, AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if (!$auth->hasAnyRole(['admin', 'accountant', 'dispatcher'])) {
            return new Response('Forbidden', 403);
        }

        $agentUserId = (int) $request->input('agent_user_id', 0);

        if ($agentUserId <= 0) {
            return new Response('agent_user_id is required', 400);
        }

        $agent = User::find($agentUserId);

        if ($agent === null || (string) $agent->role !== 'agent') {
            $_SESSION['agents_error'] = 'Агент не найден.';

            return redirect('/agents');
        }

        $requests = AgentRequest::forAgentUser($agentUserId);

        return $this->view('requests.index', [
            'title'       => 'Заявки агента',
            'requests'    => $requests,
            'isAgentMode' => false,
            'agentUserId' => $agentUserId,
            'agent'       => $agent,
        ]);
    }

    // -------------------------------------------------------------------------
    // Agent: GET /requests/create
    // -------------------------------------------------------------------------

    public function create(AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if ((string) $user->role !== 'agent') {
            return new Response('Forbidden', 403);
        }

        $success = $_SESSION['requests_success'] ?? null;
        $error   = $_SESSION['requests_error'] ?? null;
        $old     = $_SESSION['requests_old'] ?? [];

        unset($_SESSION['requests_success'], $_SESSION['requests_error'], $_SESSION['requests_old']);

        return $this->view('requests.create', [
            'title'   => 'Создать заявку',
            'success' => $success,
            'error'   => $error,
            'old'     => $old,
        ]);
    }

    // -------------------------------------------------------------------------
    // Agent: POST /requests/store
    // -------------------------------------------------------------------------

    public function store(Request $request, AuthService $auth): Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if ((string) $user->role !== 'agent') {
            return new Response('Forbidden', 403);
        }

        $amountRaw   = $this->sanitizeAmountInput((string) $request->input('amount', ''));
        $paymentLink = $this->sanitizeText((string) $request->input('payment_link', ''));
        $comment     = $this->sanitizeText((string) $request->input('comment', ''));

        // --- validate ---
        $amountError = '';

        if ($amountRaw === '') {
            $amountError = 'Сумма обязательна.';
        } elseif (!preg_match('/^\d{1,10}([.,]\d{1,2})?$/', $amountRaw)) {
            $amountError = 'Формат суммы: до 10 цифр, не более 2 знаков после разделителя.';
        } elseif ((float) str_replace(',', '.', $amountRaw) <= 0) {
            $amountError = 'Сумма должна быть больше нуля.';
        }

        if ($amountError !== '') {
            $_SESSION['requests_error'] = $amountError;
            $_SESSION['requests_old']   = [
                'amount'       => $amountRaw,
                'payment_link' => $paymentLink,
                'comment'      => $comment,
            ];

            return redirect('/requests/create');
        }

        $amount = (float) str_replace(',', '.', $amountRaw);

        try {
            AgentRequest::createForAgentUser((int) $user->id, [
                'requested_amount' => $amount,
                'payment_link'     => $paymentLink,
                'comment'          => $comment,
                'status'           => 'new',
            ]);
        } catch (\Throwable) {
            $_SESSION['requests_error'] = 'Не удалось сохранить заявку. Попробуйте снова.';
            $_SESSION['requests_old']   = [
                'amount'       => $amountRaw,
                'payment_link' => $paymentLink,
                'comment'      => $comment,
            ];

            return redirect('/requests/create');
        }

        $_SESSION['requests_success'] = 'Заявка успешно создана.';

        return redirect('/my/requests');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function sanitizeText(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return trim($value);
    }

    private function sanitizeAmountInput(string $amount): string
    {
        $normalized = trim($amount);

        return str_replace(["\u{00A0}", "\u{202F}", ' '], '', $normalized);
    }
}
