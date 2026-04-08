<?php
class patienthistoryModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Search patients by patient_no OR (name + dob)
     * Returns patient information from the patients table
     */
    public function searchPatients($patient_no = "", $name = "", $dob = "") {
        // Case 1: Search by patient number
        if (!empty($patient_no)) {
            $sql = "SELECT 
                        p.id,
                        p.patient_number as patient_no,
                        p.first_name,
                        p.last_name,
                        p.email,
                        p.dob,
                        p.nic,
                        TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) as age,
                        COALESCE(pi.gender, 'N/A') as gender,
                        COALESCE(p.email, 'N/A') as contact_info
                    FROM patients p
                    LEFT JOIN patient_info pi ON pi.patient_id = p.id
                    WHERE p.patient_number = ?
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('s', $patient_no);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_all(MYSQLI_ASSOC);
            }
            $stmt->close();
            return [];
        }
        
        // Case 2: Search by name + DOB
        if (!empty($name) && !empty($dob)) {
            // Split name to handle both "FirstName LastName" and single name
            $name_parts = explode(' ', trim($name), 2);
            $first_name = $name_parts[0];
            $last_name = $name_parts[1] ?? '';
            
            if ($last_name) {
                // Search with both first and last name
                $sql = "SELECT 
                            p.id,
                            p.patient_number as patient_no,
                            p.first_name,
                            p.last_name,
                            p.email,
                            p.dob,
                            p.nic,
                            TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) as age,
                            COALESCE(pi.gender, 'N/A') as gender,
                            COALESCE(p.email, 'N/A') as contact_info
                        FROM patients p
                        LEFT JOIN patient_info pi ON pi.patient_id = p.id
                        WHERE (p.first_name LIKE ? AND p.last_name LIKE ?)
                        AND p.dob = ?";
                
                $stmt = $this->conn->prepare($sql);
                $first_name_like = "%$first_name%";
                $last_name_like = "%$last_name%";
                $stmt->bind_param('sss', $first_name_like, $last_name_like, $dob);
            } else {
                // Search with single name (could be first or last)
                $sql = "SELECT 
                            p.id,
                            p.patient_number as patient_no,
                            p.first_name,
                            p.last_name,
                            p.email,
                            p.dob,
                            p.nic,
                            TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) as age,
                            COALESCE(pi.gender, 'N/A') as gender,
                            COALESCE(p.email, 'N/A') as contact_info
                        FROM patients p
                        LEFT JOIN patient_info pi ON pi.patient_id = p.id
                        WHERE (p.first_name LIKE ? OR p.last_name LIKE ?)
                        AND p.dob = ?";
                
                $stmt = $this->conn->prepare($sql);
                $name_like = "%$first_name%";
                $stmt->bind_param('sss', $name_like, $name_like, $dob);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        return [];
    }

    /**
     * Get consultation history for a patient by patient_no
     * First gets patient_id from patients table, then fetches all consultations
     */
    public function getConsultationFormHistory($patient_no) {
        // Get patient_id from patients table using patient_number
        $sql = "SELECT id FROM patients WHERE patient_number = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $patient_no);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return [];
        }
        
        $patient = $result->fetch_assoc();
        $patient_id = $patient['id'];
        $stmt->close();
        
        // Get all consultation records for this patient
        // Use patient_id if available, otherwise fall back to patient_no
        $sql = "SELECT 
                    cf.id,
                    cf.first_name,
                    cf.last_name,
                    cf.age,
                    cf.gender,
                    cf.diagnosis,
                    cf.personal_products,
                    cf.recommended_treatment,
                    cf.question_1,
                    cf.question_2,
                    cf.question_3,
                    cf.question_4,
                    cf.notes,
                    cf.last_visit_date,
                    cf.total_visits,
                    cf.contact_info,
                    cf.check_patient_vitals,
                    cf.review_previous_medications,
                    cf.update_patient_history,
                    cf.follow_up_appointment,
                    cf.send_to_pharmacy,
                    cf.created_at,
                    c.appointment_date,
                    c.appointment_time,
                    tp.plan_id AS treatment_plan_id,
                    COALESCE(tp.treatment_name, tl.treatment_name) AS treatment_plan_name
                FROM consultationforms cf
                LEFT JOIN consultations c ON cf.appointment_id = c.id
                LEFT JOIN treatment_plans tp ON (cf.treatment_booking_id IS NOT NULL AND cf.treatment_booking_id = tp.plan_id) OR (cf.appointment_id IS NOT NULL AND cf.appointment_id = tp.appointment_id)
                LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id
                WHERE cf.patient_id = ? OR cf.patient_no = ?
                ORDER BY cf.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('is', $patient_id, $patient_no);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            // Parse personal_products JSON
            $products = 'None';
            if (!empty($row['personal_products'])) {
                $products_array = json_decode($row['personal_products'], true);
                if (is_array($products_array) && !empty($products_array)) {
                    $product_names = array_map(function($p) {
                        return ($p['product'] ?? 'Unknown') . ' (Qty: ' . ($p['qty'] ?? 1) . ')';
                    }, $products_array);
                    $products = implode(', ', $product_names);
                }
            }
            
            $history[] = [
                'diagnosis' => $row['diagnosis'] ?? 'N/A',
                'personal_products' => $products,
                'recommended_treatment' => $row['recommended_treatment'] ?? 'None',
                'notes' => $row['notes'] ?? 'No additional notes',
                'created_at' => $row['created_at'] ? date('F d, Y h:i A', strtotime($row['created_at'])) : 'N/A',
                'first_name' => $row['first_name'] ?? '',
                'last_name' => $row['last_name'] ?? '',
                'age' => $row['age'] ?? '',
                'gender' => $row['gender'] ?? '',
                'question_1' => $row['question_1'] ?? null,
                'question_2' => $row['question_2'] ?? null,
                'question_3' => $row['question_3'] ?? null,
                'question_4' => $row['question_4'] ?? null,
                'last_visit_date' => $row['last_visit_date'] ? date('F d, Y', strtotime($row['last_visit_date'])) : null,
                                'recommended_treatment' => $row['recommended_treatment'] ?? 'None',
                                'treatment_plan_name' => $row['treatment_plan_name'] ?? null,
                                'treatment_plan_id' => $row['treatment_plan_id'] ?? null,
                'contact_info' => $row['contact_info'] ?? null,
                'check_patient_vitals' => (isset($row['check_patient_vitals']) ? (bool)$row['check_patient_vitals'] : false),
                'review_previous_medications' => (isset($row['review_previous_medications']) ? (bool)$row['review_previous_medications'] : false),
                'update_patient_history' => (isset($row['update_patient_history']) ? (bool)$row['update_patient_history'] : false),
                'follow_up_appointment' => (isset($row['follow_up_appointment']) ? (bool)$row['follow_up_appointment'] : false),
                'send_to_pharmacy' => (isset($row['send_to_pharmacy']) ? (bool)$row['send_to_pharmacy'] : false),
                'age' => $row['age'] ?? 'N/A',
                'gender' => $row['gender'] ?? 'N/A'
            ];
        }
        
        $stmt->close();
        return $history;
    }

    /**
     * Get treatment plans for a given patient_id
     */
    public function getTreatmentPlansByPatientId($patient_id) {
        // Fetch plans and include treatment name from treatment_list when available
        $sql = "SELECT
                    tp.plan_id,
                    COALESCE(tp.treatment_name, tl.treatment_name) AS treatment_name,
                    tp.diagnosis,
                    tp.total_sessions,
                    tp.sessions_per_week,
                    tp.start_date,
                    tp.total_cost,
                    tp.status,
                    tp.created_at
                FROM treatment_plans tp
                LEFT JOIN treatment_list tl ON tp.treatment_id = tl.treatment_id
                WHERE tp.patient_id = ?
                ORDER BY tp.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $plans = [];
        while ($row = $result->fetch_assoc()) {
            $plan = [
                'plan_id' => $row['plan_id'],
                'treatment_name' => $row['treatment_name'] ?: 'N/A',
                'diagnosis' => $row['diagnosis'] ?: 'N/A',
                'total_sessions' => $row['total_sessions'] ?? 'N/A',
                'sessions_per_week' => $row['sessions_per_week'] ?? 'N/A',
                'start_date' => $row['start_date'] ? date('F d, Y', strtotime($row['start_date'])) : 'N/A',
                'total_cost' => isset($row['total_cost']) ? number_format($row['total_cost'], 2) : 'N/A',
                'status' => $row['status'] ?: 'N/A',
                'created_at' => $row['created_at'] ? date('F d, Y h:i A', strtotime($row['created_at'])) : 'N/A',
                'sessions' => []
            ];

            // Fetch sessions for this plan
            $s_sql = "SELECT session_number, session_date, session_time, assigned_staff_id, status
                      FROM treatment_sessions
                      WHERE plan_id = ?
                      ORDER BY session_number";
            $s_stmt = $this->conn->prepare($s_sql);
            $s_stmt->bind_param('i', $row['plan_id']);
            $s_stmt->execute();
            $s_res = $s_stmt->get_result();
            while ($s = $s_res->fetch_assoc()) {
                $session = [
                    'session_number' => $s['session_number'],
                    'session_date' => $s['session_date'] ? date('F d, Y', strtotime($s['session_date'])) : 'N/A',
                    'session_time' => $s['session_time'] ? date('g:i A', strtotime($s['session_time'])) : 'N/A',
                    'status' => $s['status'] ?: 'N/A',
                    'assigned_staff' => 'Unassigned',
                    'note' => null,
                    'note_created_at' => null,
                    'note_staff' => null
                ];

                // Assigned staff name
                if (!empty($s['assigned_staff_id'])) {
                    $u_stmt = $this->conn->prepare("SELECT first_name, last_name FROM users WHERE id = ? LIMIT 1");
                    $u_stmt->bind_param('i', $s['assigned_staff_id']);
                    $u_stmt->execute();
                    $u_res = $u_stmt->get_result();
                    if ($u_row = $u_res->fetch_assoc()) {
                        $session['assigned_staff'] = trim(($u_row['first_name'] ?? '') . ' ' . ($u_row['last_name'] ?? '')) ?: 'Staff #'.$s['assigned_staff_id'];
                    }
                    $u_stmt->close();
                }

                // Latest session note (if any)
                $sn_stmt = $this->conn->prepare("SELECT session_note, created_at, staff_id FROM staff_treatment_session_notes WHERE plan_id = ? AND session_number = ? ORDER BY id DESC LIMIT 1");
                $sn_stmt->bind_param('ii', $row['plan_id'], $s['session_number']);
                $sn_stmt->execute();
                $sn_res = $sn_stmt->get_result();
                if ($sn_row = $sn_res->fetch_assoc()) {
                    $session['note'] = $sn_row['session_note'];
                    $session['note_created_at'] = $sn_row['created_at'] ? date('F d, Y g:i A', strtotime($sn_row['created_at'])) : null;
                    if (!empty($sn_row['staff_id'])) {
                        $ns_stmt = $this->conn->prepare("SELECT first_name, last_name FROM users WHERE id = ? LIMIT 1");
                        $ns_stmt->bind_param('i', $sn_row['staff_id']);
                        $ns_stmt->execute();
                        $ns_res = $ns_stmt->get_result();
                        if ($ns = $ns_res->fetch_assoc()) {
                            $session['note_staff'] = trim(($ns['first_name'] ?? '') . ' ' . ($ns['last_name'] ?? '')) ?: ('Staff #'.$sn_row['staff_id']);
                        }
                        $ns_stmt->close();
                    }
                }
                $sn_stmt->close();

                $plan['sessions'][] = $session;
            }
            $s_stmt->close();

            $plans[] = $plan;
        }

        $stmt->close();
        return $plans;
    }
}
?>