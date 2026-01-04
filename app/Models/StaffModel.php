<?php

class StaffModel {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Staff to Room Assignment Mapping
    // Maps staff members to their assigned treatment rooms
    private static $STAFF_ROOM_MAP = [
        // Room 1: Udwarthana
        'M.H.Gunarathne' => ['room' => 1, 'treatment_type' => 'Udwarthana', 'role' => 'Therapist'],
        'K.A.Perera' => ['room' => 1, 'treatment_type' => 'Udwarthana', 'role' => 'Assistant'],
        'S.M.Wickramasinghe' => ['room' => 1, 'treatment_type' => 'Udwarthana', 'role' => 'Therapist'],
        'N.D.Fernando' => ['room' => 1, 'treatment_type' => 'Udwarthana', 'role' => 'Assistant'],
        'R.P.Jayawardena' => ['room' => 1, 'treatment_type' => 'Udwarthana', 'role' => 'Therapist'],
        
        // Room 2: Nasya Karma
        'A.B.Silva' => ['room' => 2, 'treatment_type' => 'Nasya Karma', 'role' => 'Therapist'],
        'C.D.Mendis' => ['room' => 2, 'treatment_type' => 'Nasya Karma', 'role' => 'Assistant'],
        'E.F.Ratnayake' => ['room' => 2, 'treatment_type' => 'Nasya Karma', 'role' => 'Therapist'],
        'G.H.Amarasinghe' => ['room' => 2, 'treatment_type' => 'Nasya Karma', 'role' => 'Assistant'],
        'I.J.Karunaratne' => ['room' => 2, 'treatment_type' => 'Nasya Karma', 'role' => 'Therapist'],
        
        // Room 3: Shirodhara
        'L.M.Dissanayake' => ['room' => 3, 'treatment_type' => 'Shirodhara', 'role' => 'Therapist'],
        'N.O.Peiris' => ['room' => 3, 'treatment_type' => 'Shirodhara', 'role' => 'Assistant'],
        'P.Q.Rajapaksa' => ['room' => 3, 'treatment_type' => 'Shirodhara', 'role' => 'Therapist'],
        'R.S.Tennakoon' => ['room' => 3, 'treatment_type' => 'Shirodhara', 'role' => 'Assistant'],
        'T.U.Vithanage' => ['room' => 3, 'treatment_type' => 'Shirodhara', 'role' => 'Therapist'],
        
        // Room 4: Basti
        'W.X.Yapa' => ['room' => 4, 'treatment_type' => 'Basti', 'role' => 'Therapist'],
        'Z.A.Bandara' => ['room' => 4, 'treatment_type' => 'Basti', 'role' => 'Assistant'],
        'B.C.Dharmasena' => ['room' => 4, 'treatment_type' => 'Basti', 'role' => 'Therapist'],
        'D.E.Fonseka' => ['room' => 4, 'treatment_type' => 'Basti', 'role' => 'Assistant'],
        'F.G.Gunasekara' => ['room' => 4, 'treatment_type' => 'Basti', 'role' => 'Therapist'],
        
        // Room 5: Panchakarma Detox
        'H.I.Jayasinghe' => ['room' => 5, 'treatment_type' => 'Panchakarma Detox', 'role' => 'Therapist'],
        'K.L.Munasinghe' => ['room' => 5, 'treatment_type' => 'Panchakarma Detox', 'role' => 'Assistant'],
        'M.N.Opatha' => ['room' => 5, 'treatment_type' => 'Panchakarma Detox', 'role' => 'Therapist'],
        'O.P.Qadir' => ['room' => 5, 'treatment_type' => 'Panchakarma Detox', 'role' => 'Assistant'],
        'Q.R.Seneviratne' => ['room' => 5, 'treatment_type' => 'Panchakarma Detox', 'role' => 'Therapist'],
        
        // Room 6: Vashpa Sweda
        'S.T.Udawatta' => ['room' => 6, 'treatment_type' => 'Vashpa Sweda', 'role' => 'Therapist'],
        'U.V.Wijesekera' => ['room' => 6, 'treatment_type' => 'Vashpa Sweda', 'role' => 'Assistant'],
        'W.X.Yapa' => ['room' => 6, 'treatment_type' => 'Vashpa Sweda', 'role' => 'Therapist'],
        'Y.Z.Abeysekera' => ['room' => 6, 'treatment_type' => 'Vashpa Sweda', 'role' => 'Assistant'],
        'A.B.Cooray' => ['room' => 6, 'treatment_type' => 'Vashpa Sweda', 'role' => 'Therapist'],
        
        // Room 7: Abhyanga Massage
        'C.D.Edirisinghe' => ['room' => 7, 'treatment_type' => 'Abhyanga Massage', 'role' => 'Therapist'],
        'E.F.Gunawardena' => ['room' => 7, 'treatment_type' => 'Abhyanga Massage', 'role' => 'Assistant'],
        'G.H.Ihalagama' => ['room' => 7, 'treatment_type' => 'Abhyanga Massage', 'role' => 'Therapist'],
        'I.J.Kulatunga' => ['room' => 7, 'treatment_type' => 'Abhyanga Massage', 'role' => 'Assistant'],
        'K.L.Mahinda' => ['room' => 7, 'treatment_type' => 'Abhyanga Massage', 'role' => 'Therapist'],
        
        // Room 8: Elakizhi
        'M.N.Nanayakkara' => ['room' => 8, 'treatment_type' => 'Elakizhi', 'role' => 'Therapist'],
        'O.P.Premaratne' => ['room' => 8, 'treatment_type' => 'Elakizhi', 'role' => 'Assistant'],
        'Q.R.Ranasinghe' => ['room' => 8, 'treatment_type' => 'Elakizhi', 'role' => 'Therapist'],
        'S.T.Samarasinghe' => ['room' => 8, 'treatment_type' => 'Elakizhi', 'role' => 'Assistant'],
        'U.V.Thilakarathne' => ['room' => 8, 'treatment_type' => 'Elakizhi', 'role' => 'Therapist'],
    ];
    
    // Get staff room assignment
    public function getStaffRoomAssignment($staff_name) {
        return self::$STAFF_ROOM_MAP[$staff_name] ?? null;
    }
    
    // Get all staff assigned to a specific room
    public function getStaffByRoom($room_number) {
        $staff = [];
        foreach (self::$STAFF_ROOM_MAP as $name => $info) {
            if ($info['room'] === (int)$room_number) {
                $staff[] = [
                    'name' => $name,
                    'role' => $info['role'],
                    'treatment_type' => $info['treatment_type']
                ];
            }
        }
        return $staff;
    }
    
    // Get all staff names
    public function getAllStaffNames() {
        return array_keys(self::$STAFF_ROOM_MAP);
    }
}

?>
