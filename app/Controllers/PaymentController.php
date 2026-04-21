<?php

namespace App\Controllers;

use App\Models\Agent;
use App\Models\Payment;
use App\Services\AuditLogger;
use App\Services\AuthService;
use App\Support\AuditAction;
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
            return $this->redirectStorageError();
        }

        $agent = $this->resolveAgentFromRequest($request, (int) $user->id);

        if ($agent === null) {
            $_SESSION['agents_error'] = 'Агент не найден.';

            return Response::redirect('/agents');
        }

        $success = $_SESSION['payments_success'] ?? null;
        $error = $_SESSION['payments_error'] ?? null;

        unset($_SESSION['payments_success'], $_SESSION['payments_error']);

        return $this->view('payments.index', [
            'title' => 'Платежи',
            'agent' => $agent,
            'payments' => Payment::forAgentAndUser((int) $agent->id, (int) $user->id),
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function create(Request $request, AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError();
        }

        $agent = $this->resolveAgentFromRequest($request, (int) $user->id);

        if ($agent === null) {
            $_SESSION['agents_error'] = 'Агент не найден.';

            return Response::redirect('/agents');
        }

        $errors = $_SESSION['payments_create_errors'] ?? [];
        $old = $_SESSION['payments_create_old'] ?? [];

        unset($_SESSION['payments_create_errors'], $_SESSION['payments_create_old']);

        return $this->view('payments.create', [
            'title' => 'Создать платеж',
            'agent' => $agent,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    public function store(Request $request, AuthService $auth, AuditLogger $audit): Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError();
        }

        $agentId = (int) $request->input('agent_id', 0);
        $agent = Agent::findForUser($agentId, (int) $user->id);

        if ($agent === null) {
            $_SESSION['agents_error'] = 'Агент не найден.';

            return Response::redirect('/agents');
        }

        $payload = $this->payloadFromRequest($request, $agentId);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $_SESSION['payments_create_errors'] = $errors;
            $_SESSION['payments_create_old'] = $payload;

            return Response::redirect('/payments/create?agent_id=' . $agentId);
        }

        try {
            $payment = Payment::create([
                'agent_id' => $agentId,
                'amount' => $this->normalizeAmount($payload['amount']),
                'payment_date' => $payload['payment_date'],
                'status' => $payload['status'],
                'note' => $payload['note'] !== '' ? $payload['note'] : null,
            ]);
        } catch (\Throwable) {
            $_SESSION['payments_create_errors'] = [
                '_form' => 'Не удалось сохранить платеж. Проверьте введённые данные и попробуйте снова.',
            ];
            $_SESSION['payments_create_old'] = $payload;

            return Response::redirect('/payments/create?agent_id=' . $agentId);
        }

        $audit->log(AuditAction::PAYMENT_CREATE, $request, $auth, [
            'entity_type' => 'payment',
            'entity_id' => (int) $payment->id,
            'meta' => [
                'snapshot' => $this->paymentSnapshot($payment),
            ],
        ]);

        unset($_SESSION['payments_create_errors'], $_SESSION['payments_create_old']);
        $_SESSION['payments_success'] = 'Платеж успешно создан.';

        return Response::redirect('/payments?agent_id=' . $agentId);
    }

    public function show(Request $request, AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError();
        }

        $payment = Payment::findForUser((int) $request->input('id', 0), (int) $user->id);

        if ($payment === null) {
            return $this->redirectPaymentNotFound($request, (int) $user->id);
        }

        $agent = Agent::find((int) $payment->agent_id);

        return $this->view('payments.show', [
            'title' => 'Платеж',
            'payment' => $payment,
            'agent' => $agent,
            'success' => $_SESSION['payments_success'] ?? null,
            'error' => $_SESSION['payments_error'] ?? null,
        ]);
    }

    public function edit(Request $request, AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError();
        }

        $payment = Payment::findForUser((int) $request->input('id', 0), (int) $user->id);

        if ($payment === null) {
            return $this->redirectPaymentNotFound($request, (int) $user->id);
        }

        $agent = Agent::find((int) $payment->agent_id);
        $errors = $_SESSION['payments_edit_errors'] ?? [];
        $old = $_SESSION['payments_edit_old'] ?? [];

        unset($_SESSION['payments_edit_errors'], $_SESSION['payments_edit_old']);

        return $this->view('payments.edit', [
            'title' => 'Изменить платеж',
            'payment' => $payment,
            'agent' => $agent,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    public function update(Request $request, AuthService $auth, AuditLogger $audit): Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError();
        }

        $payment = Payment::findForUser((int) $request->input('id', 0), (int) $user->id);

        if ($payment === null) {
            return $this->redirectPaymentNotFound($request, (int) $user->id);
        }

        $payload = $this->payloadFromRequest($request, (int) $payment->agent_id);
        $errors = $this->validatePayload($payload);

        if ($errors !== []) {
            $_SESSION['payments_edit_errors'] = $errors;
            $_SESSION['payments_edit_old'] = $payload;

            return Response::redirect('/payments/edit?id=' . (int) $payment->id);
        }

        $before = $this->paymentSnapshot($payment);

        try {
            $payment->amount = $this->normalizeAmount($payload['amount']);
            $payment->payment_date = $payload['payment_date'];
            $payment->status = $payload['status'];
            $payment->note = $payload['note'] !== '' ? $payload['note'] : null;
            $payment->save();
        } catch (\Throwable) {
            $_SESSION['payments_edit_errors'] = [
                '_form' => 'Не удалось обновить платеж. Проверьте введённые данные и попробуйте снова.',
            ];
            $_SESSION['payments_edit_old'] = $payload;

            return Response::redirect('/payments/edit?id=' . (int) $payment->id);
        }

        $after = $this->paymentSnapshot($payment);

        $audit->log(AuditAction::PAYMENT_UPDATE, $request, $auth, [
            'entity_type' => 'payment',
            'entity_id' => (int) $payment->id,
            'meta' => $audit->diff($before, $after),
        ]);

        unset($_SESSION['payments_edit_errors'], $_SESSION['payments_edit_old']);
        $_SESSION['payments_success'] = 'Платеж успешно обновлён.';

        return Response::redirect('/payments/show?id=' . (int) $payment->id);
    }

    public function destroy(Request $request, AuthService $auth, AuditLogger $audit): Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        if (!$this->paymentsStorageReady()) {
            return $this->redirectStorageError();
        }

        $payment = Payment::findForUser((int) $request->input('id', 0), (int) $user->id);

        if ($payment === null) {
            return $this->redirectPaymentNotFound($request, (int) $user->id);
        }

        $snapshot = $this->paymentSnapshot($payment);
        $agentId = (int) $payment->agent_id;

        $payment->delete();

        $audit->log(AuditAction::PAYMENT_DELETE, $request, $auth, [
            'entity_type' => 'payment',
            'entity_id' => (int) $snapshot['id'],
            'meta' => [
                'snapshot' => $snapshot,
            ],
        ]);

        $_SESSION['payments_success'] = 'Платеж успешно удалён.';

        return Response::redirect('/payments?agent_id=' . $agentId);
    }

    private function resolveAgentFromRequest(Request $request, int $userId): ?Agent
    {
        $agentId = (int) $request->input('agent_id', 0);

        return Agent::findForUser($agentId, $userId);
    }

    private function payloadFromRequest(Request $request, int $agentId): array
    {
        return [
            'agent_id' => $agentId,
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

    private function redirectStorageError(): Response
    {
        $_SESSION['agents_error'] = 'Раздел платежей недоступен. Выполните миграции базы данных.';

        return Response::redirect('/agents');
    }

    private function redirectPaymentNotFound(Request $request, int $userId): Response
    {
        $agentId = (int) $request->input('agent_id', 0);

        if ($agentId > 0 && Agent::findForUser($agentId, $userId) !== null) {
            $_SESSION['payments_error'] = 'Платеж не найден.';

            return Response::redirect('/payments?agent_id=' . $agentId);
        }

        $_SESSION['agents_error'] = 'Платеж не найден.';

        return Response::redirect('/agents');
    }

    private function paymentSnapshot(Payment $payment): array
    {
        return [
            'id' => (int) $payment->id,
            'agent_id' => (int) $payment->agent_id,
            'amount' => (string) $payment->amount,
            'payment_date' => (string) $payment->payment_date,
            'status' => (string) $payment->status,
            'note' => $payment->note !== null ? (string) $payment->note : null,
        ];
    }
}


