<?php
// public/index.php – front controller

require_once __DIR__ . '/../core/bootloader.php';
require_once __DIR__ . '/../core/Core.php';

use Core\Core;
use App\Controllers\BatchController;
use App\Controllers\TreatmentController;

$router = new Core();

// Patient-facing pages
$router->get('/', fn() => require __DIR__ . '/../app/Views/Patient/home.php');
$router->get('/login', fn() => require __DIR__ . '/../app/Views/Patient/login.php');
$router->get('/doctors', fn() => require __DIR__ . '/../app/Views/Patient/doctors.php');
$router->get('/products', fn() => require __DIR__ . '/../app/Views/Patient/products.php');
$router->get('/treatment/before', fn() => require __DIR__ . '/../app/Views/Patient/before_login_treatment.php');
$router->get('/treatment/after', fn() => require __DIR__ . '/../app/Views/Patient/after_login_treatment.php');

// Admin view
$router->get('/admin/users', fn() => require __DIR__ . '/../app/Views/Admin/adminusers.php');

// API endpoints
$router->get('/api/admin/users', fn() => require __DIR__ . '/../app/Controllers/admin_users.php');

// Pharmacist inventory/batch API
$router->get('/api/batches', [BatchController::class, 'list']);
$router->get('/api/batches/by-product', [BatchController::class, 'batches']);
$router->post('/api/batches/create', [BatchController::class, 'create']);
$router->post('/api/batches/update', [BatchController::class, 'update']);
$router->post('/api/batches/delete', [BatchController::class, 'delete']);

// Treatments API
$router->get('/api/treatments', [TreatmentController::class, 'index']);
$router->get('/api/treatments/show', [TreatmentController::class, 'show']);
$router->post('/api/treatments/create', [TreatmentController::class, 'create']);
$router->post('/api/treatments/update', [TreatmentController::class, 'update']);
$router->post('/api/treatments/delete', [TreatmentController::class, 'delete']);

$router->run();


