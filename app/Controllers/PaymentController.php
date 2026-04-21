<?php

namespace App\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Services\AuthService;
use Framework\Core\Controller;
use Framework\Core\Database;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class PaymentController extends Controller
{
    public function index(Request $request, AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth);

        if ($context === null) {
            return $this->redirectAgentNotFound($auth);
        }

        $success = $_SESSION['payments_success'] ?? null;
        $error = $_SESSION['payments_error'] ?? null;

        unset($_SESSION['payments_success'], $_SESSION['payments_error']);

        return $this->view('payments.index', [
            'title' => 'Платежи',
            'agent' => $context['agent'],
            'payments' => Payment::forAgentUser($context['agent_user_id']),
            'success' => $success,
            'error' => $error,
            'isAdminMode' => $context['is_admin_mode'],
            'agentUserId' => $context['agent_user_id'],
        ]);
    }

    public function create(Request $request, AuthService $auth): string|Response
    {
        if ($auth->user() === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth);

        if ($context === null) {
            return $this->redirectAgentNotFound($auth);
        }

        $errors = $_SESSION['payments_create_errors'] ?? [];
        $old = $_SESSION['payments_create_old'] ?? [];

        unset($_SESSION['payments_create_errors'], $_SESSION['payments_create_old']);

        return $this->view('payments.create', [
            'title' => 'Создать платеж',
            'agent' => $context['agent'],
            'errors' => $errors,
            'old' => $old,
            'isAdminMode' => $context['is_admin_mode'],
            'agentUserId' => $context['agent_user_id'],
        ]);
    }

    public function store(Request $request, AuthService $auth): Response
    {
        if ($auth->user() === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth, true);

        if ($context === null) {
            return $this->redirectAgentNotFound($auth);
        }

        $agentUserId = (int) $context['agent_user_id'];

        $payload = $this->payloadFromRequest($request, $agentUserId);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $_SESSION['payments_create_errors'] = $errors;
            $_SESSION['payments_create_old'] = $payload;

            return Response::redirect($this->paymentsCreateUrl($context));
        }

        try {
            Payment::create([
                'agent_user_id' => $agentUserId,
                'amount' => $this->normalizeAmount($payload['amount']),
                'payment_date' => $payload['payment_date'],
                'status' => $payload['status'],
                'note' => $payload['note'] !== '' ? $payload['note'] : null,
            ]);
        } catch (\Throwable) {
            $_SESSION['payments_create_errors'] = [
                '_form' => 'Не удалось сохранить платеж. Проверьте введенные данные и попробуйте снова.',
            ];
            $_SESSION['payments_create_old'] = $payload;

            return Response::redirect($this->paymentsCreateUrl($context));
        }

        unset($_SESSION['payments_create_errors'], $_SESSION['payments_create_old']);
        $_SESSION['payments_success'] = 'Платеж успешно создан.';

        return Response::redirect($this->paymentsIndexUrl($context));
    }

    public function show(Request $request, AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth);
        $payment = $this->resolvePayment($request, $auth, $context);

        if ($payment === null) {
            return $this->redirectPaymentNotFound($auth, $context);
        }

        return $this->view('payments.show', [
            'title' => 'Платеж',
            'payment' => $payment,
            'agent' => $context['agent'],
            'success' => $_SESSION['payments_success'] ?? null,
            'error' => $_SESSION['payments_error'] ?? null,
            'isAdminMode' => $context['is_admin_mode'],
            'agentUserId' => $context['agent_user_id'],
        ]);
    }

    public function edit(Request $request, AuthService $auth): string|Response
    {
        if ($auth->user() === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth);
        $payment = $this->resolvePayment($request, $auth, $context);

        if ($payment === null) {
            return $this->redirectPaymentNotFound($auth, $context);
        }

        $errors = $_SESSION['payments_edit_errors'] ?? [];
        $old = $_SESSION['payments_edit_old'] ?? [];

        unset($_SESSION['payments_edit_errors'], $_SESSION['payments_edit_old']);

        return $this->view('payments.edit', [
            'title' => 'Изменить платеж',
            'payment' => $payment,
            'agent' => $context['agent'],
            'errors' => $errors,
            'old' => $old,
            'isAdminMode' => $context['is_admin_mode'],
            'agentUserId' => $context['agent_user_id'],
        ]);
    }

    public function update(Request $request, AuthService $auth): Response
    {
        if ($auth->user() === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth, true);
        $payment = $this->resolvePayment($request, $auth, $context);

        if ($payment === null) {
            return $this->redirectPaymentNotFound($auth, $context);
        }

        $payload = $this->payloadFromRequest($request, $context['agent_user_id']);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $_SESSION['payments_edit_errors'] = $errors;
            $_SESSION['payments_edit_old'] = $payload;

            return Response::redirect($this->paymentsEditUrl((int) $payment->id, $context));
        }

        try {
            $payment->amount = $this->normalizeAmount($payload['amount']);
            $payment->payment_date = $payload['payment_date'];
            $payment->status = $payload['status'];
            $payment->note = $payload['note'] !== '' ? $payload['note'] : null;
            $payment->save();
        } catch (\Throwable) {
            $_SESSION['payments_edit_errors'] = [
                '_form' => 'Не удалось обновить платеж. Проверьте введенные данные и попробуйте снова.',
            ];
            $_SESSION['payments_edit_old'] = $payload;

            return Response::redirect($this->paymentsEditUrl((int) $payment->id, $context));
        }

        unset($_SESSION['payments_edit_errors'], $_SESSION['payments_edit_old']);
        $_SESSION['payments_success'] = 'Платеж успешно обновлен.';

        return Response::redirect($this->paymentsShowUrl((int) $payment->id, $context));
    }

    public function destroy(Request $request, AuthService $auth): Response
    {
        if ($auth->user() === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth, true);
        $payment = $this->resolvePayment($request, $auth, $context);

        if ($payment === null) {
            return $this->redirectPaymentNotFound($auth, $context);
        }

        $payment->delete();

        $_SESSION['payments_success'] = 'Платеж успешно удален.';

        return Response::redirect($this->paymentsIndexUrl($context));
    }

    private function resolveContext(Request $request, AuthService $auth, bool $fromPost = false): ?array
    {
        $user = $auth->user();

        if ($user === null) {
            return null;
        }

        $isAdmin = $auth->hasRole('admin');
        $agentUserId = (int) $request->input('agent_user_id', 0);

        if ($isAdmin && $agentUserId > 0) {
            $agent = User::findAgentById($agentUserId);

            if ($agent === null) {
                return null;
            }

            return [
                'is_admin_mode' => true,
                'agent_user_id' => (int) $agent->id,
                'agent' => $agent,
            ];
        }

        if ((string) $user->role !== 'agent') {
            return null;
        }

        $agent = User::findAgentById((int) $user->id);

        if ($agent === null) {
            return null;
        }

        return [
            'is_admin_mode' => false,
            'agent_user_id' => (int) $user->id,
            'agent' => $agent,
        ];
    }

    private function resolvePayment(Request $request, AuthService $auth, ?array $context): ?Payment
    {
        if ($context === null) {
            return null;
        }

        $user = $auth->user();

        if ($user === null) {
            return null;
        }

        $paymentId = (int) $request->input('id', 0);

        return Payment::findAccessible(
            $paymentId,
            (int) $user->id,
            $context['is_admin_mode'],
            $context['agent_user_id']
        );
    }

    private function payloadFromRequest(Request $request, int $agentUserId): array
    {
        return [
            'agent_user_id' => $agentUserId,
            'amount' => trim((string) $request->input('amount', '')),
            'payment_date' => trim((string) $request->input('payment_date', '')),
            'status' => trim((string) $request->input('status', 'pending')),
            'note' => trim((string) $request->input('note', '')),
        ];
    }

    private function validatePayload(array $payload): array
    {
        $errors = [];

        $amountRaw = $payload['amount'];
        $amount = $this->normalizeAmount($amountRaw);

        if ($amountRaw === '') {
            $errors['amount'] = 'Сумма обязательна.';
        } elseif (!preg_match('/^\d{1,10}([.,]\d{1,2})?$/', $amountRaw)) {
            $errors['amount'] = 'Формат суммы: до 10 цифр и при необходимости 1-2 знака после разделителя.';
        } elseif ((float) $amount <= 0) {
            $errors['amount'] = 'Сумма должна быть больше нуля.';
        }

        if ($payload['payment_date'] === '') {
            $errors['payment_date'] = 'Дата платежа обязательна.';
        } else {
            $date = \DateTime::createFromFormat('Y-m-d', $payload['payment_date']);

            if (!$date || $date->format('Y-m-d') !== $payload['payment_date']) {
                $errors['payment_date'] = 'Дата платежа должна быть в формате ГГГГ-ММ-ДД.';
            }
        }

        $allowedStatuses = ['pending', 'paid', 'failed'];

        if ($payload['status'] === '') {
            $errors['status'] = 'Статус обязателен.';
        } elseif (!in_array($payload['status'], $allowedStatuses, true)) {
            $errors['status'] = 'Выберите корректный статус.';
        }

        if (strlen($payload['note']) > 1000) {
            $errors['note'] = 'Примечание не должно превышать 1000 символов.';
        }

        return $errors;
    }

    private function normalizeAmount(string $amount): string
    {
        return str_replace(',', '.', $amount);
    }

    private function paymentsStorageReady(): bool
    {
        try {
            $db = Database::getConnection();
            $tableName = $db->query("SELECT to_regclass('payments')")->fetchColumn();

            return $tableName !== false && $tableName !== null && $tableName !== '';
        } catch (\Throwable) {
            return false;
        }
    }

    private function redirectStorageError(AuthService $auth): Response
    {
        $_SESSION['payments_error'] = 'Раздел платежей недоступен. Выполните миграции базы данных.';

        if ($auth->hasRole('admin')) {
            return Response::redirect('/admin/agents');
        }

        return Response::redirect('/dashboard');
    }

    private function redirectAgentNotFound(AuthService $auth): Response
    {
        $_SESSION['agents_error'] = 'Агент не найден.';

        if ($auth->hasRole('admin')) {
            return Response::redirect('/admin/agents');
        }

        return Response::redirect('/dashboard');
    }

    private function redirectPaymentNotFound(AuthService $auth, ?array $context): Response
    {
        $_SESSION['payments_error'] = 'Платеж не найден.';

        if ($context !== null) {
            return Response::redirect($this->paymentsIndexUrl($context));
        }

        if ($auth->hasRole('admin')) {
            return Response::redirect('/admin/agents');
        }

        return Response::redirect('/payments');
    }

    private function paymentsIndexUrl(array $context): string
    {
        if ($context['is_admin_mode']) {
            return '/admin/agents/payments?agent_user_id=' . (int) $context['agent_user_id'];
        }

        return '/payments';
    }

    private function paymentsCreateUrl(array $context): string
    {
        if ($context['is_admin_mode']) {
            return '/payments/create?agent_user_id=' . (int) $context['agent_user_id'];
        }

        return '/payments/create';
    }

    private function paymentsShowUrl(int $paymentId, array $context): string
    {
        if ($context['is_admin_mode']) {
            return '/payments/show?id=' . $paymentId . '&agent_user_id=' . (int) $context['agent_user_id'];
        }

        return '/payments/show?id=' . $paymentId;
    }

    private function paymentsEditUrl(int $paymentId, array $context): string
    {
        if ($context['is_admin_mode']) {
            return '/payments/edit?id=' . $paymentId . '&agent_user_id=' . (int) $context['agent_user_id'];
        }

        return '/payments/edit?id=' . $paymentId;
    }
}
