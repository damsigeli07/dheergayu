<?php
namespace Core;

class Core {
	private array $routes = ['GET' => [], 'POST' => []];

	public function get(string $path, callable|array $handler): void {
		$this->routes['GET'][$this->normalize($path)] = $handler;
	}

	public function post(string $path, callable|array $handler): void {
		$this->routes['POST'][$this->normalize($path)] = $handler;
	}

    public function run(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($base && $base !== '/' && str_starts_with($requestPath, $base)) {
            $requestPath = substr($requestPath, strlen($base)) ?: '/';
        }
        $path = $this->normalize($requestPath);
		$handler = $this->routes[$method][$path] ?? null;

		if (!$handler) {
			http_response_code(404);
			echo '404 Not Found';
			return;
		}

		if (is_array($handler)) {
			[$class, $action] = $handler;
			$controller = new $class();
			$controller->$action();
			return;
		}

		$handler();
	}

	private function normalize(string $path): string {
		return ($path !== '/' && str_ends_with($path, '/')) ? rtrim($path, '/') : $path;
	}
}


