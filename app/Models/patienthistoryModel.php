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
                    cf.notes,
                    cf.created_at,
                    c.appointment_date,
                    c.appointment_time
                FROM consultationforms cf
                LEFT JOIN consultations c ON cf.appointment_id = c.id
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
                'age' => $row['age'] ?? 'N/A',
                'gender' => $row['gender'] ?? 'N/A'
            ];
        }
        
        $stmt->close();
        return $history;
    }
}
?>