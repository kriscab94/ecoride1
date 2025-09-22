<?php

namespace EcoRide\Core;

class Router {
    private $routes = [];
    
    public function get(string $path, $handler): void {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post(string $path, $handler): void {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put(string $path, $handler): void {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete(string $path, $handler): void {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    private function addRoute(string $method, string $path, $handler): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function handleRequest(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                $params = $this->extractParams($route['path'], $path);
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }
        
        $this->sendResponse(['error' => 'Route not found'], 404);
    }
    
    private function matchPath(string $routePath, string $requestPath): bool {
        $routePattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        return preg_match($routePattern, $requestPath);
    }
    
    private function extractParams(string $routePath, string $requestPath): array {
        $params = [];
        $routeParts = explode('/', $routePath);
        $requestParts = explode('/', $requestPath);
        
        foreach ($routeParts as $index => $part) {
            if (preg_match('/\{([^}]+)\}/', $part, $matches)) {
                $params[$matches[1]] = $requestParts[$index] ?? null;
            }
        }
        
        return $params;
    }
    
    private function executeHandler($handler, array $params): void {
        try {
            if (is_callable($handler)) {
                $result = $handler($params);
            } elseif (is_array($handler) && count($handler) === 2) {
                [$class, $method] = $handler;
                $controller = new $class();
                $result = $controller->$method($params);
            } else {
                throw new \Exception('Invalid handler');
            }
            
            if ($result !== null) {
                $this->sendResponse($result);
            }
        } catch (\Exception $e) {
            $this->sendResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    private function sendResponse($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}