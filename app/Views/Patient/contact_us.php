<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Dheergayu Ayurvedic Center</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f7f5f2;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .main-header {
            background: linear-gradient(135deg, #2d2d2d 0%, #6f4403 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .main-header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .main-header .logo {
            display: flex;
            align-items: center;
        }

        .main-header .logo img {
            height: 57px;
            margin-right: 15px;
        }

        .main-header .logo h1 {
            font-size: 32px;
            margin: 0;
            line-height: 1.1;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Times New Roman', Times, serif;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .main-header .logo h1 span {
            font-size: 14px;
            font-family: Arial, sans-serif;
            letter-spacing: 2px;
            opacity: 0.8;
            margin-top: 5px;
        }

        /* Navigation */
        .main-nav ul {
            display: flex;
        }

        .main-nav li {
            margin-left: 30px;
        }

        .main-nav a {
            color: white;
            font-weight: bold;
            font-size: 15px;
            padding: 10px 0;
            transition: color 0.3s ease;
        }

        .main-nav a:hover,
        .main-nav a.active {
            color: #e6a646;
        }

        .header-right .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .header-right .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Contact Section */
        .contact-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 100px 20px 60px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .page-title {
            color: #8B7355;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-subtitle {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Contact Grid */
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        /* Contact Info Cards */
        .contact-info {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .info-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .info-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #8B7355, #A0916B);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }

        .info-card h3 {
            color: #8B7355;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .info-card p {
            color: #555;
            line-height: 1.8;
            margin: 5px 0;
        }

        .info-card a {
            color: #8B7355;
            transition: color 0.3s ease;
        }

        .info-card a:hover {
            color: #A0916B;
            text-decoration: underline;
        }

        /* Contact Form */
        .contact-form-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .contact-form-container h2 {
            color: #8B7355;
            font-size: 1.8rem;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Roboto', sans-serif;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #8B7355;
            box-shadow: 0 0 0 4px rgba(139, 115, 85, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #8B7355, #A0916B);
            color: white;
            padding: 16px 40px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(139, 115, 85, 0.3);
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #A0916B, #8B7355);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 115, 85, 0.4);
        }

        /* Map Section */
        .map-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .map-section h2 {
            color: #8B7355;
            font-size: 1.8rem;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }

        .map-container {
            width: 100%;
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e1e5e9;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Social Links */
        .social-section {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .social-section h2 {
            color: #8B7355;
            font-size: 1.8rem;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #8B7355, #A0916B);
            color: white;
            border-radius: 50%;
            font-size: 24px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(139, 115, 85, 0.3);
        }

        .social-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(139, 115, 85, 0.4);
        }

        /* Footer */
        .main-footer {
            background: linear-gradient(135deg, #2d2d2d 0%, #5e3800 100%);
            color: white;
            padding-top: 60px;
            margin-top: 60px;
        }

        .main-footer .container {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            padding-bottom: 40px;
            border-bottom: 1px solid #444;
        }

        .footer-column {
            flex: 1;
            min-width: 200px;
        }

        .footer-column h3 {
            font-size: 20px;
            text-transform: uppercase;
            margin: 0 0 25px 0;
            border-bottom: 2px solid #557945;
            padding-bottom: 5px;
            display: inline-block;
        }

        .footer-column p {
            margin: 5px 0;
            color: #ccc;
            font-size: 15px;
        }

        .footer-link {
            color: #ccc;
            transition: color 0.3s;
            display: block;
            padding: 5px 0;
        }

        .footer-link:hover {
            color: #557945;
        }

        /* Success Message */
        .success-message {
            display: none;
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .success-message.show {
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }

            .main-header .logo h1 {
                font-size: 24px;
            }

            .main-nav {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .contact-wrapper {
                padding: 80px 15px 40px;
            }

            .page-title {
                font-size: 2rem;
            }

            .contact-form-container {
                padding: 25px;
            }

            .map-container {
                height: 300px;
            }

            .main-footer .container {
                flex-wrap: wrap;
            }

            .footer-column {
                flex: 1 1 45%;
                margin-bottom: 30px;
            }

            .main-header .logo h1 {
                font-size: 20px;
            }

            .main-header .logo img {
                height: 45px;
            }

            .header-right .back-btn {
                padding: 8px 20px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .main-header .logo h1 {
                font-size: 18px;
            }

            .main-header .logo img {
                height: 40px;
            }

            .page-title {
                font-size: 1.6rem;
            }

            .footer-column {
                flex: 1 1 100%;
            }

            .social-links {
                gap: 15px;
            }

            .social-link {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }
    </style>
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
                    <li><a href="/dheergayu/app/Views/Patient/channeling.php">BOOKING</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/treatment.php">TREATMENTS</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/products.php">SHOP</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <a href="/dheergayu/app/Views/Patient/home.php" class="back-btn">← Back to Home</a>
            </div>
        </div>
    </header>

    <!-- Contact Content -->
    <div class="contact-wrapper">
        <div class="page-header">
            <h1 class="page-title">Contact Us</h1>
            <p class="page-subtitle">We'd love to hear from you. Reach out to us for any queries or appointments.</p>
        </div>

        <div class="contact-grid">
            <!-- Contact Information -->
            <div class="contact-info">
                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon">📍</div>
                        <h3>Visit Us</h3>
                    </div>
                    <p>123 Wellness Street</p>
                    <p>Colombo, LK 00100</p>
                    <p>Sri Lanka</p>
                </div>

                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon">📞</div>
                        <h3>Call Us</h3>
                    </div>
                    <p><a href="tel:+94112345678">+94 11 234 5678</a></p>
                    <p><a href="tel:+94771234567">+94 77 123 4567</a></p>
                    <p>Mon - Sat: 8:00 AM - 6:00 PM</p>
                </div>

                <div class="info-card">
                    <div class="info-card-header">
                        <div class="info-icon">✉️</div>
                        <h3>Email Us</h3>
                    </div>
                    <p><a href="mailto:info@dheergayu.com">info@dheergayu.com</a></p>
                    <p><a href="mailto:appointments@dheergayu.com">appointments@dheergayu.com</a></p>
                    <p>We'll respond within 24 hours</p>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-container">
                <h2>Send us a Message</h2>
                <div id="successMessage" class="success-message">
                    Thank you for contacting us! We'll get back to you soon.
                </div>
                <form id="contactForm">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" placeholder="07XXXXXXXX" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <select id="subject" name="subject" required>
                            <option value="">-- Select Subject --</option>
                            <option value="appointment">Appointment Inquiry</option>
                            <option value="treatment">Treatment Information</option>
                            <option value="general">General Question</option>
                            <option value="feedback">Feedback</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
        </div>

        <!-- Map Section -->
        <div class="map-section">
            <h2>Find Us on the Map</h2>
            <div class="map-container">
                <!-- Replace with your actual Google Maps embed code -->
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.798467384463!2d79.86119631477242!3d6.927078995009707!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae259692f4b8f65%3A0x8d2e1b8b8b8b8b8b!2sColombo%2C%20Sri%20Lanka!5e0!3m2!1sen!2slk!4v1234567890"
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>

        <!-- Social Media Section -->
        <div class="social-section">
            <h2>Connect With Us</h2>
            <div class="social-links">
                <a href="#" class="social-link" title="Facebook">📘</a>
                <a href="#" class="social-link" title="Instagram">📷</a>
                <a href="#" class="social-link" title="Twitter">🐦</a>
                <a href="#" class="social-link" title="LinkedIn">💼</a>
                <a href="#" class="social-link" title="WhatsApp">💬</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-column">
                <h3>HELLO</h3>
                <p>Welcome to one of the best Ayurvedic wellness centers in your area!</p>
            </div>
            <div class="footer-column">
                <h3>OFFICE</h3>
                <p>Sri Lanka –</p>
                <p>123 Wellness Street</p>
                <p>Colombo, LK 00100</p>
                <p><a href="mailto:info@dheergayu.com" class="footer-link">info@dheergayu.com</a></p>
                <p>+94 11 234 5678</p>
            </div>
            <div class="footer-column">
                <h3>LINKS</h3>
                <ul>
                    <li><a href="/dheergayu/app/Views/Patient/home.php" class="footer-link">Home</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/treatment.php" class="footer-link">Treatments</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/learn_more.php" class="footer-link">About Us</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/channeling.php" class="footer-link">Booking</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/contact_us.php" class="footer-link">Contacts</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>GET IN TOUCH</h3>
                <ul>
                    <li><a href="#" class="footer-link">Facebook</a></li>
                    <li><a href="#" class="footer-link">X</a></li>
                    <li><a href="#" class="footer-link">LinkedIn</a></li>
                    <li><a href="#" class="footer-link">Instagram</a></li>
                </ul>
            </div>
        </div>
    </footer>

<script>
    // Form submission handler
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const phone = formData.get('phone');
        const phoneRegex = /^0[0-9]{9}$/;
        
        if (!phoneRegex.test(phone)) {
            alert('Please enter a valid Sri Lankan phone number (e.g., 0712345678)');
            return;
        }
        
        // Submit to backend
        fetch('/dheergayu/public/api/submit-contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const successMessage = document.getElementById('successMessage');
                successMessage.textContent = data.message;
                successMessage.classList.add('show');
                this.reset();
                setTimeout(() => successMessage.classList.remove('show'), 5000);
                successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                alert('Error: ' + (data.error || 'Failed to send message'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again later.');
        });
    });

    document.getElementById('phone').addEventListener('input', function(e) {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });
</script>
</body>
</html>