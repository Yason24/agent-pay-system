<?php

namespace App\Controllers;

use App\Services\AuthService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class RequestController extends Controller
{
    public function create(AuthService $auth): string|Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if ((string) $user->role !== 'agent') {
            return new Response('Forbidden', 403);
        }

        $success = $_SESSION['requests_success'] ?? null;
        $error = $_SESSION['requests_error'] ?? null;
        $old = $_SESSION['requests_old'] ?? [];

        unset($_SESSION['requests_success'], $_SESSION['requests_error'], $_SESSION['requests_old']);

        return $this->view('requests.create', [
            'title' => 'Создать заявку',
            'success' => $success,
            'error' => $error,
            'old' => $old,
        ]);
    }

    public function store(Request $request, AuthService $auth): Response
    {
        $user = $auth->user();

        if ($user === null) {
            return redirect('/login');
        }

        if ((string) $user->role !== 'agent') {
            return new Response('Forbidden', 403);
        }

        $subject = trim((string) $request->input('subject', ''));
        $comment = trim((string) $request->input('comment', ''));

        if ($subject === '') {
            $_SESSION['requests_error'] = 'Тема заявки обязательна.';
            $_SESSION['requests_old'] = [
                'subject' => $subject,
                'comment' => $comment,
            ];

            return redirect('/requests/create');
        }

        $_SESSION['requests_success'] = 'Заявка принята. Полноценный модуль заявок будет подключен отдельно.';

        return redirect('/cabinet');
    }
}

