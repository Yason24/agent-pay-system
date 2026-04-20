<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Services\HashService;
use Framework\Core\Controller;
use Framework\Core\Http\Response;
use Framework\Core\Request;

class AuthController extends Controller
{
    public function showLogin(): string
    {
        return $this->view('auth.login', [
            'title' => 'Login',
            'error' => $_SESSION['auth_error'] ?? null,
            'success' => $_SESSION['auth_success'] ?? null,
        ]);
    }

    public function showRegister(): string
    {
        return $this->view('auth.register', [
            'title' => 'Register',
            'error' => $_SESSION['register_error'] ?? null,
        ]);
    }

    public function login(Request $request, AuthService $auth): Response
    {
        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        if ($auth->attempt($email, $password)) {
            unset($_SESSION['auth_error']);
            unset($_SESSION['auth_success']);

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

    public function register(Request $request, HashService $hash): Response
    {
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $passwordConfirmation = (string) $request->input('password_confirmation', '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['register_error'] = 'Please enter a valid email.';

            return Response::redirect('/register');
        }

        if ($password === '' || strlen($password) < 6) {
            $_SESSION['register_error'] = 'Password must be at least 6 characters.';

            return Response::redirect('/register');
        }

        if ($password !== $passwordConfirmation) {
            $_SESSION['register_error'] = 'Password confirmation does not match.';

            return Response::redirect('/register');
        }

        if (User::findByEmail($email)) {
            $_SESSION['register_error'] = 'User with this email already exists.';

            return Response::redirect('/register');
        }

        $user = User::create([
            'name' => $name !== '' ? $name : 'User',
            'email' => $email,
            'password' => $hash->make($password),
            'role' => 'user',
        ]);

        unset($_SESSION['register_error']);
        $_SESSION['auth_success'] = 'Registration completed for ' . $user->email . '. You can sign in now.';

        return Response::redirect('/login');
    }
}

