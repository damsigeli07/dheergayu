<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Our Doctors</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/doctors.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <img src="/dheergayu/public/assets/images/Patient/logo_modern.png" alt="Dheergayu Logo">
                <h1>DHEERGAYU <br> <span>AYURVEDIC MANAGEMENT CENTER</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="/dheergayu/app/Views/Patient/home.php">HOME</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/channeling.php">BOOKING</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/treatment.php">TREATMENTS</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/products.php">SHOP</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <a href="home.php" class="back-btn">← Back to Home</a>
            </div>
        </div>
    </header>

    <div class="content-wrapper">
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
                    <img src="/dheergayu/public/assets/images/Patient/doctor-2.png" alt="Dr. L.M. Perera" class="doctor-photo">
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
                </div>
            </div>

            <!-- Dr. K.Jayawardena -->
            <div class="doctor-card">
                <div class="availability-indicator limited">Limited Slots</div>
                <div class="doctor-image">
                    <img src="/dheergayu/public/assets/images/Patient/doctor-3.png" alt="Dr. K. Jayawardena" class="doctor-photo">
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
                </div>
            </div>

            <!-- Dr. A.T.Gunarathne -->
            <div class="doctor-card">
                <div class="availability-indicator">Available</div>
                <div class="doctor-image">
                    <img src="/dheergayu/public/assets/images/Patient/doctor-1.png" alt="Dr. A.T. Gunarathne" class="doctor-photo">
                </div>
                <div class="doctor-info">
                    <h2 class="doctor-name">Dr. A.T. Gunarathne</h2>
                    <div class="doctor-specialty">Chronic Disease Specialist</div>
                    <div class="doctor-experience">
                        <div class="experience-icon">8</div>
                        <span>8+ Years of Experience</span>
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
                </div>
            </div>
        </div>

        <div class="cta-section">
            <h2 class="cta-title">Ready to Start Your Healing Journey?</h2>
            <p class="cta-text">
                Book a consultation with one of our experienced doctors today and discover how Ayurveda can transform your health and wellbeing.
            </p>
            <a href="channeling.php" class="cta-button">Book Your Appointment</a>
        </div>
    </div>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-column">
                <h3>HELLO</h3>
                <p>Welcome to one of the best Ayurvedic wellness centers in your area!</p>
            </div>
            <div class="footer-column">
                <h3>OFFICE</h3>
                <p>Sri Lanka —</p>
                <p>123 Wellness Street</p>
                <p>Colombo, LK 00100</p>
                <p><a href="mailto:info@dheergayu.com" class="footer-link">info@dheergayu.com</a></p>
                <p>+94 11 234 5678</p>
            </div>
            <div class="footer-column">
                <h3>LINKS</h3>
                <ul>
                    <li><a href="home.php" class="footer-link">Home</a></li>
                    <li><a href="treatment.php" class="footer-link">Treatments</a></li>
                    <li><a href="learn_more.php" class="footer-link">About Us</a></li>
                    <li><a href="channeling.php" class="footer-link">Booking</a></li>
                    <li><a href="#" class="footer-link">Contacts</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>GET IN TOUCH</h3>
                <ul>
                    <li><a href="#" class="social-link">Facebook</a></li>
                    <li><a href="#" class="social-link">X</a></li>
                    <li><a href="#" class="social-link">LinkedIn</a></li>
                    <li><a href="#" class="social-link">Instagram</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
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