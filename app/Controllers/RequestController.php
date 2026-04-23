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
            'title' => 'Мои заявки',
            'page_title' => 'Мои заявки',
            'requests' => $requests,
            'isAgentMode' => true,
            'is_agent' => true,
            'canManage' => false,
            'can_manage' => false,
            'agentUserId' => (int) $user->id,
            'agent_full_name' => $this->userFullName($user),
        ]);
    }

    public function index(Request $request, AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if (!$auth->hasAnyRole(['admin', 'accountant', 'dispatcher'])) {
            return new Response('Forbidden', 403);
        }

        $context = $this->resolveStaffContext($request);

        if ($context instanceof Response) {
            return $context;
        }

        $requests = AgentRequest::forAgentUser($context['agentUserId']);

        return $this->view('requests.index', [
            'title' => 'Заявки',
            'page_title' => 'Заявки',
            'requests' => $requests,
            'isAgentMode' => false,
            'is_agent' => false,
            'canManage' => true,
            'can_manage' => true,
            'agentUserId' => $context['agentUserId'],
            'agent_full_name' => $this->userFullName($context['agent']),
        ]);
    }

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
        $errors  = $_SESSION['requests_errors'] ?? [];
        $old     = $_SESSION['requests_old'] ?? [];

        unset($_SESSION['requests_success'], $_SESSION['requests_error'], $_SESSION['requests_errors'], $_SESSION['requests_old']);

        return $this->view('requests.create', [
            'title' => 'Создать заявку',
            'success' => $success,
            'error' => $error,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    public function store(Request $request, AuthService $auth): Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if ((string) $user->role !== 'agent') {
            return new Response('Forbidden', 403);
        }

        $amountRaw = $this->sanitizeAmountInput((string) $request->input('amount', ''));
        $paymentLink = $this->sanitizeText((string) $request->input('payment_link', ''));
        $comment = $this->sanitizeText((string) $request->input('comment', ''));

        $errors = [];

        if ($amountRaw === '') {
            $errors['amount'] = 'Укажите сумму.';
        } elseif (!preg_match('/^\d{1,10}([.,]\d{1,2})?$/', $amountRaw)) {
            $errors['amount'] = 'Некорректный формат суммы. Пример: 10000,50.';
        } elseif ((float) str_replace(',', '.', $amountRaw) <= 0) {
            $errors['amount'] = 'Сумма должна быть больше нуля.';
        }

        if ($paymentLink !== '' && !$this->isValidHttpUrl($paymentLink)) {
            $errors['payment_link'] = 'Укажите корректную ссылку (URL).';
        }

        if ($errors !== []) {
            $_SESSION['requests_error'] = 'Проверьте заполнение формы.';
            $_SESSION['requests_errors'] = $errors;
            $_SESSION['requests_old'] = [
                'amount' => $amountRaw,
                'payment_link' => $paymentLink,
                'comment' => $comment,
            ];

            return redirect('/requests/create');
        }

        $amount = (float) str_replace(',', '.', $amountRaw);

        $created = AgentRequest::createForAgentUser((int) $user->id, [
            'requested_amount' => $amount,
            'amount' => $amount,
            'payment_link' => $paymentLink,
            'link' => $paymentLink,
            'comment' => $comment,
            'topic' => 'Payment request',
            'status' => 'new',
        ]);

        if (!$created) {
            $source = AgentRequest::lastCreateError();
            $_SESSION['requests_error'] = 'Не удалось сохранить заявку. Попробуйте снова.';

            if ($source !== null && $source !== '') {
                $_SESSION['requests_error'] .= ' Причина: ' . $source;
            }

            $_SESSION['requests_old'] = [
                'amount' => $amountRaw,
                'payment_link' => $paymentLink,
                'comment' => $comment,
            ];

            return redirect('/requests/create');
        }

        unset($_SESSION['requests_errors'], $_SESSION['requests_old']);
        $_SESSION['requests_success'] = 'Заявка успешно создана.';

        return redirect('/my/requests');
    }

    public function take(Request $request, AuthService $auth): Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if (!$auth->hasAnyRole(['dispatcher', 'accountant', 'admin'])) {
            return new Response('Forbidden', 403);
        }

        $context = $this->resolveStaffContext($request);

        if ($context instanceof Response) {
            return $context;
        }

        $requestId = (int) $request->input('request_id', 0);
        $agentUserId = (int) $context['agentUserId'];
        $backUrl = '/requests?agent_user_id=' . $agentUserId;

        if ($requestId <= 0) {
            return new Response('request_id is required', 400);
        }

        $target = AgentRequest::findForAgentUser($requestId, $agentUserId);

        if ($target === null) {
            return new Response('Request not found', 404);
        }

        if ($this->normalizeStatus((string) $target->status) !== 'new') {
            return new Response('Request is already taken', 400);
        }

        if ((int) $target->taken_by_user_id > 0) {
            return new Response('Request is already taken', 400);
        }

        $taken = AgentRequest::updateStatusForAgentUser(
            $requestId,
            $agentUserId,
            'in_progress',
            [
                'taken_by_user_id' => (int) $user->id,
                'taken_by_name' => $this->userFullName($user),
            ]
        );

        if (!$taken) {
            $_SESSION['requests_error'] = 'Не удалось взять заявку в работу.';

            return redirect($backUrl);
        }

        $_SESSION['requests_success'] = 'Заявка взята в работу.';

        return redirect($backUrl);
    }

    public function complete(Request $request, AuthService $auth): Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if (!$auth->hasAnyRole(['dispatcher', 'accountant', 'admin'])) {
            return new Response('Forbidden', 403);
        }

        $context = $this->resolveStaffContext($request);

        if ($context instanceof Response) {
            return $context;
        }

        $requestId = (int) $request->input('request_id', 0);

        if ($requestId <= 0) {
            return new Response('request_id is required', 400);
        }

        $target = AgentRequest::findForAgentUser($requestId, (int) $context['agentUserId']);

        if ($target === null) {
            return new Response('Request not found', 404);
        }

        if ($this->normalizeStatus((string) $target->status) !== 'in_progress') {
            return new Response('Request is not in progress', 400);
        }

        AgentRequest::updateStatusForAgentUser(
            $requestId,
            (int) $context['agentUserId'],
            'paid',
            [
                'processed_by_user_id' => (int) $user->id,
                'processed_by_name' => $this->userFullName($user),
            ]
        );

        $_SESSION['requests_success'] = 'Заявка исполнена.';

        $backUrl = '/requests?agent_user_id=' . (int) $context['agentUserId'];

        return redirect($backUrl);
    }

    public function changeStatus(Request $request, AuthService $auth): Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if (!$auth->hasAnyRole(['dispatcher', 'accountant', 'admin'])) {
            return new Response('Forbidden', 403);
        }

        $context = $this->resolveStaffContext($request);

        if ($context instanceof Response) {
            return $context;
        }

        $agentUserId = (int) $context['agentUserId'];
        $requestId = (int) $request->input('request_id', 0);
        $newStatus = $this->normalizeStatus((string) $request->input('status', ''));

        if ($requestId <= 0) {
            return new Response('request_id is required', 400);
        }

        if (!in_array($newStatus, $this->allowedStatuses(), true)) {
            return new Response('Invalid status', 400);
        }

        $target = AgentRequest::findForAgentUser($requestId, $agentUserId);

        if ($target === null) {
            return new Response('Request not found', 404);
        }

        $currentStatus = $this->normalizeStatus((string) $target->status);

        if (in_array($currentStatus, ['paid', 'rejected', 'cancelled'], true)) {
            return new Response('Final status cannot be changed', 400);
        }

        if ($currentStatus === 'new' && $newStatus !== 'in_progress') {
            return new Response('New request can only be moved to in_progress', 400);
        }

        if ($currentStatus === 'in_progress' && !in_array($newStatus, ['paid', 'rejected', 'cancelled'], true)) {
            return new Response('In-progress request can only be finished', 400);
        }

        $extra = [];

        if ($newStatus === 'in_progress') {
            if ((int) $target->taken_by_user_id > 0) {
                return new Response('Request is already taken', 400);
            }

            $extra['taken_by_user_id'] = (int) $user->id;
            $extra['taken_by_name'] = $this->userFullName($user);
        }

        if (in_array($newStatus, ['paid', 'rejected', 'cancelled'], true)) {
            $extra['processed_by_user_id'] = (int) $user->id;
            $extra['processed_by_name'] = $this->userFullName($user);
        }

        $updated = AgentRequest::updateStatusForAgentUser($requestId, $agentUserId, $newStatus, $extra);

        if (!$updated) {
            return new Response('Unable to update request status', 500);
        }

        $_SESSION['requests_success'] = 'Статус заявки обновлен.';

        return redirect('/requests?agent_user_id=' . $agentUserId);
    }

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

    private function isValidHttpUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        return in_array($scheme, ['http', 'https'], true);
    }

    private function resolveStaffContext(Request $request): array|Response
    {
        $agentUserId = (int) $request->input('agent_user_id', 0);

        if ($agentUserId <= 0) {
            return new Response('agent_user_id is required', 400);
        }

        $agent = User::find($agentUserId);

        if ($agent === null || (string) $agent->role !== 'agent') {
            $_SESSION['agents_error'] = 'Агент не найден.';

            return redirect('/agents');
        }

        return [
            'agentUserId' => $agentUserId,
            'agent' => $agent,
        ];
    }

    private function userFullName(User $user): string
    {
        $fullName = trim(implode(' ', array_filter([
            trim((string) $user->last_name),
            trim((string) $user->first_name),
            trim((string) $user->middle_name),
        ], static fn (string $part): bool => $part !== '')));

        if ($fullName !== '') {
            return $fullName;
        }

        $fallback = trim((string) $user->name);

        return $fallback !== '' ? $fallback : '—';
    }

    private function allowedStatuses(): array
    {
        return ['new', 'in_progress', 'paid', 'rejected', 'cancelled'];
    }

    private function normalizeStatus(string $status): string
    {
        $value = strtolower(trim($status));

        return match ($value) {
            'in_work' => 'in_progress',
            'done' => 'paid',
            default => $value,
        };
    }
}
