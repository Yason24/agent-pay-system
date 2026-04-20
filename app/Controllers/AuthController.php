<?php

namespace App\Controllers;

use App\Services\AuthService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;
use Framework\Core\Request;
use App\Services\HashService;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): string
    {
        return $this->view('auth.login', [
            'title' => 'Login',
            'error' => $_SESSION['auth_error'] ?? null,
        ]);
    }

    public function login(Request $request, AuthService $auth): Response
    {
        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        if ($auth->attempt($email, $password)) {
            unset($_SESSION['auth_error']);

            return Response::redirect('/dashboard');
        }

        $_SESSION['auth_error'] = 'Invalid credentials.';

        return Response::redirect('/login');
    }

    public function logout(AuthService $auth): Response
    {
        $auth->logout();

        return Response::redirect('/login');
    }

    public function register(HashService $hash)
    {
        $password = $hash->make($_POST['password']);

        User::create([
            'email' => $_POST['email'],
            'password' => $password,
        ]);
    }
}