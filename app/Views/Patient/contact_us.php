<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /dheergayu/app/Views/Patient/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Dheergayu Ayurvedic Center</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/contact.css">
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
                    <li><a href="/dheergayu/app/Views/Patient/contact_us.php" class="active">CONTACT US</a></li>
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
                <p>123 Wellness Street</p>
                <p>Colombo, LK 00100</p>
                <p>Sri Lanka</p>
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
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/facebook.png" alt="Facebook" class="social-icon">
                            Facebook
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/twitter(x).png" alt="X" class="social-icon">
                            X
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/linkedin.png" alt="LinkedIn" class="social-icon">
                            LinkedIn
                        </a>
                    </li>
                    <li>
                        <a href="#" class="social-link">
                            <img src="/dheergayu/public/assets/images/Patient/instagram.png" alt="Instagram" class="social-icon">
                            Instagram
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>

<script src="/dheergayu/public/assets/js/patient-form-utils.js"></script>
<script>
    const contactForm = document.getElementById('contactForm');
    const successMessage = document.getElementById('successMessage');

    const FIELD_RULES = {
        name: {
            required: true,
            message: 'Full name is required.'
        },
        email: {
            required: true,
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address.'
        },
        phone: {
            required: true,
            pattern: /^0[0-9]{9}$/,
            message: 'Please enter a valid Sri Lankan phone number (e.g., 0712345678).'
        },
        subject: {
            required: true,
            message: 'Please select a subject.'
        },
        message: {
            required: true,
            message: 'Message is required.'
        }
    };

    function sanitizeFormInput(formData) {
        const phone = PatientFormUtils.toDigits(formData.get('phone'), 10);
        formData.set('phone', phone);
        return formData;
    }

    function validateForm(formData) {
        return PatientFormUtils.validateRules(formData, FIELD_RULES);
    }

    async function submitContactForm(formData) {
        const response = await fetch('/dheergayu/public/api/submit-contact.php', {
            method: 'POST',
            body: formData
        });
        return response.json();
    }

    contactForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        let formData = new FormData(contactForm);
        formData = sanitizeFormInput(formData);

        const validationError = validateForm(formData);
        if (validationError) {
            alert(validationError);
            return;
        }

        try {
            const data = await submitContactForm(formData);
            if (!data.success) {
                alert('Error: ' + (data.error || 'Failed to send message'));
                return;
            }

            successMessage.textContent = data.message || 'Your message has been sent successfully!';
            successMessage.classList.add('show');
            contactForm.reset();
            setTimeout(() => successMessage.classList.remove('show'), 5000);
            successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (error) {
            console.error(error);
            alert('An error occurred. Please try again later.');
        }
    });

    document.getElementById('phone').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
    });
</script>
</body>
</html>