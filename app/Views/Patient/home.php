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
                </ul>
            </nav>
            <div class="header-right">
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

    <!-- Gift Certificates -->
    <section class="gift-certificates">
        <div class="container">
            <p class="subtitle">Make an Order</p>
            <h2>TREATMENT COMBO SPECIAL PACKAGES</h2>
            <div class="decorative-line"></div>
            <p class="description">There is no better gift to he ones we love than a gift of a healthy, therapeutic, or relaxing session of massage</p>
            <div class="certificate-cards">
                <div class="certificate-card">
                    <p class="certificate-level silver">Silver</p>
                    <div class="certificate-image-circle">
                        <img src="/dheergayu/public/assets/images/Patient/silver2.jpg" alt="Silver Certificate" class="circle-image">
                    </div>
                    <h3 class="price">14 000 LKR</h3>
                </div>
                <div class="certificate-card">
                    <p class="certificate-level gold">Gold</p>
                    <div class="certificate-image-circle">
                        <img src="/dheergayu/public/assets/images/Patient/silver.jpg" alt="Gold Certificate" class="circle-image">
                    </div>
                    <h3 class="price">15 500 LKR</h3>
                </div>
                <div class="certificate-card">
                    <p class="certificate-level platinum">Platinum</p>
                    <div class="certificate-image-circle">
                        <img src="/dheergayu/public/assets/images/Patient/platinum.jpg" alt="Platinum Certificate" class="circle-image">
                    </div>
                    <h3 class="price">8 000 LKR</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <p class="subtitle">Premium Quality</p>
            <h2>DHEERGAYU MASSAGE CENTER</h2>
            <div class="decorative-line"></div>
        </div>
    </section>

    <!-- Massage Therapy Details -->
    <section class="massage-therapy-details">
        <div class="container">
            <div class="therapy-navigation">
                <div class="nav-item active" data-therapy="geothermal">
                    <span class="icon">✨</span>
                    <span>GEOTHERMAL MASSAGE</span>
                </div>
                <div class="nav-item" data-therapy="prenatal">
                    <span class="icon">✨</span>
                    <span>PRENATAL MASSAGE</span>
                </div>
                <div class="nav-item" data-therapy="reflexology">
                    <span class="icon">✨</span>
                    <span>REFLEXOLOGY MASSAGE</span>
                </div>
                <div class="nav-item" data-therapy="trigger">
                    <span class="icon">✨</span>
                    <span>TRIGGER POINT MASSAGE</span>
                </div>
                <div class="nav-item" data-therapy="lymph">
                    <span class="icon">✨</span>
                    <span>MANUAL LYMPH MASSAGE</span>
                </div>
                <div class="nav-item" data-therapy="myofacial">
                    <span class="icon">✨</span>
                    <span>MYOFACIAL MASSAGE</span>
                </div>
            </div>
            <div class="therapy-content">
                <div class="therapy-image">
                    <img id="therapy-img" src="/dheergayu/public/assets/images/Patient/head-massage.jpg" alt="Massage Therapy">
                </div>
                <div class="therapy-description">
                    <h2 id="therapy-title">GEOTHERMAL MASSAGE THERAPY</h2>
                    <p id="therapy-desc">Massage is the manipulation of soft tissues in the body. The techniques are commonly applied with hands, fingers, or a device. Its purpose is for the treatment of body stress or pain:</p>
                    <div class="list-columns">
                        <ul id="therapy-list-1">
                            <li>Back pain</li>
                            <li>Sciatica</li>
                            <li>Sleep disorder</li>
                        </ul>
                        <ul id="therapy-list-2">
                            <li>Hip or leg pain</li>
                            <li>Muscle pain</li>
                            <li>Depression</li>
                        </ul>
                    </div>
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
        // Get login state from PHP
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

        // Handle Booking - Check login status
        function handleBooking(event) {
            event.preventDefault();
            if (isLoggedIn) {
                window.location.href = '/dheergayu/app/Views/Patient/channeling.php';
            } else {
                window.location.href = '/dheergayu/app/Views/Patient/login.php';
            }
        }

        // User Profile Dropdown Toggle
        const userProfile = document.getElementById('userProfile');
        const userIcon = document.getElementById('userIcon');
        const dropdownMenu = document.getElementById('dropdownMenu');

        if (userIcon) {
            userIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                userProfile.classList.toggle('active');
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (userProfile && !userProfile.contains(e.target)) {
                userProfile.classList.remove('active');
            }
        });

        // Hero Slider
        const slides = document.querySelectorAll('.slide');
        const sliderDots = document.querySelector('.slider-dots');
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === index) {
                    slide.classList.add('active');
                }
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
            const dots = document.querySelectorAll('.dot');
            dots.forEach((dot, i) => {
                dot.classList.remove('active');
                if (i === index) {
                    dot.classList.add('active');
                }
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        createDots();
        showSlide(currentSlide);
        setInterval(nextSlide, 5000);

        // Therapy Navigation
        const therapyData = {
            geothermal: {
                title: "GEOTHERMAL MASSAGE THERAPY",
                description: "Massage is the manipulation of soft tissues in the body. The techniques are commonly applied with hands, fingers, or a device. Its purpose is for the treatment of body stress or pain:",
                list1: ["Back pain", "Sciatica", "Sleep disorder"],
                list2: ["Hip or leg pain", "Muscle pain", "Depression"],
                image: "/dheergayu/public/assets/images/Patient/head-massage.jpg"
            },
            prenatal: {
                title: "PREGNANCY OR PRENATAL MASSAGE",
                description: "Pregnancy massage or prenatal massage is therapeutic massage that focuses on the special needs of the Mother-to-be. Pregnancy Massage Therapy can effectively treat and help with:",
                list1: ["Back pain", "Sciatica", "Sleep disorder"],
                list2: ["Hip or leg pain", "Muscle pain", "Depression"],
                image: "/dheergayu/public/assets/images/Patient/stone2.jpg"
            },
            reflexology: {
                title: "REFLEXOLOGY MASSAGE",
                description: "This is a type of massage that involves applying different amounts of pressure to the feet, hands, and ears, because these body parts are connected to certain organs, and it helps with:",
                list1: ["Back pain", "Sciatica", "Sleep disorder"],
                list2: ["Hip or leg pain", "Muscle pain", "Depression"],
                image: "/dheergayu/public/assets/images/Patient/massage2.jpg"
            },
            trigger: {
                title: "TRIGGER POINT MASSAGE",
                description: "Let your sore and painful spots feel the pressure that slowly turns into pleasure after our therapeutic trigger point massage. It is a great way to get rid of pain effectively:",
                list1: ["Back pain", "Sciatica", "Sleep disorder"],
                list2: ["Hip or leg pain", "Muscle pain", "Depression"],
                image: "/dheergayu/public/assets/images/Patient/massage3.jpg"
            },
            lymph: {
                title: "MANUAL LYMPH MASSAGE",
                description: "We help your body maintain its fluid balance with the most amazing lymph massage that is performed manually. You can lose up to 2 lbs after a session and get rid of:",
                list1: ["Back pain", "Sciatica", "Sleep disorder"],
                list2: ["Hip or leg pain", "Muscle pain", "Depression"],
                image: "/dheergayu/public/assets/images/Patient/massage4.jpg"
            },
            myofacial: {
                title: "MYOFACIAL MASSAGE",
                description: "Myofascial therapy among other massages works the broader network of muscles that might be causing your pain and distress. Its purpose is for the treatment of body stress or pain:",
                list1: ["Back pain", "Sciatica", "Sleep disorder"],
                list2: ["Hip or leg pain", "Muscle pain", "Depression"],
                image: "/dheergayu/public/assets/images/Patient/massage5.jpg"
            }
        };

        const therapyNavItems = document.querySelectorAll('.therapy-navigation .nav-item');
        therapyNavItems.forEach(item => {
            item.addEventListener('click', () => {
                therapyNavItems.forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
                
                const therapyType = item.getAttribute('data-therapy');
                const data = therapyData[therapyType];
                
                document.getElementById('therapy-title').textContent = data.title;
                document.getElementById('therapy-desc').textContent = data.description;
                document.getElementById('therapy-img').src = data.image;
                
                const list1 = document.getElementById('therapy-list-1');
                const list2 = document.getElementById('therapy-list-2');
                
                list1.innerHTML = data.list1.map(item => `<li>${item}</li>`).join('');
                list2.innerHTML = data.list2.map(item => `<li>${item}</li>`).join('');
            });
        });
    </script>
</body>
</html>