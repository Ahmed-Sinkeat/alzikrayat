<?php

declare(strict_types=1);

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $viewFile = ROOT_PATH . '/views/' . $view . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException("View not found: {$view}");
        }

        extract($data, EXTR_SKIP);
        require ROOT_PATH . '/views/layout/header.php';
        require $viewFile;
        require ROOT_PATH . '/views/layout/footer.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function requireAuthentication(): void
    {
        if (!isLoggedIn()) {
            setFlash('warning', 'Please log in to continue.');
            $this->redirect('/login');
        }
    }

    protected function requireValidCsrfToken(): void
    {
        $submittedToken = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';

        if (!isValidCsrfToken($submittedToken)) {
            http_response_code(403);
            $this->view('errors/403', ['pageTitle' => 'Request rejected']);
            exit;
        }
    }

    protected function notFound(): void
    {
        http_response_code(404);
        $this->view('errors/404', ['pageTitle' => 'Page not found']);
    }
}
