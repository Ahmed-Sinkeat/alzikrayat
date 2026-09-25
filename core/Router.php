<?php

declare(strict_types=1);

final class Router
{
    private array $routes = [];

    private string $basePath;

    private $notFoundHandler = null;

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function add(string $method, string $path, mixed $handler): void
    {
        if ($path === '' || $path[0] !== '/') {
            throw new InvalidArgumentException('Route paths must begin with a slash.');
        }

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $this->normalizePath($path),
            'handler' => $handler,
        ];
    }

    public function get(string $path, mixed $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, mixed $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function setNotFoundHandler(callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    public function dispatch(string $requestMethod, string $requestUri): mixed
    {
        $requestPath = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        $requestPath = $this->removeBasePath($requestPath);
        $requestPath = $this->normalizePath($requestPath);

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($requestMethod)) {
                continue;
            }

            [$regularExpression, $parameterNames] = $this->compilePattern($route['path']);

            if (preg_match($regularExpression, $requestPath, $matches) !== 1) {
                continue;
            }

            $parameters = [];
            foreach ($parameterNames as $parameterName) {
                $parameters[] = rawurldecode((string) $matches[$parameterName]);
            }

            return $this->callHandler($route['handler'], $parameters);
        }

        http_response_code(404);

        if (is_callable($this->notFoundHandler)) {
            return call_user_func($this->notFoundHandler);
        }

        return null;
    }

    private function compilePattern(string $routePath): array
    {
        $placeholderPattern = '/(\{[a-zA-Z][a-zA-Z0-9_]*\})/';
        $parts = preg_split($placeholderPattern, $routePath, -1, PREG_SPLIT_DELIM_CAPTURE);
        $regularExpression = '';
        $parameterNames = [];

        foreach ($parts ?: [] as $part) {
            if (preg_match('/^\{([a-zA-Z][a-zA-Z0-9_]*)\}$/', $part, $placeholderMatch) === 1) {
                $parameterName = $placeholderMatch[1];
                $parameterNames[] = $parameterName;
                $regularExpression .= '(?P<' . $parameterName . '>[^/]+)';
            } else {
                $regularExpression .= preg_quote($part, '#');
            }
        }

        return ['#^' . $regularExpression . '$#', $parameterNames];
    }

    private function callHandler(mixed $handler, array $parameters): mixed
    {
        if (is_array($handler) && count($handler) === 2 && is_string($handler[0])) {
            $controller = new $handler[0]();
            $handler = [$controller, $handler[1]];
        }

        if (!is_callable($handler)) {
            throw new RuntimeException('The matched route handler is not callable.');
        }

        return call_user_func_array($handler, $parameters);
    }

    private function removeBasePath(string $requestPath): string
    {
        if ($this->basePath !== '' && ($requestPath === $this->basePath || str_starts_with($requestPath, $this->basePath . '/'))) {
            $requestPath = substr($requestPath, strlen($this->basePath));
        }

        return $requestPath === '' ? '/' : $requestPath;
    }

    private function normalizePath(string $path): string
    {
        if ($path === '/') {
            return '/';
        }

        return rtrim($path, '/');
    }
}
