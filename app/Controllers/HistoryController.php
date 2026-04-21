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
    // -------------------------------------------------------------------------
    // Agent: GET /my/balance
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

        $summary  = Payment::summaryForAgentUser((int) $user->id);
        $payments = Payment::forAgentUser((int) $user->id);

        return $this->view('history.index', [
            'title'       => 'Мой баланс',
            'agent'       => $user,
            'summary'     => $summary,
            'payments'    => $payments,
            'isAgentMode' => true,
            'agentUserId' => (int) $user->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Staff: GET /history?agent_user_id=
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

        $summary  = Payment::summaryForAgentUser($agentUserId);
        $payments = Payment::forAgentUser($agentUserId);

        return $this->view('history.index', [
            'title'       => 'Финансовая история агента',
            'agent'       => $agent,
            'summary'     => $summary,
            'payments'    => $payments,
            'isAgentMode' => false,
            'agentUserId' => $agentUserId,
        ]);
    }
}
