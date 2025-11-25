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
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/home.css?v=<?php echo time(); ?>">
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
                    <li><a href="#">HOME</a></li>
                    <li><a href="#">BOOKING</a></li>
                    <li><a href="#">BLOG</a></li>
                    <li><a href="#">SHOP</a></li>
                </ul>
            </nav>
            <div class="header-right"> 
                <div class="language-selector dropdown">
                    </div>
            </div>
        </div>
    </header>
    <section class="hero-slider">
        <div class="slider-container">
            <div class="slide active" style="background-image: url('/dheergayu/public/assets/images/Patient/green11.jpg');">
                <div class="hero-content">
                    <p class="subtitle">Massage and therapy</p>
                    <h2>DHEERGAYU TREATMENT CENTER</h2>
                    <p>Give yourself a moment to relax. Find a minute to rejuvenate your body.</p>
                    <button class="btn">MAKE AN APPOINTMENT</button>
                </div>
            </div>
            <div class="slide" style="background-image: url('/dheergayu/public/assets/images/Patient/green9.png');">
                <div class="hero-content">
                    <p class="subtitle">Relax and Refresh</p>
                    <h2>OUR TREATMENTS</h2>
                    <p>Experience peace and harmony with our curated therapies.</p>
                    <button class="btn">MAKE AN APPOINTMENT</button>
                </div>
            </div>
            <div class="slide" style="background-image: url('/dheergayu/public/assets/images/Patient/green6.jpg');">
                <div class="hero-content">
                    <p class="subtitle">Wellness for You</p>
                    <h2>AYURVEDIC HEALING</h2>
                    <p>Discover ancient secrets for a balanced mind and body.</p>
                    <button class="btn">MAKE AN APPOINTMENT</button>
                </div>
            </div>
        </div>
        <div class="slider-dots"></div>
    </section>

    <section class="welcome-section">
        <div class="container">
            <p class="subtitle">Welcome to Our</p>
            <h2>MASSAGE THERAPY CENTER</h2>
            <p class="description">You deserve better than a rushed massage by a rookie therapist in a place that makes you feel more stressed</p>
        </div>
    </section>

    <section class="promo-cards">
        <div class="container">
            <div class="card">
                <div class="card-image-container">
                    <img src="/dheergayu/public/assets/images/Patient/head-massage.jpg" alt="Our_treatments" class="card-image">
                </div>
                <h3>Our Treatments</h3>
                <p>FIRHNERIVH HFOERHFIUHER HRFEURH</p>
                <button class="btn card-btn">READ MORE</button>
            </div>
            <div class="card">
                <div class="card-image-container">
                    <img src="/dheergayu/public/assets/images/Patient/herbal-product.jpg" alt="Special Gifts" class="card-image">
                </div>
                <h3>Our Products</h3>
                <p>FIRHNERIVH HFOERHFIUHER HRFEURH</p>
                <button class="btn card-btn-alt">READ MORE</button>
            </div>
            <div class="card">
                <div class="card-image-container">
                    <img src="/dheergayu/public/assets/images/Patient/stone2.jpg" alt="Book Online" class="card-image">
                </div>
                <h3>Book Now</h3>
                <p>APPOINTMENTS ....</p>
                <button class="btn card-btn">READ MORE</button>
            </div>
        </div>
    </section>

    <section class="we-offer-section">
        <div class="container">
            <div class="offer-content">
                <p class="subtitle">Massage Studio</p>
                <h2>WE OFFER HEALTHY SOLUTION</h2>
                <div class="offer-buttons">
                    <button class="btn about-us-btn">ABOUT US</button>
                    <button class="btn get-in-touch-btn">GET IN TOUCH</button>
                </div>
            </div>
            <div class="offer-background">
                <img src="/dheergayu/public/assets/images/Patient/head-massage.jpg" alt="Massage Background" class="offer-bg-image">
            </div>
        </div>
    </section>



    <section class="gift-certificates">
        <div class="container">
            <p class="subtitle">Make an Order</p>
            <h2>GIFT CERTIFICATES</h2>
            <div class="decorative-line"></div>
            <p class="description">There is no better gift to he ones we love than a gift of a healthy, therapeutic, or relaxing session of massage</p>

            <div class="certificate-cards">
                <div class="certificate-card">
                    <p class="certificate-level silver">Silver</p>
                    <div class="certificate-image-circle">
                        <img src="/dheergayu/public/assets/images/Patient/treatments.png" alt="Silver Certificate" class="circle-image">
                        <span class="price-badge">$100</span>
                    </div>
                    <button class="btn certificate-btn">I WANT THIS CARD</button>
                </div>
                <div class="certificate-card">
                    <p class="certificate-level gold">Gold</p>
                    <div class="certificate-image-circle">
                        <img src="/dheergayu/public/assets/images/Patient/treatments.png" alt="Gold Certificate" class="circle-image">
                        <span class="price-badge-alt">$300</span>
                    </div>
                    <button class="btn certificate-btn-alt">I WANT THIS CARD</button>
                </div>
                <div class="certificate-card">
                    <p class="certificate-level platinum">Platinum</p>
                    <div class="certificate-image-circle">
                        <img src="/dheergayu/public/assets/images/Patient/treatments.png" alt="Platinum Certificate" class="circle-image">
                        <span class="price-badge">$500</span>
                    </div>
                    <button class="btn certificate-btn">I WANT THIS CARD</button>
                </div>
            </div>
        </div>
    </section>

    <section class="products-section">
        <div class="container">
            <p class="subtitle">Premium Quality</p>
            <h2>NATURAL BEAUTY PRODUCTS</h2>
            <div class="decorative-line"></div>

            <div class="product-grid">
                <div class="product-item">
                    <div class="product-image-box">
                        <img src="/dheergayu/public/assets/images/Patient/treatments.png" alt="Supreme Skincare">
                    </div>
                    <p class="product-category">BODY, COSMETICS, HYDRATION</p>
                    <p class="product-name">SUPREME SKINCARE</p>
                    <div class="stars">★★★★☆</div>
                    <p class="product-price">$39.00 – $47.00</p>
                </div>
                <div class="product-item">
                    <div class="product-image-box">
                        <img src="/dheergayu/public/assets/images/Patient/treatments.png" alt="Eye Contour Mask">
                    </div>
                    <p class="product-category">FACIAL, HYDRATION, SALE</p>
                    <p class="product-name">EYE CONTOUR MASK</p>
                    <div class="stars">★★★★☆</div>
                    <p class="product-price">$47.00 – $57.00</p>
                </div>
                <div class="product-item">
                    <div class="product-image-box">
                        <img src="/dheergayu/public/assets/images/Patient/treatments.png" alt="Black Rose Serum">
                    </div>
                    <p class="product-category">COSMETICS, HYDRATION, PROCEDURE</p>
                    <p class="product-name">BLACK ROSE SERUM</p>
                    <div class="stars">★★★★★</div>
                    <p class="product-price">$89.00 – $99.00</p>
                </div>
                <div class="product-item">
                    <div class="product-image-box">
                        <img src="/dheergayu/public/assets/images/Patient/treatments.png" alt="Natural Soft Soap">
                    </div>
                    <p class="product-category">CREAM, FACIAL, HYDRATION</p>
                    <p class="product-name">NATURAL SOFT SOAP</p>
                    <div class="stars">★★★☆☆</div>
                    <p class="product-price">$30.00 – $35.00</p>
                </div>
            </div>
            <button class="btn view-all-products-btn">VIEW ALL PRODUCTS</button>
        </div>
    </section>

    <section class="massage-therapy-details">
        <div class="container">
            <div class="therapy-navigation">
                <div class="nav-item active">
                    <span class="icon">🛀</span>
                    <span>GEOTHERMAL MASSAGE</span>
                </div>
                <div class="nav-item">
                    <span class="icon">🤰</span>
                    <span>PRENATAL MASSAGE</span>
                </div>
                <div class="nav-item">
                    <span class="icon">👣</span>
                    <span>REFLEXOLOGY MASSAGE</span>
                </div>
                <div class="nav-item">
                    <span class="icon">👆</span>
                    <span>TRIGGER POINT MASSAGE</span>
                </div>
                <div class="nav-item">
                    <span class="icon">🤲</span>
                    <span>MANUAL LYMPH MASSAGE</span>
                </div>
                <div class="nav-item">
                    <span class="icon">🛌</span>
                    <span>MYOFACIAL MASSAGE</span>
                </div>
            </div>
            <div class="therapy-content">
                <div class="therapy-image">
                    <img src="/dheergayu/public/assets/images/Patient/head-massage.jpg" alt="Geothermal Massage">
                </div>
                <div class="therapy-description">
                    <h2>GEOTHERMAL MASSAGE THERAPY</h2>
                    <p>Massage is the manipulation of soft tissues in the body. The techniques are commonly applied with hands, fingers, or a device. Its purpose is for the treatment of body stress or pain:</p>
                    <div class="list-columns">
                        <ul>
                            <li>• Back pain</li>
                            <li>• Sciatica</li>
                            <li>• Sleep disorder</li>
                        </ul>
                        <ul>
                            <li>• Hip or leg pain</li>
                            <li>• Muscle pain</li>
                            <li>• Depression</li>
                        </ul>
                    </div>
                    <button class="btn learn-more-btn">LEARN MORE</button>
                </div>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-column">
                <h3>HELLO</h3>
                <p>Welcome to one of the best therapeutic massage studios in your area!</p>
            </div>
            <div class="footer-column">
                <h3>OFFICE</h3>
                <p>Germany —</p>
                <p>785 15h Street, Office 478</p>
                <p>Berlin, De 81566</p>
                <p><a href="mailto:info@email.com" class="footer-link">info@email.com</a></p>
                <p>+1 840 841 25 69</p>
            </div>
            <div class="footer-column">
                <h3>LINKS</h3>
                <ul>
                    <li><a href="#" class="footer-link">Home</a></li>
                    <li><a href="#" class="footer-link">Services</a></li>
                    <li><a href="#" class="footer-link">About Us</a></li>
                    <li><a href="#" class="footer-link">Booking</a></li>
                    <li><a href="#" class="footer-link">Contacts</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>GET IN TOUCH</h3>
                <ul>
                    <li><a href="#" class="social-link">f Facebook</a></li>
                    <li><a href="#" class="social-link">x X</a></li>
                    <li><a href="#" class="social-link">🏀 Dribble</a></li>
                    <li><a href="#" class="social-link">📷 Instagram</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>ThemeREX © 2025. All Rights Reserved.</p>
                <a href="#top" class="scroll-to-top">▲</a>
            </div>
        </div>
    </footer>

        <!-- Login Required Modal -->
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
                window.location.href = 'treatment.php';
            } else {
                window.location.href = 'before_login_treatment.php';
            }
        }

        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        function handleBookNow() {
            if (isLoggedIn) {
                window.location.href = 'channeling.php';
            } else {
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

        // Hero Slider JavaScript
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

        // Initialize slider
        createDots();
        showSlide(currentSlide);
        setInterval(nextSlide, 5000); // Change slide every 5 seconds

        // Therapy navigation active state (Simulates content switching)
        const therapyNavItems = document.querySelectorAll('.therapy-navigation .nav-item');
        therapyNavItems.forEach(item => {
            item.addEventListener('click', () => {
                therapyNavItems.forEach(nav => nav.classList.remove('active'));
                item.classList.add('active');
                // You would add code here to change the image and text in the .therapy-content
            });
        });

         // Dropdown functionality (Header navigation)
         document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.addEventListener('click', function(event) {
                // Toggle active class on click
                this.classList.toggle('active');
                event.stopPropagation(); // Prevent immediate closing
            });
        });

        // Close dropdowns if clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        });

        // Scroll to top functionality
        document.querySelector('.scroll-to-top').addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</body>
</html>