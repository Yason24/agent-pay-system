<?php

namespace App\Controllers;

use App\Models\Agent;
use App\Services\AuthService;
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

        $agents = Agent::forUser((int) $user->id);

        return $this->view('agents.index', [
            'title' => 'My Agents',
            'agents' => $agents,
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
            'title' => 'Create Agent',
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

        $name = trim((string) $request->input('name', ''));
        $errors = $this->validate($name);

        if ($errors !== []) {
            $_SESSION['agents_create_errors'] = $errors;
            $_SESSION['agents_create_old'] = ['name' => $name];

            return Response::redirect('/agents/create');
        }

        Agent::create([
            'name' => $name,
            'user_id' => (int) $user->id,
        ]);

        unset($_SESSION['agents_create_errors'], $_SESSION['agents_create_old']);
        $_SESSION['agents_success'] = 'Agent created successfully.';

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
            $_SESSION['agents_error'] = 'Agent not found.';

            return Response::redirect('/agents');
        }

        return $this->view('agents.show', [
            'title' => 'Agent Card',
            'agent' => $agent,
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
            $_SESSION['agents_error'] = 'Agent not found.';

            return Response::redirect('/agents');
        }

        $errors = $_SESSION['agents_edit_errors'] ?? [];
        $old = $_SESSION['agents_edit_old'] ?? [];

        unset($_SESSION['agents_edit_errors'], $_SESSION['agents_edit_old']);

        return $this->view('agents.edit', [
            'title' => 'Edit Agent',
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

        $id = (int) $request->input('id', 0);
        $agent = Agent::findForUser($id, (int) $user->id);

        if ($agent === null) {
            $_SESSION['agents_error'] = 'Agent not found.';

            return Response::redirect('/agents');
        }

        $name = trim((string) $request->input('name', ''));
        $errors = $this->validate($name);

        if ($errors !== []) {
            $_SESSION['agents_edit_errors'] = $errors;
            $_SESSION['agents_edit_old'] = ['name' => $name];

            return Response::redirect('/agents/edit?id=' . $agent->id);
        }

        $agent->name = $name;
        $agent->save();

        unset($_SESSION['agents_edit_errors'], $_SESSION['agents_edit_old']);
        $_SESSION['agents_success'] = 'Agent updated successfully.';

        return Response::redirect('/agents/show?id=' . $agent->id);
    }

    public function destroy(Request $request, AuthService $auth): Response
    {
        $user = $auth->user();

        if ($user === null) {
            return Response::redirect('/login');
        }

        $id = (int) $request->input('id', 0);
        $agent = Agent::findForUser($id, (int) $user->id);

        if ($agent === null) {
            $_SESSION['agents_error'] = 'Agent not found.';

            return Response::redirect('/agents');
        }

        $agent->delete();
        $_SESSION['agents_success'] = 'Agent deleted successfully.';

        return Response::redirect('/agents');
    }

    private function validate(string $name): array
    {
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'Name is required.';

            return $errors;
        }

        if (strlen($name) < 2) {
            $errors['name'] = 'Name must be at least 2 characters.';
        }

        if (strlen($name) > 255) {
            $errors['name'] = 'Name must not exceed 255 characters.';
        }

        return $errors;
    }
}


