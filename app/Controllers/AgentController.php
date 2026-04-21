<?php

namespace App\Controllers;

use App\Models\Agent;
use App\Models\Payment;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\AuthService;
use App\Support\AuditAction;
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

        $success = $_SESSION['agents_success'] ?? null;
        $error = $_SESSION['agents_error'] ?? null;

        unset($_SESSION['agents_success'], $_SESSION['agents_error']);

        if ($auth->hasAnyRole(['admin', 'accountant'])) {
            $agentUsers = User::where('role', '=', 'agent')
                ->orderBy('id', 'DESC')
                ->get();

            $staffAgents = [];

            foreach ($agentUsers as $agentUser) {
                $legacyAgent = Agent::where('user_id', '=', (int) $agentUser->id)
                    ->orderBy('id', 'DESC')
                    ->first();

                $staffAgents[] = [
                    'user_id' => (int) $agentUser->id,
                    'name' => (string) $agentUser->name,
                    'email' => (string) $agentUser->email,
                    'role' => (string) $agentUser->role,
                    'legacy_agent_id' => $legacyAgent !== null ? (int) $legacyAgent->id : null,
                ];
            }

            return $this->view('agents.index', [
                'title' => 'Агенты',
                'staffAgents' => $staffAgents,
                'staffAgentsListMode' => true,
                'success' => $success,
                'error' => $error,
            ]);
        }

        $agents = Agent::forUser((int) $user->id);

        return $this->view('agents.index', [
            'title' => 'Мои агенты',
            'agents' => $agents,
            'staffAgentsListMode' => false,
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function create(AuthService $auth): string|Response
    {
        if ($auth->user() === null) {
            return Response::redirect('/login');
        }

        $errors = $_SESSION['agents_create_errors'] ?? [];
        $old = $_SESSION['agents_create_old'] ?? [];

        unset($_SESSION['agents_create_errors'], $_SESSION['agents_create_old']);

        return $this->view('agents.create', [
            'title' => 'Создать агента',
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

        $name = trim((string) $request->input('name', ''));
        $errors = $this->validate($name);

        if ($errors !== []) {
            $_SESSION['agents_create_errors'] = $errors;
            $_SESSION['agents_create_old'] = ['name' => $name];

            return Response::redirect('/agents/create');
        }

        $agent = Agent::create([
            'name' => $name,
            'user_id' => (int) $user->id,
        ]);

        $audit->log(AuditAction::AGENT_CREATE, $request, $auth, [
            'entity_type' => 'agent',
            'entity_id' => (int) $agent->id,
            'meta' => [
                'snapshot' => $this->agentSnapshot($agent),
            ],
        ]);

        unset($_SESSION['agents_create_errors'], $_SESSION['agents_create_old']);
        $_SESSION['agents_success'] = 'Агент успешно создан.';

        return Response::redirect('/agents');
    }

    public function show(Request $request, AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $id = (int) $request->input('id', 0);
        $agent = Agent::findForUser($id, (int) $user->id);

        if ($agent === null) {
            $_SESSION['agents_error'] = 'Агент не найден.';

            return Response::redirect('/agents');
        }

        $paymentSummary = Payment::summaryForAgentAndUser((int) $agent->id, (int) $user->id);
        $latestPayments = Payment::latestForAgentAndUser((int) $agent->id, (int) $user->id, 5);

        return $this->view('agents.show', [
            'title' => 'Кабинет агента',
            'agent' => $agent,
            'paymentSummary' => $paymentSummary,
            'latestPayments' => $latestPayments,
        ]);
    }

    public function edit(Request $request, AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $id = (int) $request->input('id', 0);
        $agent = Agent::findForUser($id, (int) $user->id);

        if ($agent === null) {
            $_SESSION['agents_error'] = 'Агент не найден.';

            return Response::redirect('/agents');
        }

        $errors = $_SESSION['agents_edit_errors'] ?? [];
        $old = $_SESSION['agents_edit_old'] ?? [];

        unset($_SESSION['agents_edit_errors'], $_SESSION['agents_edit_old']);

        return $this->view('agents.edit', [
            'title' => 'Изменить агента',
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

        $id = (int) $request->input('id', 0);
        $agent = Agent::findForUser($id, (int) $user->id);

        if ($agent === null) {
            $_SESSION['agents_error'] = 'Агент не найден.';

            return Response::redirect('/agents');
        }

        $name = trim((string) $request->input('name', ''));
        $errors = $this->validate($name);

        if ($errors !== []) {
            $_SESSION['agents_edit_errors'] = $errors;
            $_SESSION['agents_edit_old'] = ['name' => $name];

            return Response::redirect('/agents/edit?id=' . $agent->id);
        }

        $before = $this->agentSnapshot($agent);

        $agent->name = $name;
        $agent->save();

        $after = $this->agentSnapshot($agent);

        $audit->log(AuditAction::AGENT_UPDATE, $request, $auth, [
            'entity_type' => 'agent',
            'entity_id' => (int) $agent->id,
            'meta' => $audit->diff($before, $after),
        ]);

        unset($_SESSION['agents_edit_errors'], $_SESSION['agents_edit_old']);
        $_SESSION['agents_success'] = 'Агент успешно обновлён.';

        return Response::redirect('/agents/show?id=' . $agent->id);
    }

    public function destroy(Request $request, AuthService $auth, AuditLogger $audit): Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $id = (int) $request->input('id', 0);
        $agent = Agent::findForUser($id, (int) $user->id);

        if ($agent === null) {
            $_SESSION['agents_error'] = 'Агент не найден.';

            return Response::redirect('/agents');
        }

        $snapshot = $this->agentSnapshot($agent);

        $agent->delete();

        $audit->log(AuditAction::AGENT_DELETE, $request, $auth, [
            'entity_type' => 'agent',
            'entity_id' => (int) $snapshot['id'],
            'meta' => [
                'snapshot' => $snapshot,
            ],
        ]);

        $_SESSION['agents_success'] = 'Агент успешно удалён.';

        return Response::redirect('/agents');
    }

    private function validate(string $name): array
    {
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Имя обязательно.';

            return $errors;
        }

        if (strlen($name) < 2) {
            $errors['name'] = 'Имя должно быть не короче 2 символов.';
        }

        if (strlen($name) > 255) {
            $errors['name'] = 'Имя не должно превышать 255 символов.';
        }

        return $errors;
    }

    private function agentSnapshot(Agent $agent): array
    {
        return [
            'id' => (int) $agent->id,
            'name' => (string) $agent->name,
            'user_id' => (int) $agent->user_id,
        ];
    }
}


