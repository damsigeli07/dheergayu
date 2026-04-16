<?php
// app/Controllers/ConsultationFormController.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDbConnection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'dheergayu_db';

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        throw new Exception("Database connection failed");
    }
    return $conn;
}

// Helper: safe fetch POST
function post($key, $default = null) {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDbConnection();
        $db->begin_transaction();

        // Debug
        file_put_contents(__DIR__ . '/../../debug_consultation.txt', date('c') . " POST:\n" . print_r($_POST, true) . "\n\n", FILE_APPEND);

        $appointment_id = intval(post('appointment_id', 0));
        if ($appointment_id <= 0) throw new Exception('Invalid appointment id');

        // fetch consultation to get patient id
        $pq = $db->prepare("SELECT patient_id FROM consultations WHERE id = ? LIMIT 1");
        $pq->bind_param('i', $appointment_id);
        $pq->execute();
        $prow = $pq->get_result()->fetch_assoc();
        $pq->close();
        $patient_id = intval($prow['patient_id'] ?? 0);

        // Normalize patient_no (try to get from patients table)
        $patient_no = '';
        if ($patient_id > 0) {
            $pp = $db->prepare("SELECT patient_number FROM patients WHERE id = ? LIMIT 1");
            $pp->bind_param('i', $patient_id);
            $pp->execute();
            $pr = $pp->get_result()->fetch_assoc();
            $pp->close();
            if (!empty($pr['patient_number'])) {
                $n = preg_replace('/^P/i', '', trim($pr['patient_number']));
                $patient_no = is_numeric($n) ? ('P' . str_pad((int)$n, 4, '0', STR_PAD_LEFT)) : trim($pr['patient_number']);
            }
        }
        if (empty($patient_no) && !empty(post('patient_no'))) {
            $raw = trim(post('patient_no'));
            $n = preg_replace('/^P/i', '', $raw);
            $patient_no = is_numeric($n) ? ('P' . str_pad((int)$n, 4, '0', STR_PAD_LEFT)) : $raw;
        }
        if (empty($patient_no)) $patient_no = 'P0001';

        // Read fields from POST
        $first_name = trim((string)post('first_name', ''));
        $last_name = trim((string)post('last_name', ''));
        $age = intval(post('age', 0));
        $gender = trim((string)post('gender', ''));
        $diagnosis = trim((string)post('diagnosis', ''));
        $personal_products = post('personal_products', '[]');
        $notes = trim((string)post('notes', ''));
        $recommended_treatment = trim((string)post('recommended_treatment', ''));
        if (strtolower($recommended_treatment) === 'no treatment needed') $recommended_treatment = '';

        // basic required validation
        $missing = [];
        if ($first_name === '') $missing[] = 'first_name';
        if ($last_name === '') $missing[] = 'last_name';
        if ($age <= 0) $missing[] = 'age';
        if ($gender === '') $missing[] = 'gender';
        if (!empty($missing)) {
            throw new Exception('Missing fields: ' . implode(', ', $missing));
        }

        // Upsert consultationforms
        $check = $db->prepare("SELECT id FROM consultationforms WHERE appointment_id = ? LIMIT 1");
        $check->bind_param('i', $appointment_id);
        $check->execute();
        $ex = $check->get_result()->fetch_assoc();
        $check->close();

        if ($ex) {
            $upd = $db->prepare("UPDATE consultationforms SET first_name = ?, last_name = ?, age = ?, gender = ?, diagnosis = ?, personal_products = ?, recommended_treatment = ?, notes = ?, patient_id = ?, patient_no = ?, updated_at = NOW() WHERE appointment_id = ?");
            $upd->bind_param('ssisssssisi', $first_name, $last_name, $age, $gender, $diagnosis, $personal_products, $recommended_treatment, $notes, $patient_id, $patient_no, $appointment_id);
            if (!$upd->execute()) throw new Exception('Failed updating consultation form: ' . $upd->error);
            $upd->close();
        } else {
            $ins = $db->prepare("INSERT INTO consultationforms (appointment_id, patient_id, patient_no, first_name, last_name, age, gender, diagnosis, personal_products, recommended_treatment, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $ins->bind_param('iississssss', $appointment_id, $patient_id, $patient_no, $first_name, $last_name, $age, $gender, $diagnosis, $personal_products, $recommended_treatment, $notes);
            if (!$ins->execute()) throw new Exception('Failed inserting consultation form: ' . $ins->error);
            $ins->close();
        }

        // Handle treatment plans syncing
        $treatment_plan_choice = post('treatment_plan_choice', 'no_need');
        $treatment_schedule_data = post('treatment_schedule_data', '');
        $single_treatment_data = post('single_treatment_data', '');

        // Helper: delete other plans for this appointment (keep plan_id)
        $delete_other_plans = function($keep_plan_id) use ($db, $appointment_id) {
            $c = $db->prepare("DELETE FROM treatment_plans WHERE appointment_id = ? AND plan_id != ?");
            if ($c) { $c->bind_param('ii', $appointment_id, $keep_plan_id); $c->execute(); $c->close(); }
        };

        if ($treatment_plan_choice === 'single_session' && !empty($single_treatment_data)) {
            $tData = json_decode($single_treatment_data, true);
            if ($tData && isset($tData['booking_id'])) {
                $booking_id = intval($tData['booking_id']);

                // link booking
                $u = $db->prepare("UPDATE consultationforms SET treatment_booking_id = ? WHERE appointment_id = ?");
                $u->bind_param('ii', $booking_id, $appointment_id);
                $u->execute();
                $u->close();

                // load booking
                $b = $db->prepare("SELECT tb.patient_id, tb.treatment_id, tb.booking_date, ts.slot_time, tl.treatment_name, tl.price FROM treatment_bookings tb LEFT JOIN treatment_slots ts ON tb.slot_id = ts.slot_id LEFT JOIN treatment_list tl ON tb.treatment_id = tl.treatment_id WHERE tb.booking_id = ? LIMIT 1");
                $b->bind_param('i', $booking_id);
                $b->execute();
                $booking = $b->get_result()->fetch_assoc();
                $b->close();

                if ($booking) {
                    $plan_patient_id = $patient_id;
                    $plan_treatment_id = intval($booking['treatment_id']);
                    $plan_start_date = $booking['booking_date'] ?? date('Y-m-d');
                    $plan_session_time = $booking['slot_time'] ?? '09:00';
                    $plan_diagnosis_single = $diagnosis ?: 'Single session';
                    $plan_total_cost = floatval($booking['price'] ?? 0);

                    // find or create plan
                    $plan_id_single = 0;
                    $chk = $db->prepare("SELECT plan_id FROM treatment_plans WHERE appointment_id = ? LIMIT 1");
                    $chk->bind_param('i', $appointment_id);
                    $chk->execute();
                    $cres = $chk->get_result()->fetch_assoc();
                    $chk->close();
                    if ($cres && !empty($cres['plan_id'])) $plan_id_single = (int)$cres['plan_id'];

                    if ($plan_id_single > 0) {
                        $up = $db->prepare("UPDATE treatment_plans SET patient_id = ?, treatment_id = ?, diagnosis = ?, start_date = ?, total_cost = ?, updated_at = NOW() WHERE plan_id = ?");
                        $up->bind_param('iissdi', $plan_patient_id, $plan_treatment_id, $plan_diagnosis_single, $plan_start_date, $plan_total_cost, $plan_id_single);
                        $up->execute();
                        $up->close();
                    } else {
                        $ins = $db->prepare("INSERT INTO treatment_plans (appointment_id, patient_id, treatment_id, diagnosis, start_date, total_cost, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())");
                        $ins->bind_param('iiissd', $appointment_id, $plan_patient_id, $plan_treatment_id, $plan_diagnosis_single, $plan_start_date, $plan_total_cost);
                        $ins->execute();
                        $plan_id_single = (int)$ins->insert_id;
                        $ins->close();
                    }

                    if ($plan_id_single > 0) {
                        // replace sessions — space additional sessions 1 week apart
                        $del = $db->prepare("DELETE FROM treatment_sessions WHERE plan_id = ?");
                        $del->bind_param('i', $plan_id_single);
                        $del->execute();
                        $del->close();

                        // Only create Session 1 with the booked date/time — staff assigns sessions 2+
                        $sess1 = $db->prepare("INSERT INTO treatment_sessions (plan_id, session_number, session_date, session_time, status, created_at) VALUES (?, 1, ?, ?, 'Pending', NOW())");
                        $sess1->bind_param('iss', $plan_id_single, $plan_start_date, $plan_session_time);
                        $sess1->execute();
                        $sess1->close();

                        // cleanup duplicates
                        $delete_other_plans($plan_id_single);
                    }
                }
            }
        }

        // finalize
        $u2 = $db->prepare("UPDATE consultations SET status = 'Completed' WHERE id = ?");
        $u2->bind_param('i', $appointment_id);
        $u2->execute();
        $u2->close();

        $db->commit();
        $db->close();

        echo json_encode(['status' => 'success', 'message' => 'Consultation saved successfully']);

    } catch (Exception $e) {
        if (isset($db)) { $db->rollback(); $db->close(); }
        error_log('Consultation save error: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {

    if ($_GET['action'] === 'get_consultation_form') {
        try {
            $appointment_id = intval($_GET['appointment_id'] ?? 0);
            if ($appointment_id <= 0) throw new Exception('Invalid appointment_id');

            $db = getDbConnection();
            $stmt = $db->prepare("SELECT * FROM consultationforms WHERE appointment_id = ? LIMIT 1");
            $stmt->bind_param('i', $appointment_id);
            $stmt->execute();
            $form = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $response = ['form' => $form ?? null];
            $merged = [];

            if ($form) {
                if (!empty($form['recommended_treatment'])) {
                    $merged['recommended_treatment'] = $form['recommended_treatment'];
                }
                // linked booking
                if (empty($merged['recommended_treatment']) && !empty($form['treatment_booking_id'])) {
                    $booking_id = intval($form['treatment_booking_id']);
                    $bst = $db->prepare("SELECT tb.booking_id, tb.booking_date, ts.slot_time, tl.treatment_name, tl.price FROM treatment_bookings tb LEFT JOIN treatment_slots ts ON tb.slot_id = ts.slot_id LEFT JOIN treatment_list tl ON tb.treatment_id = tl.treatment_id WHERE tb.booking_id = ? LIMIT 1");
                    $bst->bind_param('i', $booking_id);
                    $bst->execute();
                    $brow = $bst->get_result()->fetch_assoc();
                    $bst->close();
                    if ($brow) {
                        $merged['recommended_treatment'] = "Treatment: " . ($brow['treatment_name'] ?? '') . " | Date: " . ($brow['booking_date'] ?? '') . " | Time: " . ($brow['slot_time'] ?? '');
                        $merged['treatment_booking'] = $brow;
                    }
                }

                // linked plan
                if (empty($merged['recommended_treatment'])) {
                    $pst = $db->prepare("SELECT tp.plan_id, tp.treatment_id, tp.start_date, tp.diagnosis, tl.treatment_name, tl.price FROM treatment_plans tp LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id WHERE tp.appointment_id = ? LIMIT 1");
                    $pst->bind_param('i', $appointment_id);
                    $pst->execute();
                    $prow = $pst->get_result()->fetch_assoc();
                    $pst->close();
                    if ($prow) {
                        $merged['recommended_treatment'] = "Treatment: " . ($prow['treatment_name'] ?? '') . " | Start: " . ($prow['start_date'] ?? '');
                        $merged['treatment_plan'] = $prow;
                    }
                }

                // fallback most recent for patient
                if (empty($merged['recommended_treatment'])) {
                    $cst = $db->prepare("SELECT patient_id FROM consultations WHERE id = ? LIMIT 1");
                    $cst->bind_param('i', $appointment_id);
                    $cst->execute();
                    $crow = $cst->get_result()->fetch_assoc();
                    $cst->close();
                    $pid = intval($crow['patient_id'] ?? 0);
                    if ($pid > 0) {
                        $pst2 = $db->prepare("SELECT tp.plan_id, tp.treatment_id, tp.start_date, tl.treatment_name, tl.price FROM treatment_plans tp LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id WHERE tp.patient_id = ? ORDER BY tp.created_at DESC LIMIT 1");
                        $pst2->bind_param('i', $pid);
                        $pst2->execute();
                        $prow2 = $pst2->get_result()->fetch_assoc();
                        $pst2->close();
                        if ($prow2) {
                            $merged['recommended_treatment'] = "Treatment: " . ($prow2['treatment_name'] ?? '') . " | Start: " . ($prow2['start_date'] ?? '');
                            $merged['treatment_plan'] = $prow2;
                        }
                    }
                }
            }

            $response['merged'] = $merged;
            $db->close();

            echo json_encode($response);

        } catch (Exception $e) {
            error_log('Get consultation error: ' . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
