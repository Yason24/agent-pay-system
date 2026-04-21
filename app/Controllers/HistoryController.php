<?php

namespace App\Controllers;

use App\Models\Payment;
use App\Services\AuthService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;

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

        return $this->view('history.index', [
            'title' => 'Моя история',
            'agent' => $user,
            'payments' => Payment::latestForAgentUser((int) $user->id, 20),
        ]);
    }
}

