<?php
// Fetch active treatments from database
$db = new mysqli('localhost', 'root', '', 'dheergayu_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch only active treatments from treatment_list table
$query = "SELECT treatment_id, treatment_name, description, duration, price, image, status 
          FROM treatment_list 
          WHERE status = 'Active'
          ORDER BY treatment_id";
$result = $db->query($query);

$treatments = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $treatments[] = [
            'id' => $row['treatment_id'],
            'name' => $row['treatment_name'],
            'description' => $row['description'] ?? '',
            'duration' => $row['duration'] ?? '',
            'price' => number_format($row['price'], 2, '.', ','),
            'image' => $row['image'] ?? '/dheergayu/public/assets/images/Patient/health-treatments.jpg'
        ];
    }
}

// Function to generate tagline from treatment name
function generateTagline($treatmentName) {
    $taglines = [
        'Full Steam Treatment' => 'COMPLETE BODY STEAM THERAPY',
        'Shiro Dhara' => 'MIND RELAXATION THERAPY',
        'Head Treatment' => 'THERAPEUTIC HEAD CARE',
        'Eye Treatment' => 'VISION WELLNESS THERAPY',
        'Nasya Treatment' => 'NASAL THERAPY',
        'Fat Burn Treatment' => 'WEIGHT MANAGEMENT THERAPY',
        'Foot Treatment' => 'REFLEXOLOGY & CARE',
        'Facial Treatment' => 'NATURAL SKIN REJUVENATION'
    ];
    
    return $taglines[$treatmentName] ?? strtoupper(str_replace(' Treatment', ' THERAPY', $treatmentName));
}

$db->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dheergayu - Treatment Appointment</title>
    <link rel="stylesheet" href="/dheergayu/public/assets/css/Patient/treatment.css?v=<?php echo time(); ?>">
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
                    <li><a href="/dheergayu/app/Views/Patient/treatment.php" class="active">TREATMENTS</a></li>
                    <li><a href="/dheergayu/app/Views/Patient/products.php">SHOP</a></li>
                </ul>
            </nav>
            <div class="header-right">
                <a href="home.php" class="back-btn">← Back to Home</a>
            </div>
        </div>
    </header>

    <section class="treatments-hero">
        <div class="container">
            <p class="subtitle">Discover Wellness</p>
            <h2>OUR AYURVEDIC TREATMENTS</h2>
            <div class="decorative-line"></div>
            <p class="description">Experience the ancient healing wisdom of Ayurveda through our carefully curated treatments. Each therapy is designed to restore balance, promote natural healing, and enhance your overall well-being.</p>
        </div>
    </section>

    <section class="treatments-section">
    <div class="container">
        <div class="treatments-grid">
            <?php if (empty($treatments)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #666;">
                    <p>No treatments available at the moment. Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($treatments as $treatment): ?>
                    <div class="card-wrapper">
                        <img src="<?= htmlspecialchars($treatment['image']) ?>" 
                             alt="<?= htmlspecialchars($treatment['name']) ?>" 
                             class="card-image">
                        
                        <div class="floating-card">
                            <div class="text-content">
                                <div class="treatment-title"><?= htmlspecialchars($treatment['name']) ?></div>
                                <div class="treatment-tagline"><?= htmlspecialchars(generateTagline($treatment['name'])) ?></div>
                                <p class="treatment-description"><?= htmlspecialchars($treatment['description']) ?></p>
                                
                                <div class="treatment-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Duration:</span>
                                        <span class="detail-value"><?= htmlspecialchars($treatment['duration']) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Price:</span>
                                        <span class="detail-value price">Rs. <?= htmlspecialchars($treatment['price']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

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
                    <li><a href="#" class="footer-link">Treatments</a></li>
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
                    <li><a href="#" class="social-link">in LinkedIn</a></li>
                    <li><a href="#" class="social-link">📷 Instagram</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll to top
        document.querySelector('.scroll-to-top')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Book Now button handler
        document.querySelectorAll('.btn-book').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                alert('Please login to book this treatment');
                window.location.href = 'login.php';
            });
        });
    </script>
</body>

</html>