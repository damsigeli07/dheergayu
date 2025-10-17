<?php
// core/Controller.php
namespace Core;

class Controller {
	protected function view(string $view, array $data = []): void {
		extract($data, EXTR_SKIP);
		require __DIR__ . '/../app/Views/' . $view . '.php';
	}

	protected function json(mixed $payload, int $status = 200): void {
		http_response_code($status);
		header('Content-Type: application/json');
		echo json_encode($payload);
	}
}


