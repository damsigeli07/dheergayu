<?php


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
                    <li><a href="/dheergayu/app/Views/Patient/home.php">HOME</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/channeling.php">BOOKING</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/treatment.php">TREATMENTS</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/products.php">SHOP</a></li>
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
                    <a href="/dheergayu/app/Views/Patient/treatment.php"><button class="btn">MAKE AN APPOINTMENT</button></a>
                </div>
            </div>
            <div class="slide" style="background-image: url('/dheergayu/public/assets/images/Patient/green9.png');">
                <div class="hero-content">
                    <p class="subtitle">Relax and Refresh</p>
                    <h2>OUR TREATMENTS</h2>
                    <p>Experience peace and harmony with our curated therapies.</p>
                    <a href="/dheergayu/app/Views/Patient/treatment.php"><button class="btn">MAKE AN APPOINTMENT</button></a>
                </div>
            </div>
            <div class="slide" style="background-image: url('/dheergayu/public/assets/images/Patient/green12.jpg');">
                <div class="hero-content">
                    <p class="subtitle">Wellness for You</p>
                    <h2>AYURVEDIC HEALING</h2>
                    <p>Discover ancient secrets for a balanced mind and body.</p>
                    <a href="/dheergayu/app/Views/Patient/treatment.php"><button class="btn">MAKE AN APPOINTMENT</button></a>
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
                    <button class="btn certificate-btn"><a href="/dheergayu/app/Views/Patient/channeling.php">READ MORE</a></button>
                </div>
            </div>
        </div>
    </section>

    <section class="we-offer-section">
        <div class="container">
            <div class="offer-content">
                <p class="subtitle">Massage Studio</p>
                <h2>WE OFFER HEALTHY SOLUTIONS</h2>
                <div class="offer-buttons">
                    <button class="btn about-us-btn"><a href="/dheergayu/app/Views/Patient/doctors.php">ABOUT US</a></button>
                    <button class="btn get-in-touch-btn">GET IN TOUCH</button>
                </div>
            </div>
        </div>
    </section>



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
                        <img src="/dheergayu/public/assets/images/Patient/silver.jpg" alt="Silver Certificate" class="circle-image">
                    </div>
                    <button class="btn certificate-btn">Rs. 14,000.00</button>
                </div>
                <div class="certificate-card">
                    <p class="certificate-level gold">Gold</p>
                    <div class="certificate-image-circle">
                        <img src="/dheergayu/public/assets/images/Patient/health-treatments.jpg" alt="Gold Certificate" class="circle-image">
                    </div>
                    <button class="btn certificate-btn-alt">Rs. 15,500.00</button>
                </div>
                <div class="certificate-card">
                    <p class="certificate-level platinum">Platinum</p>
                    <div class="certificate-image-circle">
                        <img src="/dheergayu/public/assets/images/Patient/platinum.jpg" alt="Platinum Certificate" class="circle-image">
                    </div>
                    <button class="btn certificate-btn">Rs. 8,000.00</button>
                </div>
            </div>
        </div>
    </section>

    <section class="products-section">
        <div class="container">
            <p class="subtitle">Premium Quality</p>
            <h2>DHEERGAYU MASSAGE CENTER</h2>
            <div class="decorative-line"></div>
        </div>
    </section>

    <section class="massage-therapy-details">
        <div class="container">
            <div class="therapy-navigation">
                <div class="nav-item active" data-therapy="geothermal">
                    <span class="icon">🛀</span>
                    <span>GEOTHERMAL MASSAGE</span>
                </div>
                <div class="nav-item" data-therapy="prenatal">
                    <span class="icon">🤰</span>
                    <span>PRENATAL MASSAGE</span>
                </div>
                <div class="nav-item" data-therapy="reflexology">
                    <span class="icon">👣</span>
                    <span>REFLEXOLOGY MASSAGE</span>
                </div>
                <div class="nav-item" data-therapy="trigger">
                    <span class="icon">👆</span>
                    <span>TRIGGER POINT MASSAGE</span>
                </div>
                <div class="nav-item" data-therapy="lymph">
                    <span class="icon">🤲</span>
                    <span>MANUAL LYMPH MASSAGE</span>
                </div>
                <div class="nav-item" data-therapy="myofacial">
                    <span class="icon">🛌</span>
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

    <footer class="main-footer">
        <div class="container">
            <div class="footer-column">
                <h3>HELLO</h3>
                <p>Welcome to one of the best therapeutic massage studios in your area!</p>
            </div>
            <div class="footer-column">
                <h3>Main branch</h3>
                <p>Colombo —</p>
                <p>785 Street, Office 478</p>
                <p>Colombo</p>
                <p><a href="mailto:info@email.com" class="footer-link">info@email.com</a></p>
                <p>+94 712841259</p>
            </div>
            <div class="footer-column">
                <h3>LINKS</h3>
                <ul>
                    <li><a href="/dheergayu/app/Views/Patient/home.php" class="footer-link">Home</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/doctors.php" class="footer-link">About Us</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/channeling.php" class="footer-link">Booking</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/contact.php" class="footer-link">Contacts</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>GET IN TOUCH</h3>
                <ul>
                    <li><a href="#" class="social-link"> Facebook</a></li>
                    <li><a href="#" class="social-link"> X</a></li>
                    <li><a href="#" class="social-link"> Instagram</a></li>
                </ul>
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
        setInterval(nextSlide, 5000);

        // Make all "MAKE AN APPOINTMENT" buttons trigger the login modal
        document.querySelectorAll('.hero-content .btn').forEach(btn => {
            btn.addEventListener('click', handleBookNow);
        });

        // Therapy navigation with content switching
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

         // Dropdown functionality (Header navigation)
         document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.addEventListener('click', function(event) {
                this.classList.toggle('active');
                event.stopPropagation();
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

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('loginModal');
            if (event.target === modal) {
                closeLoginModal();
            }
        });
    </script>
</body>
</html>