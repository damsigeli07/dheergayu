<?php
session_start();


// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userEmail = $isLoggedIn ? $_SESSION['user_email'] : '';

// Get first letter of first name for the icon
$userInitial = '';
if ($isLoggedIn && !empty($userName)) {
    // Extract first name (everything before the first space)
    $nameParts = explode(' ', trim($userName));
    $firstName = $nameParts[0];
    $userInitial = strtoupper(substr($firstName, 0, 1));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Dashboard</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/home.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <img src="/dheergayu/public/assets/images/Patient/logo_modern.png" alt="Dheergayu Logo"> 
                <h1>DHEERGAYU <br> <span>AYURVEDIC MANAGEMENT CENTER</span></h1> 
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="/dheergayu/app/Views/Patient/home.php">HOME</a></li>
                    <li><a href="#" onclick="handleBooking(event)">BOOKING</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/treatment.php">TREATMENTS</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/products.php">SHOP</a></li>
                    <li><a href="#" onclick="handleContact(event)">CONTACT US</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <!-- Cart Icon (Always visible) -->
                <div class="header-cart-icon" onclick="goToCart()" style="margin-right: 20px; position: relative; cursor: pointer;">
                    <span style="font-size: 24px; color: white;">🛒</span>
                    <span class="cart-badge-header" id="cartBadgeHeader" style="display: none; position: absolute; top: -8px; right: -8px; background: #dc3545; color: white; font-size: 11px; font-weight: bold; min-width: 18px; height: 18px; border-radius: 9px; display: flex; align-items: center; justify-content: center; padding: 0 5px;">0</span>
                </div>
                
                <?php if ($isLoggedIn): ?>
                    <!-- User Profile Icon (Show when logged in) -->
                    <div class="user-profile-container" id="userProfile">
                        <div class="user-icon" id="userIcon">
                            <?php echo htmlspecialchars($userInitial); ?>
                            <div class="user-tooltip"><?php echo htmlspecialchars($userName); ?></div>
                        </div>
                        <div class="dropdown-menu" id="dropdownMenu">
                            <a href="patient_profile.php" class="dropdown-item">Profile</a>
                            <a href="patient_appointments.php" class="dropdown-item">My Appointments</a>
                            <a href="my_orders.php" class="dropdown-item">My Orders</a>
                            <a href="my_inquiries.php" class="dropdown-item">My Inquiries</a>
                            <a href="logout.php" class="dropdown-item logout" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Login Button (Show when not logged in) -->
                    <button class="login-btn" onclick="window.location.href='/dheergayu/app/Views/Patient/login.php'">Login</button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Slider -->
    <section class="hero-slider">
        <div class="slider-container">
            <div class="slide active" style="background-image: url('/dheergayu/public/assets/images/Patient/green11.jpg');">
                <div class="hero-content">
                    <p class="subtitle">Massage and therapy</p>
                    <h2>DHEERGAYU TREATMENT CENTER</h2>
                    <p>Give yourself a moment to relax. Find a minute to rejuvenate your body.</p>
                    <button class="btn" onclick="handleBooking(event)">MAKE AN APPOINTMENT</button>
                </div>
            </div>
            <div class="slide" style="background-image: url('/dheergayu/public/assets/images/Patient/green9.png');">
                <div class="hero-content">
                    <p class="subtitle">Relax and Refresh</p>
                    <h2>OUR TREATMENTS</h2>
                    <p>Experience peace and harmony with our curated therapies.</p>
                    <button class="btn" onclick="handleBooking(event)">MAKE AN APPOINTMENT</button>
                </div>
            </div>
            <div class="slide" style="background-image: url('/dheergayu/public/assets/images/Patient/green12.jpg');">
                <div class="hero-content">
                    <p class="subtitle">Wellness for You</p>
                    <h2>AYURVEDIC HEALING</h2>
                    <p>Discover ancient secrets for a balanced mind and body.</p>
                    <button class="btn" onclick="handleBooking(event)">MAKE AN APPOINTMENT</button>
                </div>
            </div>
        </div>
        <div class="slider-dots"></div>
    </section>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <div class="container">
            <p class="subtitle">Welcome to Our</p>
            <h2>MASSAGE THERAPY CENTER</h2>
            <p class="description">You deserve better than a rushed massage by a rookie therapist in a place that makes you feel more stressed</p>
        </div>
    </section>

    <!-- Promo Cards -->
    <section class="promo-cards">
        <div class="container">
            <div class="card">
                <div class="card-image-container">
                    <img src="/dheergayu/public/assets/images/Patient/head-massage.jpg" alt="Our_treatments" class="card-image">
                </div>
                <div class="card-content">
                    <h3>Treatments</h3>
                    <p>Schedule your personalized treatment plan with our expert Ayurvedic practitioners.</p>
                    <button class="btn certificate-btn"><a href="/dheergayu/app/Views/Patient/treatment.php">READ MORE</a></button>
                </div>
            </div>
            <div class="card">
                <div class="card-image-container">
                    <img src="/dheergayu/public/assets/images/Patient/herbal-product.jpg" alt="Special Gifts" class="card-image">
                </div>
                <div class="card-content">
                    <h3>Products</h3>
                    <p>Discover our range of authentic Ayurvedic products crafted to support your wellness journey.</p>
                    <button class="btn certificate-btn"><a href="/dheergayu/app/Views/Patient/products.php">READ MORE</a></button>
                </div>
            </div>
            <div class="card">
                <div class="card-image-container">
                    <img src="/dheergayu/public/assets/images/Patient/stone2.jpg" alt="Book Online" class="card-image">
                </div>
                <div class="card-content">
                    <h3>Book Now</h3>
                    <p>Schedule your healing journey with our expert Ayurvedic practitioners today.</p>
                    <button class="btn certificate-btn" onclick="handleBooking(event)">READ MORE</button>
                </div>
            </div>
        </div>
    </section>

    <!-- We Offer Section -->
    <section class="we-offer-section">
        <div class="container">
            <div class="offer-content">
                <p class="subtitle">Massage Studio</p>
                <h2>WE OFFER HEALTHY SOLUTIONS</h2>
                <div class="offer-buttons">
                    <button class="btn about-us-btn"><a href="/dheergayu/app/Views/Patient/doctors.php">ABOUT US</a></button>
                    <button class="btn get-in-touch-btn"><a href="/dheergayu/app/Views/Patient/learn_more.php">Learn More</a></button>
                </div>
            </div>
        </div>
    </section>

    <!-- NEW: Why Choose Dheergayu Section -->
    <section class="why-choose-section">
        <div class="container">
            <p class="subtitle">Why Choose Dheergayu</p>
            <h2>ANCIENT WISDOM, MODERN EXCELLENCE</h2>
            <div class="decorative-line"></div>
            <p class="description">Experience the perfect blend of time-tested Ayurvedic practices and contemporary healthcare standards</p>
            
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">✨</div>
                    <h3>Traditional Methods</h3>
                    <p>Authentic Ayurvedic formulations prepared using classical techniques passed down through generations</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">✨</div>
                    <h3>Holistic Wellness</h3>
                    <p>Personalized treatment plans addressing your unique health needs for complete mind-body balance</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">✨</div>
                    <h3>Quality & Safety</h3>
                    <p>Hygienic preparation, patient safety protocols, and continuous care follow-up you can trust</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">✨</div>
                    <h3>Expert Practitioners</h3>
                    <p>Certified Ayurvedic doctors with years of experience in traditional healing practices</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">✨</div>
                    <h3>Natural Ingredients</h3>
                    <p>Carefully sourced organic herbs and botanicals ensuring purity and maximum therapeutic benefit</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">✨</div>
                    <h3>Lasting Results</h3>
                    <p>Focus on root-cause healing for sustainable wellness, not just symptom management</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
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
                    <li><a href="contact_us.php" class="footer-link">Contacts</a></li>
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
        // ─── Login state from PHP ────────────────────────────────────────────
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

        // ─── Cart badge: read from DB, not localStorage ──────────────────────
        async function updateHeaderCartCount() {
            try {
                const res  = await fetch('/dheergayu/public/api/cart-api.php?action=get');
                const data = await res.json();
                const cartBadge = document.getElementById('cartBadgeHeader');
                if (!cartBadge) return;
                if (data.success) {
                    const total = (data.items || []).reduce((s, i) => s + i.quantity, 0);
                    cartBadge.textContent    = total;
                    cartBadge.style.display  = total > 0 ? 'flex' : 'none';
                } else {
                    cartBadge.style.display = 'none';
                }
            } catch (e) {
                const cartBadge = document.getElementById('cartBadgeHeader');
                if (cartBadge) cartBadge.style.display = 'none';
            }
        }

        function goToCart() {
            window.location.href = '/dheergayu/app/Views/Patient/cart.php';
        }

        // Load cart count on page load
        updateHeaderCartCount();

        // ─── Booking handler ─────────────────────────────────────────────────
        function handleBooking(event) {
            event.preventDefault();
            if (isLoggedIn) {
                window.location.href = '/dheergayu/app/Views/Patient/channeling.php';
            } else {
                window.location.href = '/dheergayu/app/Views/Patient/login.php';
            }
        }

        function handleContact(event) {
            event.preventDefault();
            if (isLoggedIn) {
                window.location.href = '/dheergayu/app/Views/Patient/contact_us.php';
            } else {
                window.location.href = '/dheergayu/app/Views/Patient/login.php';
            }
        }


        // ─── User profile dropdown ────────────────────────────────────────────
        const userProfile  = document.getElementById('userProfile');
        const userIcon     = document.getElementById('userIcon');
        if (userIcon) {
            userIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                userProfile.classList.toggle('active');
            });
        }
        document.addEventListener('click', function(e) {
            if (userProfile && !userProfile.contains(e.target)) {
                userProfile.classList.remove('active');
            }
        });

        // ─── Hero Slider ──────────────────────────────────────────────────────
        const slides      = document.querySelectorAll('.slide');
        const sliderDots  = document.querySelector('.slider-dots');
        let   currentSlide = 0;

        function showSlide(index) {
            slides.forEach((s, i) => {
                s.classList.toggle('active', i === index);
            });
            updateDots(index);
        }
        function createDots() {
            slides.forEach((_, i) => {
                const dot = document.createElement('span');
                dot.classList.add('dot');
                if (i === 0) dot.classList.add('active');
                dot.addEventListener('click', () => showSlide(i));
                sliderDots.appendChild(dot);
            });
        }
        function updateDots(index) {
            document.querySelectorAll('.dot').forEach((dot, i) => dot.classList.toggle('active', i === index));
        }
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        createDots();
        showSlide(currentSlide);
        setInterval(nextSlide, 5000);

    </script>
</body>
</html>