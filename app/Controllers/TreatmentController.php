<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\TreatmentModel;

class TreatmentController extends Controller {
    private TreatmentModel $model;

    public function __construct() {
        $this->model = new TreatmentModel();
    }

    public function index(): void {
        $this->json(['data' => $this->model->getAll()]);
    }

    public function show(): void {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->json(['error' => 'id required'], 400); return; }
        $row = $this->model->getById($id);
        $this->json(['data' => $row]);
    }

    public function create(): void {
        $p = $_POST;
        $ok = $this->model->create(
            trim($p['treatment_name'] ?? ''),
            $p['description'] ?? null,
            trim($p['duration'] ?? ''),
            (float)($p['price'] ?? 0),
            trim($p['status'] ?? 'Active')
        );
        $this->json(['success' => $ok]);
    }

    public function update(): void {
        $p = $_POST;
        $id = (int)($p['treatment_id'] ?? 0);
        if (!$id) { $this->json(['error' => 'treatment_id required'], 400); return; }
        $ok = $this->model->update(
            $id,
            trim($p['treatment_name'] ?? ''),
            $p['description'] ?? null,
            trim($p['duration'] ?? ''),
            (float)($p['price'] ?? 0),
            trim($p['status'] ?? 'Active')
        );
        $this->json(['success' => $ok, 'id' => $id]);
    }

    public function delete(): void {
        $id = (int)($_POST['treatment_id'] ?? 0);
        if (!$id) { $this->json(['error' => 'treatment_id required'], 400); return; }
        $ok = $this->model->delete($id);
        $this->json(['success' => $ok]);
    }
}


