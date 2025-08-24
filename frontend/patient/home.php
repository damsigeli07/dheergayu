<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Dashboard</title>
    <link rel="stylesheet" href="css/home.css?v=<?php echo time(); ?>">

<body>
    <header class="header">
        <div class="header-left">
            <nav class="navigation">
                <img src="img/dheergayu.png" alt="Dheergayu Logo" class="logo">
                <h1 class="header-title">Dheergayu</h1>
            </nav>
        </div>
        <div class="header-right">
            <a href="home.php" class="nav-btn">Home</a>
            <a href="channeling.php" class="nav-btn">Consultations</a>
            <a href="treatment.php" class="nav-btn">Our Treatments</a>
            <a href="products.php" class="nav-btn">Our Products</a>
            <a href="Signup.php" class="nav-btn"><u>Book Now</u></a>
        </div>
    </header>

    <main class="hero-section">
        <div class="floating-circles">
            <div class="circle circle1">
                <img src="img/ayurveda-home-4.jpg" alt="Ayurvedic herbs and wellness">
            </div>
            
            <div class="circle circle2">
                <img src="img/ayurveda-home-6.jpg">
            </div>
            
            <div class="circle circle3">
                <img src="img/ayurveda-home_1.jpg" alt="home-1">
            </div>

        </div>

        <div class="hero-box">
            <img src="img/dheergayu.png" alt="logo" width="100px" height="100px">
            <h1>Natural Healing Journey</h1>
            <p>Discover the power of traditional wellness through natural remedies, herbal medicine, and holistic healing practices</p>
            <a href="#services" class="cta-button">Explore Our Services</a>
        </div>
    </main>
        <div class="gallery">
            <div class="gallery-item" onclick="showDetails('herbs')">
                <div class="image-container">
                    <img src="img/herbal-medicines.jpg" alt="herbal-medicines">
                </div>
                <div class="content">
                    <h4>Natural Herbal Remedies</h4>
                    <p>Discover the healing power of ancient herbs, carefully prepared with mortar and pestle, enriched with honey and essential oils.</p>
                    <button class="learn-more">Learn More</button>
                </div>
            </div>

            <div class="gallery-item" onclick="showDetails('spa')">
                <div class="image-container">
                    <img src="img/health-treatments.jpg" alt="health-treatments">
                </div>
                <div class="content">
                    <h4>Therapeutic Treatments</h4>
                    <p>Experience authentic Ayurvedic spa therapies with herbal oils, healing potions, and traditional wellness practices.</p>
                    <button class="learn-more">Book Session</button>
                </div>
            </div>

            <div class="gallery-item" onclick="showDetails('wellness')">
                <div class="image-container">
                    <img src="img/herbal-product.jpg" alt="natural-products">
                </div>
                <div class="content">
                    <h4>Natural Wellness Products</h4>
                    <p>Handcrafted essential oils, herbal soaps, and therapeutic products made with organic ingredients and traditional methods.</p>
                    <button class="learn-more">Shop Now</button>
                </div>
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
                <button class="fact-sheet-btn" onclick="navigateTo('About')">Book Now</button>
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
                    <li><a href="#" onclick="navigateTo('Treatments')">Treatments</a></li>
                    <li><a href="#" onclick="navigateTo('Dining')">Dining</a></li>
                    <li><a href="#" onclick="navigateTo('Experiences')">Experiences</a></li>
                    <li><a href="#" onclick="navigateTo('Facilities')">Facilities</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Get in Touch</h3>
                <div class="contact-info">
                    <p><strong>Dheergayu Ayurveda Center</strong></p>
                    <p><strong><img src="img/email.jpg" alt="email" width="27px" height="17px"></strong> info@dheergayu.com</p>
                    <p><strong><img src="img/call.jpg" alt="call" width="19px" height="15px"></strong> +94 25 8858500</p>
                </div>
            </div>

            <div class="footer-section">
                <h3>Stay Connected with Ayurveda</h3>
                <p style="color: #666; margin-bottom: 20px;">Subscribe to our newsletter for wellness tips and updates</p>
                <div class="social-links">
                    <a href="#"  onclick="openSocial('Facebook')"><img src="img/fb.jpg" alt="fb" width="27px" height="19px"></a>
                    <a href="#"  onclick="openSocial('Twitter')"><img src="img/x.jpg" alt="x" width="27px" height="19px"></a>
                    <a href="#"  onclick="openSocial('Youtube')"><img src="img/youtube.jpg" alt="youtube" width="27px" height="19px"></a>
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

        <script>

            
let slideIndex = 0;
showSlides();

function showSlides() {
  let i;
  let slides = document.getElementsByClassName("fade"); // Changed from "mySlides" to "fade"
  let dots = document.getElementsByClassName("dot");
  
  // Hide all slides first
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  
  slideIndex++;
  if (slideIndex > slides.length) {
    slideIndex = 1;
  }
  
  // Remove active class from all dots
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  
  // Show current slide and mark corresponding dot as active
  if (slides.length > 0) {
    slides[slideIndex-1].style.display = "block";  
  }
  if (dots.length > 0) {
    dots[slideIndex-1].className += " active";
  }
  
  setTimeout(showSlides, 3000); // Change image every 3 seconds
}

            function navigateTo(page) {
                alert(`Navigating to: ${page}`);
            }

            function openChat(platform) {
                alert(`Opening ${platform} chat...`);
            }

            function openSocial(platform) {
                alert(`Opening ${platform} page...`);
            }

            // Add smooth scroll effect
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Add floating animation to chat buttons
            const chatButtons = document.querySelectorAll('.chat-button');
            chatButtons.forEach((button, index) => {
                button.style.animationDelay = `${index * 0.1}s`;
            });

           
        </script>
</body>

</html>