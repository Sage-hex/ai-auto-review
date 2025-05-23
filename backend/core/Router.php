<?php
/**
 * Router Class
 * 
 * This class handles routing for the API requests.
 * It's a core component that can be used to extend the routing functionality.
 */

class Router {
    private $routes = [];
    
    /**
     * Register a route
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $path Route path
     * @param callable $handler Handler function
     */
    public function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    /**
     * Match a request to a route
     * 
     * @param string $method HTTP method
     * @param string $path Request path
     * @return array|bool Route handler if matched, false if not
     */
    public function match($method, $path) {
        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }
            
            // Simple path matching (can be extended for more complex routing)
            if ($route['path'] === $path) {
                return $route['handler'];
            }
            
            // Path parameter matching (e.g., /users/{id})
            $routePath = preg_replace('/{[^}]+}/', '([^/]+)', $route['path']);
            $routePath = str_replace('/', '\/', $routePath);
            
            if (preg_match('/^' . $routePath . '$/', $path, $matches)) {
                array_shift($matches); // Remove the full match
                return [
                    'handler' => $route['handler'],
                    'params' => $matches
                ];
            }
        }
        
        return false;
    }
    
    /**
     * Handle a request
     * 
     * @param string $method HTTP method
     * @param string $path Request path
     * @return mixed Result of the handler
     */
    public function handleRequest($method, $path) {
        $route = $this->match($method, $path);
        
        if (!$route) {
            header('HTTP/1.1 404 Not Found');
            return [
                'status' => 'error',
                'message' => 'Route not found'
            ];
        }
        
        if (is_array($route) && isset($route['handler']) && isset($route['params'])) {
            return call_user_func_array($route['handler'], $route['params']);
        }
        
        return call_user_func($route);
    }
}
