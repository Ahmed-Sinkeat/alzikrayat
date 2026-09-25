<?php

declare(strict_types=1);

require dirname(__DIR__) . '/core/Router.php';

final class RouterTestController
{
    public function show(string $id): string
    {
        return 'photo:' . $id;
    }
}

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', received ' . var_export($actual, true));
    }
}

$router = new Router('/project1/public');
$router->get('/photo/{id}', [RouterTestController::class, 'show']);
$router->post('/photo/store', 'routerStoredResponse');
$router->setNotFoundHandler('routerNotFoundResponse');

function routerStoredResponse(): string
{
    return 'stored';
}

function routerNotFoundResponse(): string
{
    return 'not-found';
}

assertSameValue('photo:25', $router->dispatch('GET', '/project1/public/photo/25?view=large'), 'Parameterized GET route failed.');
assertSameValue('stored', $router->dispatch('POST', '/project1/public/photo/store'), 'POST route failed.');
assertSameValue('not-found', $router->dispatch('GET', '/project1/public/unknown'), '404 handler failed.');
assertSameValue(404, http_response_code(), 'Unknown route did not set HTTP 404.');

echo "Router tests passed.\n";
