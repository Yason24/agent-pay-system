<?php

namespace App\Controllers;

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

		$searchQuery = (string) ($_GET['q'] ?? '');
		$q = $this->normalizeSearch($searchQuery);
		$agents = User::agents()->orderBy('id', 'DESC')->get();

		if ($q !== '') {
			$agents = $agents->filter(function (User $agent) use ($q): bool {
				$fullName = User::composeFullName([
					'last_name' => (string) $agent->last_name,
					'first_name' => (string) $agent->first_name,
					'middle_name' => (string) $agent->middle_name,
					'name' => (string) $agent->name,
				]);

				return str_contains($this->normalizeSearch($fullName), $q);
			});
		}

		return $this->view('admin.agents.index', [
			'title' => 'Агенты',
			'agents' => $agents,
			'success' => $success,
			'error' => $error,
			'search_query' => $searchQuery,
			'canManageUsers' => $auth->hasRole('admin'),
			'canTopUp' => $auth->hasAnyRole(['admin', 'accountant']),
			'canViewProfile' => $auth->hasRole('admin'),
		]);
	}

	private function normalizeSearch(?string $value): string
	{
		$value = trim((string) $value);
		$value = preg_replace('/\s+/u', ' ', $value) ?? '';

		if (function_exists('mb_strtolower')) {
			return mb_strtolower($value);
		}

		return strtolower($value);
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

			return redirect('/agents');
		}

		if (!$auth->hasRole('admin')) {
			$_SESSION['app_error'] = 'Просмотр профиля агента доступен только администратору.';

			return redirect('/agents');
		}

		return redirect('/admin/users/edit?id=' . (int) $agent->id);
	}

	private function ensureStaff(AuthService $auth): ?Response
	{
		if ($auth->guest()) {
			return redirect('/login');
		}

		if (!$auth->hasAnyRole(['admin', 'accountant', 'dispatcher'])) {
			$_SESSION['app_error'] = 'Доступ к разделу агентов есть только у администратора, бухгалтера и диспетчера.';

			return redirect('/dashboard');
		}

		return null;
	}
}


