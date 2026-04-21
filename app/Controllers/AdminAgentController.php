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
		if ($response = $this->ensureAdmin($auth)) {
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
		]);
	}

	public function payments(Request $request, AuthService $auth): string|Response
	{
		if ($response = $this->ensureAdmin($auth)) {
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

	private function ensureAdmin(AuthService $auth): ?Response
	{
		if ($auth->guest()) {
			return Response::redirect('/login');
		}

		if (!$auth->hasRole('admin')) {
			$_SESSION['app_error'] = 'Доступ к разделу агентов есть только у администратора.';

			return Response::redirect('/dashboard');
		}

		return null;
	}
}

