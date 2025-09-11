<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userEmail = $isLoggedIn ? $_SESSION['user_email'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Dashboard</title>
    <link rel="stylesheet" href="css/home.css?v=<?php echo time(); ?>">
</head>

<body>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <img src="img/dheergayu.png" alt="Dheergayu Logo" class="logo">
                <h1 class="header-title">Dheergayu</h1>
            </nav>
        </div>
        <div class="header-right" id="headerRight">
            <?php if ($isLoggedIn): ?>
                <!-- After login navigation -->
                <a href="home.php" class="nav-btn">Home</a>
                <a href="channeling.php" class="nav-btn" onclick="handleChanneling()">Consultations</a>
                <a href="after_login_treatment.php" class="nav-btn" onclick="handleTreatmentNavigation()">Treatments</a>
                <div class="profile-container">
                    <button class="profile-btn" onclick="toggleProfileDropdown()">👤</button>
                    <div class="profile-dropdown" id="profileDropdown">
                        <a href="patient_profile.php" class="dropdown-item" onclick="showMyProfile()">My Profile</a>
                        <a href="patient_appointments.php" class="dropdown-item" onclick="showMyAppointments()">My Appointments</a>
                        <a href="logout.php" class="dropdown-item" onclick="logout()">Logout</a>
                    </div>
                </div>
                <span style="margin-left: 10px; font-size: 0.9em;"><?php echo htmlspecialchars($userType); ?></span>
            <?php else: ?>
                <!-- Before login navigation -->
                <a href="home.php" class="nav-btn">Home</a>
                <a href="before_login_treatment.php" class="nav-btn" onclick="handleTreatmentNavigation()">Our Treatments</a>
                <a href="products.php" class="nav-btn">Our Products</a>
                <a href="login.php" class="nav-btn">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($isLoggedIn): ?>
        <div class="welcome-message" style="background: #e8f5e8; padding: 10px; text-align: center; margin-bottom: 20px;">
            <p>Welcome back, <?php echo htmlspecialchars($userName); ?>! 🌿</p>
        </div>
    <?php endif; ?>

    <main class="hero-section">
        <div class="floating-circles">
            <div class="circle circle1">
                <img src="img/ayurveda-home-4.jpg" alt="Ayurvedic herbs and wellness">
            </div>
            
            <div class="circle circle2">
                <img src="img/ayurveda-home-6.jpg" alt="Ayurvedic treatment">
            </div>
            
            <div class="circle circle3">
                <img src="img/ayurveda-home_1.jpg" alt="Ayurvedic wellness">
            </div>
        </div>

        <div class="hero-box">
            <img src="img/dheergayu.png" alt="logo" width="100px" height="100px">
            <h1>Natural Healing Journey</h1>
            <p>Discover the power of traditional wellness through natural remedies, herbal medicine, and holistic healing practices</p>
            <a href="doctors.php" class="cta-button" onclick="handleExploreServices()">Explore Our Services</a>
        </div>
    </main>

    <div class="gallery">
        <div class="gallery-item">
            <div class="image-container">
                <img src="img/health-treatments.jpg" alt="health-treatments">
            </div>
            <div class="content">
                <h4>Therapeutic Treatments</h4>
                <p>Experience authentic Ayurvedic spa therapies with herbal oils, healing potions, and traditional wellness practices.</p>
                <a href="#" class="learn-more" onclick="handleTreatmentNavigation()">Our Treatments</a>
            </div>
        </div>

        <div class="gallery-item">
            <div class="image-container">
                <img src="img/herbal-product.jpg" alt="natural-products">
            </div>
            <div class="content">
                <h4>Natural Wellness Products</h4>
                <p>Handcrafted essential oils, herbal soaps, and therapeutic products made with organic ingredients and traditional methods.</p>
                <a href="products.php" class="learn-more">Our Products</a>
            </div>
        </div>
    </div>

    <section class="description-section">
        <div class="description-container">
            <div class="section-subtitle">Ancient Wisdom Meets Modern Care</div>
            <h1 class="main-title">Where Ancient Traditions Meet Holistic Healing</h1>
            <p class="description-text">
                Discover serenity at Dheergayu, your trusted Ayurvedic wellness center. Rooted in age-old Ayurvedic traditions, we offer tailored wellness programs, from rejuvenation to stress relief. Indulge in personalized treatments, tranquil spaces and nourishing therapies as you embark on a transformative journey to harmonize your mind, body and spirit.
            </p>
            <button class="fact-sheet-btn" onclick="handleBookNow()">Book Now</button>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>Dheergayu Ayurveda</h3>
                <ul>
                    <li><a href="#" onclick="navigateTo('Home')">Home</a></li>
                    <li><a href="#" onclick="navigateTo('Accommodation')">Accommodation</a></li>
                    <li><a href="#" onclick="navigateTo('Offers')">Offers</a></li>
                    <li><a href="#" onclick="handleTreatmentNavigation()">Treatments</a></li>
                    <li><a href="#" onclick="navigateTo('Dining')">Dining</a></li>
                    <li><a href="#" onclick="navigateTo('Experiences')">Experiences</a></li>
                    <li><a href="#" onclick="navigateTo('Facilities')">Facilities</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Get in Touch</h3>
                <div class="contact-info">
                    <p><strong>Dheergayu Ayurveda Center</strong></p>
                    <p><strong>📧</strong> infodheergayu@gmail.com</p>
                    <p><strong>📞</strong> +94 25 8858500</p>
                </div>
            </div>

            <div class="footer-section">
                <h3>Stay Connected with Ayurveda</h3>
                <p style="color: #666; margin-bottom: 20px;">Subscribe to our newsletter for wellness tips and updates</p>
                <div class="social-links">
                    <a href="#" onclick="openSocial('Facebook')">📘</a>
                    <a href="#" onclick="openSocial('Twitter')">🐦</a>
                    <a href="#" onclick="openSocial('Youtube')">📺</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            © 2024 Dheergayu Ayurveda. All rights reserved.<br>
            Website Designed and Developed by <a href="#">eMarketing.lk</a><br>
            <a href="#" onclick="navigateTo('Privacy Policy')">Privacy Policy</a> |
            <a href="#" onclick="navigateTo('Terms of Service')">Terms And Condition</a> |
            <a href="#" onclick="navigateTo('Site Map')">Sitemap</a>
        </div>
    </footer>

    <!-- Login Required Model -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLoginModal()">&times;</span>
            <h2>Login Required</h2>
            <p>Please login to book appointments and access our services.</p>
            <div class="modal-buttons">
                <button class="modal-btn primary" onclick="goToLogin()">Login Now</button>
                <button class="modal-btn secondary" onclick="closeLoginModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Get login state from PHP
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        const userType = '<?php echo htmlspecialchars($userType); ?>';

        function handleTreatmentNavigation() {
            if (isLoggedIn) {
                // Navigate to after login treatment page (with appointment booking)
                window.location.href = 'after_login_treatment.php';
            } else {
                // Navigate to before login treatment page (information only)
                window.location.href = 'before_login_treatment.php';
            }
        }

        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        function handleBookNow() {
            if (isLoggedIn) {
                // Navigate to channeling page
                window.location.href = 'channeling.php';
            } else {
                // Show login modal
                document.getElementById('loginModal').style.display = 'block';
            }
        }

        function handleChanneling() {
            if (isLoggedIn) {
                window.location.href = 'channeling.php';
            } else {
                document.getElementById('loginModal').style.display = 'block';
            }
        }

        function handleExploreServices() {
            // Navigate to doctors info page
            window.location.href = 'doctors.php';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
        }

        function goToLogin() {
            window.location.href = 'login.php';
        }

        function showMyProfile() {
            window.location.href = 'patient_profile.php';
        }

        function showMyAppointments() {
            window.location.href = 'patient_appointments.php';
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }

        function navigateTo(page) {
            if (page === 'Treatments') {
                handleTreatmentNavigation();
            } else {
                alert(`Navigating to: ${page}`);
            }
        }

        function openSocial(platform) {
            alert(`Opening ${platform} page...`);
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(event) {
            if (!event.target.matches('.profile-btn')) {
                const dropdowns = document.getElementsByClassName('profile-dropdown');
                for (let dropdown of dropdowns) {
                    if (dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show');
                    }
                }
            }
        });
    </script>
</body>
</html>