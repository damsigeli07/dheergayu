<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learn More - Natural Herbal Remedies | Dheergayu</title>
    <link rel="stylesheet" href="css/learn_more.css?v=<?php echo time(); ?>">
</head>
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

    <section class="hero-banner">
        <div class="hero-content">
            <h1>Natural Herbal Remedies</h1>
            <p>Ancient Wisdom for Modern Healing - Discover the transformative power of nature's pharmacy</p>
        </div>
    </section>

    <div class="container">
        <section class="section">
            <div class="section-content">
                <div class="section-text">
                    <h2 class="section-title">Traditional Preparation Methods</h2>
                    <p class="section-description">
                        Our herbal remedies are prepared using time-honored techniques passed down through generations. Each ingredient is carefully selected, ground with traditional mortar and pestle, and combined with natural enhancers like honey and essential oils.
                    </p>
                    <ul class="benefits-list">
                        <li>Hand-selected organic herbs and spices</li>
                        <li>Traditional grinding techniques preserve potency</li>
                        <li>Natural preservation methods</li>
                        <li>No artificial additives or chemicals</li>
                        <li>Customized formulations for individual needs</li>
                    </ul>
                </div>
                <div class="section-image">
                    <img src="img/herbal-product.jpg">
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-content reverse">
                <div class="section-image">
                    <img src="img/ayurveda-home-6.jpg">
                </div>
                <div class="section-text">
                    <h2 class="section-title">Healing Tea Blends</h2>
                    <p class="section-description">
                        Experience the therapeutic benefits of our specially crafted herbal tea blends. Each blend is formulated to address specific health concerns while providing a soothing, aromatic experience that nourishes both body and mind.
                    </p>
                    <ul class="benefits-list">
                        <li>Digestive support and detoxification</li>
                        <li>Stress relief and mental clarity</li>
                        <li>Immune system strengthening</li>
                        <li>Sleep quality improvement</li>
                        <li>Anti-inflammatory properties</li>
                    </ul>                
                </div>
                
            </div>
        </section>

        <section class="section">
            <div class="section-content">
                <div class="section-text">
                    <h2 class="section-title">Pure Natural Ingredients</h2>
                    <p class="section-description">
                        We source only the finest quality herbs, roots, and botanicals from trusted suppliers who share our commitment to sustainability and purity. Every ingredient is tested for potency and free from harmful contaminants.
                    </p>
                    <ul class="benefits-list">
                        <li>Certified organic and wild-harvested herbs</li>
                        <li>Third-party testing for purity and potency</li>
                        <li>Sustainable sourcing practices</li>
                        <li>Fair trade partnerships with growers</li>
                        <li>Seasonal freshness guaranteed</li>
                    </ul>
                  
                </div>
                <div class="section-image">
                    <img src="img/ayurveda-home-5.jpg">
                </div>
            </div>
        </section>
    </div>

    <section class="wisdom-section">
        <div class="wisdom-content">
            <p class="wisdom-quote">
                "Nature itself is the best physician. In every walk with nature, one receives far more than they seek. The healing power of plants has been humanity's first medicine, and remains our most reliable ally in the journey toward wellness."
            </p>
            <p class="wisdom-author">— Ancient Ayurvedic Principle</p>
        </div>
    </section>

    <div class="container">
        <section style="text-align: center; padding: 3rem; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <h2 style="font-size: 2.5rem; color: #7c6e4f; margin-bottom: 1rem;">Begin Your Natural Healing Journey</h2>
            <p style="font-size: 1.2rem; color: #666; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Ready to experience the transformative power of natural herbal remedies? Our experienced practitioners are here to guide you toward optimal health and wellness.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="channeling.php" class="cta-button">Schedule Consultation</a>
                <a href="products.php" class="cta-button" style="background: transparent; color: #7c6e4f; border: 2px solid #7c6e4f;">Browse Products</a>
            </div>
        </section>
    </div>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Parallax effect for hero
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero-banner');
            if (hero) {
                hero.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
        });

        // Intersection Observer for animations
        const sections = document.querySelectorAll('.section');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        sections.forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(50px)';
            section.style.transition = 'all 0.6s ease';
            observer.observe(section);
        });
    </script>
</body>
</html>