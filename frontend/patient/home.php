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
            <button class="nav-btn active">Home</button>
            <a href="channeling.php" class="nav-btn">Consultations</a>
            <a href="treatment.php" class="nav-btn">Our Treatments</a>
            <a href="products.php" class="nav-btn">Our Products</a>
            <a href="Signup.php" class="nav-btn"><u>Book Now</u></a>
        </div>
    </header>
    <img src="img/ayurveda-home-2.jpg" alt="img" width="100%">

        <h1 class="main-title">Our Ayurvedic Treatments</h1>
        <div class="sub-title">🌿 Ayurveda Treatments – Heal Naturally, Live Fully</div>
        <center>
        <div class="paragrapgh">Are you dealing with a health issue and can’t seem to find its root cause?
        Have you tried conventional treatments, only to watch the symptoms return?
        Do you feel drained, run-down, or wish you could strengthen your immunity and feel vibrant again?</div>

        <div class="paragrapgh">Welcome to The Ayurvedic Clinic – where ancient wisdom meets modern care.</div>

        <div class="paragrapgh">For over 25 years, we’ve helped people restore balance, energy, and long-lasting wellness through the time-honoured science of Ayurveda. Our approach is holistic and personalised, caring for your mind, body, and spirit—because true health is about harmony, not just the absence of illness.</div>
    </center>
        </div>
        <div class="floating-chat">
            <div class="chat-button" onclick="openChat('WhatsApp')" title="WhatsApp">💬</div>
            <div class="chat-button facebook" onclick="openChat('Facebook')" title="Facebook">📘</div>
            <div class="chat-button twitter" onclick="openChat('Twitter')" title="Twitter">🐦</div>
            <div class="chat-button instagram" onclick="openChat('Instagram')" title="Instagram">📷</div>
            <div class="chat-button linkedin" onclick="openChat('LinkedIn')" title="LinkedIn">💼</div>
        </div>

        <footer class="footer">
            <div class="footer-container">
                <div class="footer-section">
                    <h3>DHEERGAYU MENU</h3>
                    <ul>
                        <li><a href="#" onclick="navigateTo('Home')">Home</a></li>
                        <li><a href="#" onclick="navigateTo('About us')">About us</a></li>
                        <li><a href="#" onclick="navigateTo('Treatments')">Treatments</a></li>
                        <li><a href="#" onclick="navigateTo('Contact Us')">Contact Us</a></li>
                        <li><a href="#" onclick="navigateTo('Products')">Products</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>CONTACT DETAILS</h3>
                    <div class="contact-info">
                        <p><strong>ADDRESS</strong></p>
                        <p>GENERAL NUMBERS</p>
                        <p>+94 25 8858500</p>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>FOLLOW US</h3>
                    <div class="social-links">
                        <a href="#" class="social-link facebook" onclick="openSocial('Facebook')">📘</a>
                        <a href="#" class="social-link twitter" onclick="openSocial('Twitter')">🐦</a>
                        <a href="#" class="social-link google" onclick="openSocial('Google')">🔍</a>
                        <a href="#" class="social-link whatsapp" onclick="openSocial('WhatsApp')">💬</a>
                        <a href="#" class="social-link linkedin" onclick="openSocial('LinkedIn')">💼</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                © 2024 DHEERGAYU • Traditional Ayurveda Healthcare<br>
                <a href="#" onclick="navigateTo('Privacy Policy')" style="color: #8B4513; text-decoration: none;">Privacy Policy</a> |
                <a href="#" onclick="navigateTo('Terms of Service')" style="color: #8B4513; text-decoration: none;">Terms of Service</a> |
                <a href="#" onclick="navigateTo('Site Map')" style="color: #8B4513; text-decoration: none;">Site Map</a>
            </div>
        </footer>

        <script>
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

            // Add CSS for floating animation
            const style = document.createElement('style');
            style.textContent = `
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-5px); }
            }
            
            .chat-button {
                animation: float 3s ease-in-out infinite;
            }
        `;
            document.head.appendChild(style);
        </script>
</body>

</html>