<?php

namespace App\Controllers;

use App\Models\Payment;
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

        $agentUserId = (int) $user->id;

        return $this->view('history.index', [
            'title' => 'Баланс',
            'summary' => Payment::balanceSummaryForAgentUser($agentUserId),
            'history' => Payment::unifiedHistoryForAgentUser($agentUserId),
            'agent_full_name' => $this->agentFullName($user),
            'isAgentMode' => true,
            'is_agent' => true,
            'canTopUp' => false,
            'agentUserId' => $agentUserId,
            'agent_user_id' => $agentUserId,
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

        return $this->view('history.index', [
            'title' => 'Баланс',
            'summary' => Payment::balanceSummaryForAgentUser($agentUserId),
            'history' => Payment::unifiedHistoryForAgentUser($agentUserId),
            'agent_full_name' => $this->agentFullName($agent),
            'isAgentMode' => false,
            'is_agent' => false,
            'canTopUp' => $auth->hasAnyRole(['admin', 'accountant']),
            'agentUserId' => $agentUserId,
            'agent_user_id' => $agentUserId,
        ]);
    }

    private function agentFullName(User $user): string
    {
        $fullName = $user->fullName();

        return $fullName !== '' ? $fullName : '—';
    }
}
