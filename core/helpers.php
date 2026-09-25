<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    $normalizedPath = '/' . ltrim($path, '/');

    if ($normalizedPath === '//') {
        $normalizedPath = '/';
    }

    return BASE_URL . $normalizedPath;
}

function asset(string $path): string
{
    return url('/' . ltrim($path, '/'));
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function isValidCsrfToken(string $submittedToken): bool
{
    return isset($_SESSION['csrf_token'])
        && $submittedToken !== ''
        && hash_equals((string) $_SESSION['csrf_token'], $submittedToken);
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']['id']) && is_int($_SESSION['user']['id']);
}

function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['user']['id'],
        'first_name' => (string) $_SESSION['user']['first_name'],
    ];
}

function currentUserId(): ?int
{
    $user = currentUser();

    return $user === null ? null : $user['id'];
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function takeFlash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return [
        'type' => (string) ($flash['type'] ?? 'info'),
        'message' => (string) ($flash['message'] ?? ''),
    ];
}

function formatDate(string $dateTime): string
{
    $timestamp = strtotime($dateTime);

    return $timestamp === false ? $dateTime : date('M j, Y \a\t g:i A', $timestamp);
}
