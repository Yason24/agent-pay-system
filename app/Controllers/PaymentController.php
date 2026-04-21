<?php

namespace App\Controllers;

use App\Models\Agent;
use App\Models\Payment;
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
            return $this->redirectStorageError();
        }

        $agent = $this->resolveAgentFromRequest($request, (int) $user->id);

        if ($agent === null) {
            $_SESSION['agents_error'] = 'Agent not found.';

            return Response::redirect('/agents');
        }

        $success = $_SESSION['payments_success'] ?? null;
        $error = $_SESSION['payments_error'] ?? null;

        unset($_SESSION['payments_success'], $_SESSION['payments_error']);

        return $this->view('payments.index', [
            'title' => 'Payments',
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
            $_SESSION['agents_error'] = 'Agent not found.';

            return Response::redirect('/agents');
        }

        $errors = $_SESSION['payments_create_errors'] ?? [];
        $old = $_SESSION['payments_create_old'] ?? [];

        unset($_SESSION['payments_create_errors'], $_SESSION['payments_create_old']);

        return $this->view('payments.create', [
            'title' => 'Create Payment',
            'agent' => $agent,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    public function store(Request $request, AuthService $auth): Response
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
            $_SESSION['agents_error'] = 'Agent not found.';

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
            Payment::create([
                'agent_id' => $agentId,
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

            return Response::redirect('/payments/create?agent_id=' . $agentId);
        }

        unset($_SESSION['payments_create_errors'], $_SESSION['payments_create_old']);
        $_SESSION['payments_success'] = 'Payment created successfully.';

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
            'title' => 'Payment',
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
            'title' => 'Edit Payment',
            'payment' => $payment,
            'agent' => $agent,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    public function update(Request $request, AuthService $auth): Response
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

            return Response::redirect('/payments/edit?id=' . (int) $payment->id);
        }

        unset($_SESSION['payments_edit_errors'], $_SESSION['payments_edit_old']);
        $_SESSION['payments_success'] = 'Payment updated successfully.';

        return Response::redirect('/payments/show?id=' . (int) $payment->id);
    }

    public function destroy(Request $request, AuthService $auth): Response
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

        $agentId = (int) $payment->agent_id;
        $payment->delete();

        $_SESSION['payments_success'] = 'Payment deleted successfully.';

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
            $errors['amount'] = 'Amount is required.';
        } elseif (!preg_match('/^\d{1,10}([.,]\d{1,2})?$/', $amountRaw)) {
            $errors['amount'] = 'Amount format: up to 10 digits and optional 1-2 decimals.';
        } elseif ((float) $amount <= 0) {
            $errors['amount'] = 'Amount must be greater than zero.';
        }

        if ($payload['payment_date'] === '') {
            $errors['payment_date'] = 'Payment date is required.';
        } else {
            $date = \DateTime::createFromFormat('Y-m-d', $payload['payment_date']);

            if (!$date || $date->format('Y-m-d') !== $payload['payment_date']) {
                $errors['payment_date'] = 'Payment date must be in YYYY-MM-DD format.';
            }
        }

        $allowedStatuses = ['pending', 'paid', 'failed'];

        if ($payload['status'] === '') {
            $errors['status'] = 'Status is required.';
        } elseif (!in_array($payload['status'], $allowedStatuses, true)) {
            $errors['status'] = 'Status must be one of: pending, paid, failed.';
        }

        if (strlen($payload['note']) > 1000) {
            $errors['note'] = 'Note must not exceed 1000 characters.';
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
}


