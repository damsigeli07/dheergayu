<?php
// app/Models/DoctorModel.php

class DoctorModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Get appointments count for current month for a specific doctor
     */
    public function getAppointmentsThisMonth($doctor_id) {
        $query = "SELECT COUNT(*) as count 
                  FROM consultations 
                  WHERE doctor_id = ? 
                  AND MONTH(appointment_date) = MONTH(CURRENT_DATE())
                  AND YEAR(appointment_date) = YEAR(CURRENT_DATE())
                  AND status != 'Cancelled'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] ?? 0;
    }

    /**
     * Get unique patients count for current month for a specific doctor
     */
    public function getTotalPatientsThisMonth($doctor_id) {
        $query = "SELECT COUNT(DISTINCT patient_id) as count 
                  FROM consultations 
                  WHERE doctor_id = ? 
                  AND MONTH(appointment_date) = MONTH(CURRENT_DATE())
                  AND YEAR(appointment_date) = YEAR(CURRENT_DATE())
                  AND status != 'Cancelled'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] ?? 0;
    }

    /**
     * Get today's income for a specific doctor
     * Assuming consultation fee is Rs. 2,000 per appointment
     */
    public function getTodayIncome($doctor_id) {
        $consultationFee = 2000; // Standard consultation fee
        
        $query = "SELECT COUNT(*) as count 
                  FROM consultations 
                  WHERE doctor_id = ? 
                  AND DATE(appointment_date) = CURDATE()
                  AND status = 'Completed'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return ($row['count'] ?? 0) * $consultationFee;
    }

    /**
     * Get monthly appointments data for charts (last 30 days)
     */
    public function getMonthlyAppointments($doctor_id, $days = 30) {
        $query = "SELECT 
                    DATE(appointment_date) as date,
                    COUNT(*) as count
                  FROM consultations 
                  WHERE doctor_id = ? 
                  AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                  AND status != 'Cancelled'
                  GROUP BY DATE(appointment_date)
                  ORDER BY date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $doctor_id, $days);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'date' => $row['date'],
                'count' => (int)$row['count']
            ];
        }
        $stmt->close();
        
        return $data;
    }

    /**
     * Get monthly income data for charts (last 30 days)
     */
    public function getMonthlyIncome($doctor_id, $days = 30) {
        $consultationFee = 2000; // Standard consultation fee
        
        $query = "SELECT 
                    DATE(appointment_date) as date,
                    COUNT(*) as count
                  FROM consultations 
                  WHERE doctor_id = ? 
                  AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                  AND status = 'Completed'
                  GROUP BY DATE(appointment_date)
                  ORDER BY date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $doctor_id, $days);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'date' => $row['date'],
                'income' => (int)$row['count'] * $consultationFee
            ];
        }
        $stmt->close();
        
        return $data;
    }

    /**
     * Get all report statistics for a doctor
     */
    public function getDoctorReportStatistics($doctor_id) {
        return [
            'appointmentsThisMonth' => $this->getAppointmentsThisMonth($doctor_id),
            'totalPatientsThisMonth' => $this->getTotalPatientsThisMonth($doctor_id),
            'todayIncome' => $this->getTodayIncome($doctor_id),
            'monthlyAppointments' => $this->getMonthlyAppointments($doctor_id, 30),
            'monthlyIncome' => $this->getMonthlyIncome($doctor_id, 30)
        ];
    }
}
?>
