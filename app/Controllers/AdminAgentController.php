<?php

namespace App\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Services\AuthService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class AdminAgentController extends Controller
{
	public function index(AuthService $auth): string|Response
	{
		if ($response = $this->ensureStaff($auth)) {
			return $response;
		}

		$success = $_SESSION['agents_success'] ?? null;
		$error = $_SESSION['agents_error'] ?? null;

		unset($_SESSION['agents_success'], $_SESSION['agents_error']);

		return $this->view('admin.agents.index', [
			'title' => 'Агенты',
			'agents' => User::agents()->orderBy('id', 'DESC')->get(),
			'success' => $success,
			'error' => $error,
			'canManageUsers' => $auth->hasRole('admin'),
		]);
	}

	public function payments(Request $request, AuthService $auth): string|Response
	{
		if ($response = $this->ensureStaff($auth)) {
			return $response;
		}

		$agentUserId = (int) $request->input('agent_user_id', 0);
		$agent = User::findAgentById($agentUserId);

		if ($agent === null) {
			$_SESSION['agents_error'] = 'Агент не найден.';

			return Response::redirect('/admin/agents');
		}

		$success = $_SESSION['payments_success'] ?? null;
		$error = $_SESSION['payments_error'] ?? null;

		unset($_SESSION['payments_success'], $_SESSION['payments_error']);

		return $this->view('payments.index', [
			'title' => 'Платежи агента',
			'agent' => $agent,
			'payments' => Payment::forAgentUser((int) $agent->id),
			'success' => $success,
			'error' => $error,
			'isAdminMode' => true,
			'agentUserId' => (int) $agent->id,
		]);
	}

	public function show(Request $request, AuthService $auth): Response
	{
		if ($response = $this->ensureStaff($auth)) {
			return $response;
		}

		$agentUserId = (int) $request->input('agent_user_id', 0);
		$agent = User::findAgentById($agentUserId);

		if ($agent === null) {
			$_SESSION['agents_error'] = 'Агент не найден.';

			return Response::redirect('/admin/agents');
		}

		if (!$auth->hasRole('admin')) {
			$_SESSION['app_error'] = 'Просмотр профиля агента доступен только администратору.';

			return Response::redirect('/admin/agents');
		}

		return Response::redirect('/admin/users/edit?id=' . (int) $agent->id);
	}

	private function ensureStaff(AuthService $auth): ?Response
	{
		if ($auth->guest()) {
			return Response::redirect('/login');
		}

		if (!$auth->hasAnyRole(['admin', 'accountant'])) {
			$_SESSION['app_error'] = 'Доступ к разделу агентов есть только у администратора и бухгалтера.';

			return Response::redirect('/dashboard');
		}

		return null;
	}
}


