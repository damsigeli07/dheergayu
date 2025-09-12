<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Our Doctors</title>
    <link rel="stylesheet" href="css/doctors.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <img src="img/dheergayu.png" alt="Dheergayu Logo" class="logo">
                <h1 class="header-title">Dheergayu</h1>
            </nav>
        </div>
        <div>
            <a href="home.php" class="back-btn">← Back to Home</a>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Meet Our Expert Doctors</h1>
            <p class="page-subtitle">
                Our experienced Ayurvedic practitioners combine traditional wisdom with modern healthcare approaches to provide comprehensive wellness solutions tailored to your individual needs.
            </p>
        </div>

        <div class="doctors-grid">
            <!-- Dr. L.M.Perera -->
            <div class="doctor-card">
                <div class="availability-indicator">Available</div>
                <div class="doctor-image">

                <img src="img/doctor1.jpg" alt="Dr. L.M. Perera" class="doctor-photo">
                </div>
                <div class="doctor-info">
                    <h2 class="doctor-name">Dr. L.M. Perera</h2>
                    <div class="doctor-specialty">Senior Ayurvedic Physician</div>
                    <div class="doctor-experience">
                        <div class="experience-icon">15</div>
                        <span>15+ Years of Experience</span>
                    </div>
                    <p class="doctor-description">
                        Dr. L.M. Perera is a renowned Ayurvedic physician specializing in traditional healing methods. With over 15 years of experience, he has helped thousands of patients achieve optimal health through personalized Ayurvedic treatments and lifestyle guidance.
                    </p>
                    <div class="specializations">
                        <h4>Specializations:</h4>
                        <div class="specialization-tags">
                            <span class="tag">Panchakarma</span>
                            <span class="tag">Digestive Disorders</span>
                            <span class="tag">Stress Management</span>
                            <span class="tag">Joint Pain Treatment</span>
                        </div>
                    </div>
                    <!-- <button class="book-appointment-btn" onclick="bookAppointment('Dr. L.M. Perera')">
                        Book Consultation
                    </button> -->
                </div>
            </div>

            <!-- Dr. K.Jayawardena -->
            <div class="doctor-card">
                <div class="availability-indicator limited">Limited Slots</div>
                <div class="doctor-image">
                    <img src="img/doctor2.jpg" alt="Dr. K. Jayawardena" class="doctor-photo">
                </div>
                <div class="doctor-info">
                    <h2 class="doctor-name">Dr. K. Jayawardena</h2>
                    <div class="doctor-specialty">Women's Health Specialist</div>
                    <div class="doctor-experience">
                        <div class="experience-icon">12</div>
                        <span>12+ Years of Experience</span>
                    </div>
                    <p class="doctor-description">
                        Dr. K. Jayawardena specializes in women's health and reproductive wellness using Ayurvedic principles. She is particularly known for her expertise in treating hormonal imbalances and providing comprehensive prenatal and postnatal care.
                    </p>
                    <div class="specializations">
                        <h4>Specializations:</h4>
                        <div class="specialization-tags">
                            <span class="tag">Women's Health</span>
                            <span class="tag">Fertility Treatment</span>
                            <span class="tag">Hormonal Balance</span>
                            <span class="tag">Prenatal Care</span>
                        </div>
                    </div>
                    <!-- <button class="book-appointment-btn" onclick="bookAppointment('Dr. K. Jayawardena')">
                        Book Consultation
                    </button> -->
                </div>
            </div>

            <!-- Dr. A.T.Gunarathne -->
            <div class="doctor-card">
                <div class="availability-indicator">Available</div>
                <div class="doctor-image">
                    <img src="img/doctor3.jpg" alt="Dr. A.T. Gunarathne" class="doctor-photo">
                </div>
                <div class="doctor-info">
                    <h2 class="doctor-name">Dr. A.T. Gunarathne</h2>
                    <div class="doctor-specialty">Chronic Disease Specialist</div>
                    <div class="doctor-experience">
                        <div class="experience-icon">18</div>
                        <span>18+ Years of Experience</span>
                    </div>
                    <p class="doctor-description">
                        Dr. A.T. Gunarathne is an expert in treating chronic diseases through Ayurvedic medicine. His holistic approach focuses on addressing root causes rather than just symptoms, providing long-term solutions for complex health conditions.
                    </p>
                    <div class="specializations">
                        <h4>Specializations:</h4>
                        <div class="specialization-tags">
                            <span class="tag">Diabetes Management</span>
                            <span class="tag">Hypertension</span>
                            <span class="tag">Respiratory Disorders</span>
                            <span class="tag">Autoimmune Conditions</span>
                        </div>
                    </div>
                    <!-- <button class="book-appointment-btn" onclick="bookAppointment('Dr. A.T. Gunarathne')">
                        Book Consultation
                    </button> -->
                </div>
            </div>
        </div>

        <div class="cta-section">
            <h2 class="cta-title">Ready to Start Your Healing Journey?</h2>
            <p class="cta-text">
                Book a consultation with one of our experienced doctors today and discover how Ayurveda can transform your health and wellbeing.
            </p>
            <a href="channeling.php" class="cta-button" onclick="handleGeneralBooking()">Book Your Appointment</a>
        </div>
    </div>

    <script>
        function bookAppointment(doctorName) {
            // Check if user is logged in (this would come from your backend in real implementation)
            const isLoggedIn = sessionStorage.getItem('isLoggedIn') === 'true';
            
            if (isLoggedIn) {
                // Navigate to channeling page with pre-selected doctor
                window.location.href = `channeling.php?doctor=${encodeURIComponent(doctorName)}`;
            } else {
                // Show login required message
                if (confirm(`Please login to book an appointment with ${doctorName}.\n\nWould you like to go to the login page now?`)) {
                    window.location.href = 'login.php';
                }
            }
        }

        function handleGeneralBooking() {
            const isLoggedIn = sessionStorage.getItem('isLoggedIn') === 'true';
            
            if (isLoggedIn) {
                window.location.href = 'channeling.php';
            } else {
                if (confirm('Please login to book appointments.\n\nWould you like to go to the login page now?')) {
                    window.location.href = 'login.php';
                }
            }
        }

        // Add smooth scroll animation for cards
        window.addEventListener('load', function() {
            const cards = document.querySelectorAll('.doctor-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(50px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>