<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Patient History</title>
    <link rel="stylesheet" href="css/patient_history.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="header">
        PATIENT HISTORY
        <button class="export-btn" onclick="exportHistory()">📄 All Consultations Feature</button>
        <div class="user-icon" onclick="showUserMenu()" title="User Menu">👤</div>
    </div>

    <div class="container">
        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-btn active" onclick="showTab('patients')">Patients</button>
                <button class="tab-btn" onclick="showTab('appointments')">Appointments</button>
                <button class="tab-btn" onclick="showTab('history')">History Page</button>
            </div>

            <div class="tab-content">
                <!-- Patients Tab -->
                <div id="patients-tab" class="tab-panel">
                    <div class="consultation-card">
                        <div class="card-header">
                            <div class="doctor-name">Consultations</div>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Doc Name:</span>
                            <span class="detail-value">Dr Rajesh Sharma</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Date and Time:</span>
                            <span class="detail-value">March 15, 2024 - 10:30 AM</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Diagnosis:</span>
                            <span class="detail-value">Chronic back pain, muscle tension</span>
                        </div>
                        
                        <div class="prescription-section">
                            <div class="prescription-title">Prescribed Products:</div>
                            <ul class="prescription-list">
                                <li>• Herbal pain relief oil - 2 bottles</li>
                                <li>• Anti-inflammatory tablets - 1 pack</li>
                                <li>• Muscle relaxant cream - 1 tube</li>
                            </ul>
                            <button class="view-prescription-btn" onclick="viewPrescription()">View Prescription</button>
                        </div>
                    </div>
                </div>

                <!-- Appointments Tab -->
                <div id="appointments-tab" class="tab-panel" style="display: none;">
                    <div class="treatment-card">
                        <div class="card-header">
                            <div class="doctor-name">Treatments</div>
                            <span class="status-badge status-completed">Completed</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Treatment:</span>
                            <span class="detail-value">Oil Massage Therapy</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Date and Time:</span>
                            <span class="detail-value">March 18, 2024 - 4:00 PM</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Session:</span>
                            <span class="detail-value">Session 4 of 5 - 45 minutes</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Follow-up Instructions:</span>
                            <span class="detail-value">Apply warm compress twice daily</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Notes:</span>
                            <span class="detail-value">Return for next session in 3 days</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value"><span class="status-badge status-completed">Completed</span></span>
                        </div>
                    </div>
                </div>

                <!-- History Page Tab -->
                <div id="history-tab" class="tab-panel" style="display: none;">
                    <h3 style="color: #8B4513; margin-bottom: 20px;">Treatment Timeline</h3>
                    
                    <div class="treatment-timeline">
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <div class="timeline-date">March 18, 2024</div>
                                <div class="timeline-description">
                                    <strong>Oil Massage Therapy - Session 4</strong><br>
                                    45-minute session completed. Patient reported significant improvement in back pain.
                                </div>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <div class="timeline-date">March 15, 2024</div>
                                <div class="timeline-description">
                                    <strong>Initial Consultation</strong><br>
                                    Dr. Rajesh Sharma diagnosed chronic back pain and muscle tension. Prescribed herbal treatments.
                                </div>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <div class="timeline-date">March 12, 2024</div>
                                <div class="timeline-description">
                                    <strong>Oil Massage Therapy - Session 3</strong><br>
                                    Continued treatment plan. Patient showing positive response to therapy.
                                </div>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <div class="timeline-date">March 08, 2024</div>
                                <div class="timeline-description">