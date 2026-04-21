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
            return redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth);

        if ($context instanceof Response) {
            return $context;
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
            'isReadOnly' => false,
        ]);
    }

    public function myIndex(AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if ((string) $user->role !== 'agent') {
            return $this->forbidden('Раздел моих платежей доступен только агенту.');
        }

        if (!$this->paymentsStorageReady()) {
            $_SESSION['payments_error'] = 'Раздел платежей недоступен. Выполните миграции базы данных.';

            return redirect('/cabinet');
        }

        $success = $_SESSION['payments_success'] ?? null;
        $error = $_SESSION['payments_error'] ?? null;

        unset($_SESSION['payments_success'], $_SESSION['payments_error']);

        return $this->view('payments.index', [
            'title' => 'Мои платежи',
            'agent' => $user,
            'payments' => Payment::forAgentUser((int) $user->id),
            'success' => $success,
            'error' => $error,
            'isAdminMode' => false,
            'agentUserId' => (int) $user->id,
            'isReadOnly' => true,
        ]);
    }

    public function create(Request $request, AuthService $auth): string|Response
    {
        if ($auth->user() === null) {
            return redirect('/login');
        }

        if ($response = $this->ensurePaymentWriteAccess($auth)) {
            return $response;
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth);

        if ($context instanceof Response) {
            return $context;
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
            return redirect('/login');
        }

        if ($response = $this->ensurePaymentWriteAccess($auth)) {
            return $response;
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth);

        if ($context instanceof Response) {
            return $context;
        }

        $agentUserId = (int) $context['agent_user_id'];

        $payload = $this->payloadFromRequest($request, $agentUserId);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $_SESSION['payments_create_errors'] = $errors;
            $_SESSION['payments_create_old'] = $payload;

            return redirect($this->paymentsCreateUrl($context));
        }

        try {
            $createdPayment = Payment::createForAgentUser($agentUserId, [
                'amount' => $this->normalizeAmount($payload['amount']),
                'payment_date' => $payload['payment_date'],
                'status' => $payload['status'],
                'note' => $payload['note'] !== '' ? $payload['note'] : null,
            ]);

            if ($createdPayment === null) {
                throw new \RuntimeException('Payment creation failed.');
            }
        } catch (\Throwable) {
            $_SESSION['payments_create_errors'] = [
                '_form' => 'Не удалось сохранить платеж. Проверьте введенные данные и попробуйте снова.',
            ];
            $_SESSION['payments_create_old'] = $payload;

            return redirect($this->paymentsCreateUrl($context));
        }

        unset($_SESSION['payments_create_errors'], $_SESSION['payments_create_old']);
        $_SESSION['payments_success'] = 'Платеж успешно создан.';

        return redirect($this->paymentsIndexUrl($context));
    }

    public function show(Request $request, AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth);

        if ($context instanceof Response) {
            return $context;
        }

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
            return redirect('/login');
        }

        if ($response = $this->ensurePaymentWriteAccess($auth)) {
            return $response;
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth);

        if ($context instanceof Response) {
            return $context;
        }

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
            return redirect('/login');
        }

        if ($response = $this->ensurePaymentWriteAccess($auth)) {
            return $response;
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth);

        if ($context instanceof Response) {
            return $context;
        }

        $payment = $this->resolvePayment($request, $auth, $context);

        if ($payment === null) {
            return $this->redirectPaymentNotFound($auth, $context);
        }

        $payload = $this->payloadFromRequest($request, $context['agent_user_id']);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $_SESSION['payments_edit_errors'] = $errors;
            $_SESSION['payments_edit_old'] = $payload;

            return redirect($this->paymentsEditUrl((int) $payment->id, $context));
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

            return redirect($this->paymentsEditUrl((int) $payment->id, $context));
        }

        unset($_SESSION['payments_edit_errors'], $_SESSION['payments_edit_old']);
        $_SESSION['payments_success'] = 'Платеж успешно обновлен.';

        return redirect($this->paymentsShowUrl((int) $payment->id, $context));
    }

    public function destroy(Request $request, AuthService $auth): Response
    {
        if ($auth->user() === null) {
            return redirect('/login');
        }

        if ($response = $this->ensurePaymentWriteAccess($auth)) {
            return $response;
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError($auth);
        }

        $context = $this->resolveContext($request, $auth);

        if ($context instanceof Response) {
            return $context;
        }

        $payment = $this->resolvePayment($request, $auth, $context);

        if ($payment === null) {
            return $this->redirectPaymentNotFound($auth, $context);
        }

        $payment->delete();

        $_SESSION['payments_success'] = 'Платеж успешно удален.';

        return redirect($this->paymentsIndexUrl($context));
    }

    private function resolveContext(Request $request, AuthService $auth): array|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if ((string) $user->role === 'agent') {
            return [
                'is_admin_mode' => false,
                'agent_user_id' => (int) $auth->id(),
                'agent' => $user,
            ];
        }

        $isStaff = $auth->hasAnyRole(['admin', 'accountant', 'dispatcher']);

        if (!$isStaff) {
            return $this->forbidden('У вас нет доступа к платежам.');
        }

        $agentUserId = (int) $request->input('agent_user_id', 0);

        if ($agentUserId <= 0) {
            return new Response('agent_user_id is required', 400);
        }

        $agent = User::find($agentUserId);

        if ($agent === null) {
            return new Response('Agent not found', 404);
        }

        if ((string) $agent->role !== 'agent') {
            return $this->forbidden('Выбранный пользователь не является агентом.');
        }

        return [
            'is_admin_mode' => true,
            'agent_user_id' => (int) $agent->id,
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
            (bool) ($context['is_admin_mode'] ?? false),
            isset($context['agent_user_id']) ? (int) $context['agent_user_id'] : null
        );
    }

    private function payloadFromRequest(Request $request, int $agentUserId): array
    {
        return [
            'agent_user_id' => $agentUserId,
            'amount' => $this->sanitizeAmountInput((string) $request->input('amount', '')),
            'payment_date' => trim((string) $request->input('payment_date', '')),
            'status' => trim((string) $request->input('status', 'pending')),
            'note' => $this->sanitizeNote((string) $request->input('note', '')),
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
        return str_replace(',', '.', $this->sanitizeAmountInput($amount));
    }

    private function sanitizeAmountInput(string $amount): string
    {
        $normalized = trim($amount);

        return str_replace(["\u{00A0}", "\u{202F}", ' '], '', $normalized);
    }

    private function sanitizeNote(string $note): string
    {
        $sanitized = trim($note);
        $sanitized = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $sanitized) ?? $sanitized;
        $sanitized = preg_replace('/\s{2,}/u', ' ', $sanitized) ?? $sanitized;

        return trim($sanitized);
    }

    private function paymentsStorageReady(): bool
    {
        try {
            $db = Database::getConnection();
            $tableName = $db->query("SELECT to_regclass('payments')")->fetchColumn();

            if (in_array($tableName, [false, null, ''], true)) {
                return false;
            }

            $columnExists = $db->query(
                "SELECT CASE WHEN EXISTS (
                    SELECT 1
                    FROM information_schema.columns
                    WHERE table_schema = 'public'
                      AND table_name = 'payments'
                      AND column_name = 'agent_user_id'
                ) THEN 1 ELSE 0 END"
            )->fetchColumn();

            return (int) $columnExists === 1;
        } catch (\Throwable) {
            return false;
        }
    }

    private function redirectStorageError(AuthService $auth): Response
    {
        $_SESSION['payments_error'] = 'Раздел платежей недоступен. Выполните миграции базы данных.';

        if ($auth->hasAnyRole(['admin', 'accountant', 'dispatcher'])) {
            return redirect('/agents');
        }

        return redirect('/cabinet');
    }

    private function redirectAgentNotFound(AuthService $auth): Response
    {
        $_SESSION['agents_error'] = 'Агент не найден.';

        if ($auth->hasAnyRole(['admin', 'accountant', 'dispatcher'])) {
            return redirect('/agents');
        }

        return redirect('/cabinet');
    }

    private function redirectPaymentNotFound(AuthService $auth, ?array $context): Response
    {
        $_SESSION['payments_error'] = 'Платеж не найден.';

        if ($context !== null) {
            return redirect($this->paymentsIndexUrl($context));
        }

        if ($auth->hasAnyRole(['admin', 'accountant', 'dispatcher'])) {
            return redirect('/agents');
        }

        return redirect('/my/payments');
    }

    private function ensurePaymentWriteAccess(AuthService $auth): ?Response
    {
        if (!$auth->hasAnyRole(['admin', 'accountant'])) {
            return $this->forbidden('Создание и изменение платежей доступны только администратору и бухгалтеру.');
        }

        return null;
    }

    private function forbidden(string $message): Response
    {
        return new Response($message, 403);
    }

    private function paymentsIndexUrl(array $context): string
    {
        if ($context['is_admin_mode']) {
            return '/payments?agent_user_id=' . (int) $context['agent_user_id'];
        }

        return '/my/payments';
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
