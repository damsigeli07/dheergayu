<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - My Appointments</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/patient_appointments.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <img src="/dheergayu/public/assets/images/Patient/dheergayu.png" alt="Dheergayu Logo" class="logo">
                <h1 class="header-title">Dheergayu</h1>
            </nav>
        </div>
        <div>
            <a href="home.php" class="back-btn">← Back to Home</a>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">My Appointments</h1>
            <p class="page-subtitle">Manage your consultations and treatment appointments</p>
        </div>

        <div class="appointments-container">
            <div class="appointments-tabs">
                <button class="tab-btn active" onclick="showTab('upcoming')">
                    Upcoming
                    <span class="tab-badge">3</span>
                </button>
                <button class="tab-btn" onclick="showTab('completed')">Completed</button>
                <button class="tab-btn" onclick="showTab('cancelled')">Cancelled</button>
                <button class="tab-btn" onclick="showTab('all')">All Appointments</button>
            </div>

            <div class="tab-content">
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-group">
                        <label class="filter-label">Type</label>
                        <select class="filter-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="consultation">Consultation</option>
                            <option value="treatment">Treatment</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Doctor</label>
                        <select class="filter-select" id="doctorFilter">
                            <option value="">All Doctors</option>
                            <option value="dr-perera">Dr. L.M. Perera</option>
                            <option value="dr-jayawardena">Dr. K. Jayawardena</option>
                            <option value="dr-gunarathne">Dr. A.T. Gunarathne</option>
                        </select>
                    </div>
                    <input type="text" class="search-box" placeholder="Search appointments..." id="searchBox">
                </div>

                <!-- Upcoming Appointments Tab -->
                <div id="upcoming-tab" class="tab-panel">
                    <div class="appointments-list">
                        <div class="appointment-card consultation">
                            <div class="appointment-header">
                                <div class="appointment-type consultation">Consultation</div>
                                <div class="appointment-status status-confirmed">Confirmed</div>
                            </div>
                            <div class="appointment-details">
                                <div class="detail-item">
                                    <span class="detail-label">Doctor</span>
                                    <span class="detail-value">Dr. L.M. Perera</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Date & Time</span>
                                    <span class="detail-value">March 25, 2024 - 10:00 AM</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Type</span>
                                    <span class="detail-value">Follow-up Consultation</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Fee</span>
                                    <span class="detail-value">Rs 1,500</span>
                                </div>
                            </div>
                            <div class="appointment-actions">

                                <button class="action-btn btn-secondary" onclick="reschedule('APP001')">Reschedule</button>
                                <button class="action-btn btn-danger" onclick="cancelAppointment('APP001')">Cancel</button>
                            </div>
                        </div>

                        <div class="appointment-card treatment">
                            <div class="appointment-header">
                                <div class="appointment-type treatment">Treatment</div>
                                <div class="appointment-status status-confirmed">Confirmed</div>
                            </div>
                            <div class="appointment-details">
                                <div class="detail-item">
                                    <span class="detail-label">Treatment</span>
                                    <span class="detail-value">Panchakarma Therapy</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Date & Time</span>
                                    <span class="detail-value">March 28, 2024 - 2:00 PM</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Duration</span>
                                    <span class="detail-value">90 minutes</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Session</span>
                                    <span class="detail-value">Session 1 of 5</span>
                                </div>
                            </div>
                            <div class="appointment-actions">

                                <button class="action-btn btn-secondary" onclick="reschedule('APP002')">Reschedule</button>
                                <button class="action-btn btn-danger" onclick="cancelAppointment('APP002')">Cancel</button>
                            </div>
                        </div>

                        <div class="appointment-card consultation">
                            <div class="appointment-header">
                                <div class="appointment-type consultation">Consultation</div>
                                <div class="appointment-status status-pending">Pending Confirmation</div>
                            </div>
                            <div class="appointment-details">
                                <div class="detail-item">
                                    <span class="detail-label">Doctor</span>
                                    <span class="detail-value">Dr. K. Jayawardena</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Date & Time</span>
                                    <span class="detail-value">April 2, 2024 - 3:30 PM</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Type</span>
                                    <span class="detail-value">Initial Consultation</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Fee</span>
                                    <span class="detail-value">Rs 2,000</span>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <button class="action-btn btn-danger" onclick="cancelAppointment('APP003')">Cancel Request</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Appointments Tab -->
                <div id="completed-tab" class="tab-panel" style="display: none;">
                    <div class="appointments-list">
                        <div class="appointment-card consultation">
                            <div class="appointment-header">
                                <div class="appointment-type consultation">Consultation</div>
                                <div class="appointment-status status-completed">Completed</div>
                            </div>
                            <div class="appointment-details">
                                <div class="detail-item">
                                    <span class="detail-label">Doctor</span>
                                    <span class="detail-value">Dr. L.M. Perera</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Date & Time</span>
                                    <span class="detail-value">March 15, 2024 - 10:30 AM</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Diagnosis</span>
                                    <span class="detail-value">Chronic back pain, muscle tension</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Fee Paid</span>
                                    <span class="detail-value">Rs 1,500</span>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <button class="action-btn btn-primary" onclick="viewDetails('APP004')">View Details</button>
                                <button class="action-btn btn-success" onclick="viewPrescription('APP004')">View Prescription</button>
                                <button class="action-btn btn-secondary" onclick="bookFollowUp('APP004')">Book Follow-up</button>
                            </div>
                        </div>

                        <div class="appointment-card treatment">
                            <div class="appointment-header">
                                <div class="appointment-type treatment">Treatment</div>
                                <div class="appointment-status status-completed">Completed</div>
                            </div>
                            <div class="appointment-details">
                                <div class="detail-item">
                                    <span class="detail-label">Treatment</span>
                                    <span class="detail-value">Oil Massage Therapy</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Date & Time</span>
                                    <span class="detail-value">March 18, 2024 - 4:00 PM</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Duration</span>
                                    <span class="detail-value">45 minutes</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Notes</span>
                                    <span class="detail-value">Session completed successfully</span>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <button class="action-btn btn-primary" onclick="viewDetails('APP005')">View Details</button>
                                <button class="action-btn btn-secondary" onclick="bookSimilar('APP005')">Book Similar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancelled Appointments Tab -->
                <div id="cancelled-tab" class="tab-panel" style="display: none;">
                    <div class="empty-state">
                        <h3>No Cancelled Appointments</h3>
                        <p>You haven't cancelled any appointments recently.</p>
                    </div>
                </div>

                <!-- All Appointments Tab -->
                <div id="all-tab" class="tab-panel" style="display: none;">
                    <div class="appointments-list">
                        <!-- This would contain all appointments from all tabs -->
                        <p style="text-align: center; color: #666; padding: 20px;">
                            Showing all appointments from all categories...
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="channeling.php" class="book-new-btn">Book New Appointment</a>
        </div>
    </div>

    <!-- Cancellation Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCancelModal()">&times;</span>
            <h3>Cancel Appointment</h3>
            <p>Are you sure you want to cancel this appointment? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="action-btn btn-danger" onclick="confirmCancel()">Yes, Cancel</button>
                <button class="action-btn btn-secondary" onclick="closeCancelModal()">Keep Appointment</button>
            </div>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div id="rescheduleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRescheduleModal()">&times;</span>
            <h3>Reschedule Appointment</h3>
            <p>Please contact our office to reschedule your appointment or use the online booking system.</p>
            <div class="modal-buttons">
                <button class="action-btn btn-primary" onclick="goToBooking()">Online Booking</button>
                <button class="action-btn btn-secondary" onclick="contactOffice()">Contact Office</button>
                <button class="action-btn btn-secondary" onclick="closeRescheduleModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let currentCancelId = null;

        function showTab(tabName) {
            // Hide all tab panels
            const tabPanels = document.querySelectorAll('.tab-panel');
            tabPanels.forEach(panel => {
                panel.style.display = 'none';
            });
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab panel
            const selectedPanel = document.getElementById(tabName + '-tab');
            if (selectedPanel) {
                selectedPanel.style.display = 'block';
            }
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }

        function viewDetails(appointmentId) {
            alert(`Viewing details for appointment ${appointmentId}`);
            // In real implementation, this would show a detailed view of the appointment
        }

        function reschedule(appointmentId) {
            document.getElementById('rescheduleModal').style.display = 'block';
        }

        function cancelAppointment(appointmentId) {
            currentCancelId = appointmentId;
            document.getElementById('cancelModal').style.display = 'block';
        }

        function confirmCancel() {
            if (currentCancelId) {
                alert(`Appointment ${currentCancelId} has been cancelled successfully.`);
                // In real implementation, this would update the appointment status
                closeCancelModal();
                // Refresh the page or update the UI
                location.reload();
            }
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
            currentCancelId = null;
        }

        function closeRescheduleModal() {
            document.getElementById('rescheduleModal').style.display = 'none';
        }

        function viewPrescription(appointmentId) {
            alert(`Viewing prescription for appointment ${appointmentId}\n\nPrescription Details:\n\n1. Herbal pain relief oil\n   - Apply twice daily\n   - Massage gently for 10 minutes\n\n2. Anti-inflammatory tablets\n   - Take 1 tablet after meals\n   - Twice daily for 10 days\n\nNext visit: As needed`);
        }

        function bookFollowUp(appointmentId) {
            if (confirm('Would you like to book a follow-up appointment?')) {
                window.location.href = 'channeling.php?followup=' + appointmentId;
            }
        }

        function bookSimilar(appointmentId) {
            if (confirm('Would you like to book a similar treatment?')) {
                window.location.href = 'treatment.php?similar=' + appointmentId;
            }
        }

        function goToBooking() {
            window.location.href = 'channeling.php';
        }

        function contactOffice() {
            alert('Contact Information:\n\nPhone: +94 25 8858500\nEmail: infodheergayu@gmail.com\n\nOffice Hours:\nMonday - Friday: 8:00 AM - 6:00 PM\nSaturday: 8:00 AM - 2:00 PM');
        }

        // Filter functionality
        document.getElementById('typeFilter').addEventListener('change', filterAppointments);
        document.getElementById('doctorFilter').addEventListener('change', filterAppointments);
        document.getElementById('searchBox').addEventListener('input', filterAppointments);

        function filterAppointments() {
            const typeFilter = document.getElementById('typeFilter').value;
            const doctorFilter = document.getElementById('doctorFilter').value;
            const searchTerm = document.getElementById('searchBox').value.toLowerCase();
            
            const appointmentCards = document.querySelectorAll('.appointment-card');
            
            appointmentCards.forEach(card => {
                let showCard = true;
                
                // Type filter
                if (typeFilter && !card.classList.contains(typeFilter)) {
                    showCard = false;
                }
                
                // Doctor filter
                if (doctorFilter && showCard) {
                    const doctorName = card.querySelector('.detail-value').textContent.toLowerCase();
                    if (!doctorName.includes(doctorFilter.replace('dr-', '').replace('-', '.'))) {
                        showCard = false;
                    }
                }
                
                // Search filter
                if (searchTerm && showCard) {
                    const cardText = card.textContent.toLowerCase();
                    if (!cardText.includes(searchTerm)) {
                        showCard = false;
                    }
                }
                
                card.style.display = showCard ? 'block' : 'none';
            });
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            const cancelModal = document.getElementById('cancelModal');
            const rescheduleModal = document.getElementById('rescheduleModal');
            
            if (event.target === cancelModal) {
                closeCancelModal();
            }
            if (event.target === rescheduleModal) {
                closeRescheduleModal();
            }
        });

        // Initialize with upcoming tab active
        window.addEventListener('load', function() {
            // Add animation to appointment cards
            const cards = document.querySelectorAll('.appointment-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
                