<?php

namespace App\Controllers;

use App\Models\Payment;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\AuthService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class HistoryController extends Controller
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

        $rows = $this->buildHistoryRows((int) $user->id);

        return $this->view('history.index', [
            'title' => 'История',
            'agent' => $user,
            'summary' => $this->buildSummary($rows),
            'history' => $rows,
            'isAgentMode' => true,
            'agentUserId' => (int) $user->id,
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

        $agentUserId = (int) $request->input('agent_user_id', 0);

        if ($agentUserId <= 0) {
            return new Response('agent_user_id is required', 400);
        }

        $agent = User::find($agentUserId);

        if ($agent === null || (string) $agent->role !== 'agent') {
            $_SESSION['agents_error'] = 'Агент не найден.';

            return redirect('/agents');
        }

        $rows = $this->buildHistoryRows($agentUserId);

        return $this->view('history.index', [
            'title' => 'История агента',
            'agent' => $agent,
            'summary' => $this->buildSummary($rows),
            'history' => $rows,
            'isAgentMode' => false,
            'agentUserId' => $agentUserId,
        ]);
    }

    private function buildHistoryRows(int $agentUserId): array
    {
        $paymentRows = Payment::historyRowsForAgentUser($agentUserId);
        $linkedRequestIds = [];

        foreach ($paymentRows as $row) {
            $relatedRequestId = (int) ($row['related_request_id'] ?? 0);

            if ($relatedRequestId > 0) {
                $linkedRequestIds[] = $relatedRequestId;
            }
        }

        $requestRows = PaymentRequest::paidHistoryRowsForAgentUser($agentUserId, $linkedRequestIds);
        $rows = array_merge($paymentRows, $requestRows);

        usort($rows, static function (array $a, array $b): int {
            $byDate = strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? ''));

            if ($byDate !== 0) {
                return $byDate;
            }

            return ((int) ($b['source_id'] ?? 0)) <=> ((int) ($a['source_id'] ?? 0));
        });

        return $rows;
    }

    private function buildSummary(array $rows): array
    {
        $balanceTotal = 0.0;

        foreach ($rows as $row) {
            $type = (string) ($row['type'] ?? '');
            $amount = (float) ($row['amount'] ?? 0);

            if (in_array($type, ['Начисление', 'Корректировка'], true)) {
                $balanceTotal += $amount;
                continue;
            }

            if (in_array($type, ['Выплата', 'Заявка исполнена', 'Выплата по заявке'], true)) {
                $balanceTotal -= $amount;
            }
        }

        return [
            'operations_count' => count($rows),
            'balance_total' => $balanceTotal,
        ];
    }
}
