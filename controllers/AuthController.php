<?php

declare(strict_types=1);

final class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if (isLoggedIn()) {
            $this->redirect('/');
        }

        $lastLogin = $this->validLastLoginCookie($_COOKIE['last_login'] ?? '');
        $this->view('auth/login', [
            'pageTitle' => 'Login',
            'errors' => [],
            'input' => ['email' => ''],
            'lastLogin' => $lastLogin,
        ]);
    }

    public function login(): void
    {
        $this->requireValidCsrfToken();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $errors = [];

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 100) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        $user = $errors === [] ? $this->userModel->findByEmail($email) : null;

        if ($errors === [] && ($user === null || !password_verify($password, (string) $user['password']))) {
            $errors['credentials'] = 'The email address or password is incorrect.';
        }

        if ($errors !== []) {
            $this->view('auth/login', [
                'pageTitle' => 'Login',
                'errors' => $errors,
                'input' => ['email' => $email],
                'lastLogin' => $this->validLastLoginCookie($_COOKIE['last_login'] ?? ''),
            ]);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'first_name' => (string) $user['first_name'],
        ];

        $this->storeLastLoginCookie(date('Y-m-d H:i:s'));
        setFlash('success', 'Welcome back, ' . (string) $user['first_name'] . '.');
        $this->redirect('/');
    }

    public function showRegister(): void
    {
        if (isLoggedIn()) {
            $this->redirect('/');
        }

        $this->view('auth/register', [
            'pageTitle' => 'Create account',
            'errors' => [],
            'input' => $this->emptyRegistrationInput(),
        ]);
    }

    public function register(): void
    {
        $this->requireValidCsrfToken();
        $input = $this->registrationInputFromRequest();
        $errors = $this->userModel->validate($input);

        if ($errors === []) {
            try {
                $this->userModel->create($input);
            } catch (PDOException $exception) {
                if ((string) $exception->getCode() === '23000') {
                    $errors['email'] = 'An account with this email address already exists.';
                } else {
                    throw $exception;
                }
            }
        }

        if ($errors !== []) {
            $input['password'] = '';
            $input['password_confirmation'] = '';
            $this->view('auth/register', [
                'pageTitle' => 'Create account',
                'errors' => $errors,
                'input' => $input,
            ]);
            return;
        }

        setFlash('success', 'Your account was created. You can now log in.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        $this->requireAuthentication();
        $this->requireValidCsrfToken();
        unset($_SESSION['user']);
        session_regenerate_id(true);
        setFlash('success', 'You have been logged out.');
        $this->redirect('/login');
    }

    private function registrationInputFromRequest(): array
    {
        return [
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
            'location' => trim((string) ($_POST['location'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'occupation' => trim((string) ($_POST['occupation'] ?? '')),
        ];
    }

    private function emptyRegistrationInput(): array
    {
        return [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'location' => '',
            'description' => '',
            'occupation' => '',
        ];
    }

    private function storeLastLoginCookie(string $timestamp): void
    {
        setcookie('last_login', $timestamp, [
            'expires' => time() + (7 * 24 * 60 * 60),
            'path' => BASE_URL === '' ? '/' : BASE_URL,
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function validLastLoginCookie(string $cookieValue): ?string
    {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $cookieValue);

        if ($date === false || $date->format('Y-m-d H:i:s') !== $cookieValue) {
            return null;
        }

        return $cookieValue;
    }
}
