<?php
require_once 'includes/session.php';
require_once 'includes/functions.php';
require_once 'config/db.php';

$error = '';
$shortened_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_url = sanitizeInput($_POST['url'] ?? '');

    if (empty($original_url)) {
        $error = 'Please enter a URL to shorten';
    } elseif (!filter_var($original_url, FILTER_VALIDATE_URL)) {
        $error = 'Please enter a valid URL';
    } else {
        $user_id = getUserId();
        
        // Generate a unique short code
        $short_code = generateShortCode();
        
        // Make sure the short code is unique
        $stmt = $pdo->prepare("SELECT id FROM links WHERE short_code = ?");
        $stmt->execute([$short_code]);
        
        while ($stmt->rowCount() > 0) {
            $short_code = generateShortCode();
            $stmt->execute([$short_code]);
        }
        
        $stmt = $pdo->prepare("INSERT INTO links (user_id, original_url, short_code) VALUES (?, ?, ?)");
        
        if ($stmt->execute([$user_id, $original_url, $short_code])) {
            $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
            // $shortened_url = $site_url . "/shorten/redirect.php?code=" . $short_code;
            $shortened_url = $site_url . "/UrlShortenerApp/" . $short_code;
        } else {
            $error = 'An error occurred while shortening the URL. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #2c3e50;
            --light: #ecf0f1;
            --success: #2ecc71;
            --danger: #e74c3c;
            --gray: #95a5a6;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--secondary);
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header styles */
        header {
            background-color: var(--primary);
            color: var(--white);
            padding: 20px 0;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: 600;
        }

        header h1 i {
            margin-right: 10px;
            font-size: 28px;
        }

        nav {
            display: flex;
            gap: 15px;
        }

        nav a {
            color: var(--white);
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }

        nav a i {
            margin-right: 6px;
        }

        nav a:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        /* Main content styles */
        main {
            padding: 60px 0;
        }

        .hero-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .hero-section h2 {
            font-size: 36px;
            margin-bottom: 20px;
            animation: fadeInDown 1s;
        }

        .hero-section p {
            font-size: 18px;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto 30px;
            animation: fadeIn 1.5s;
        }

        /* URL form styles */
        .url-form {
            background-color: var(--white);
            border-radius: 10px;
            padding: 40px;
            box-shadow: var(--shadow);
            margin-bottom: 60px;
            animation: fadeInUp 1s;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .url-form h2 {
            margin-bottom: 30px;
            text-align: center;
            color: var(--secondary);
            position: relative;
        }

        .url-form h2:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: var(--primary);
            border-radius: 3px;
        }

        .error {
            background-color: #ffecee;
            color: var(--danger);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: shake 0.5s;
        }

        .form-group {
            display: flex;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-radius: 6px;
            overflow: hidden;
        }

        .form-group input {
            flex: 1;
            padding: 16px 20px;
            border: none;
            font-size: 16px;
            outline: none;
        }

        .form-group button {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 0 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .form-group button:hover {
            background-color: var(--primary-dark);
        }

        /* Result section styles */
        .result {
            margin-top: 30px;
            padding: 25px;
            background-color: #f0f9ff;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
            animation: bounceIn 0.8s;
        }

        .result h3 {
            color: var(--secondary);
            margin-bottom: 15px;
            font-size: 18px;
        }

        .shortened-url {
            display: flex;
            margin-bottom: 20px;
        }

        .shortened-url input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            font-size: 15px;
            color: var(--primary);
            background-color: var(--white);
        }

        .shortened-url button {
            background-color: var(--success);
            color: var(--white);
            border: none;
            padding: 0 20px;
            border-radius: 0 4px 4px 0;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .shortened-url button:hover {
            background-color: #27ae60;
        }

        /* Register prompt styles */
        .register-prompt {
            background-color: #fff8e1;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #f1c40f;
            animation: fadeIn 1s;
        }

        .register-prompt p {
            margin-bottom: 15px;
            font-size: 15px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary);
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Features section styles */
        .features {
            padding: 40px 0;
        }

        .features h2 {
            text-align: center;
            margin-bottom: 50px;
            font-size: 28px;
            position: relative;
            animation: fadeIn 1s;
        }

        .features h2:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 3px;
            background-color: var(--primary);
            border-radius: 3px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .feature {
            background-color: var(--white);
            border-radius: 8px;
            padding: 30px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .feature:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .feature:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: var(--primary);
            transform: scaleY(0);
            transition: transform 0.3s ease;
            transform-origin: bottom;
        }

        .feature:hover:before {
            transform: scaleY(1);
        }

        .feature i {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .feature:hover i {
            transform: scale(1.1);
        }

        .feature h3 {
            margin-bottom: 15px;
            font-size: 20px;
        }

        .feature p {
            color: var(--gray);
            line-height: 1.6;
        }

        /* Stats section */
        .stats-section {
            background-color: var(--primary);
            color: var(--white);
            padding: 60px 0;
            margin: 60px 0;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .stat-item {
            padding: 30px;
        }

        .stat-number {
            font-size: 46px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* How it works section */
        .how-it-works {
            padding: 60px 0;
        }

        .how-it-works h2 {
            text-align: center;
            margin-bottom: 50px;
            font-size: 28px;
            position: relative;
        }

        .how-it-works h2:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 3px;
            background-color: var(--primary);
            border-radius: 3px;
        }

        .steps {
            display: flex;
            justify-content: space-between;
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }

        .steps:before {
            content: '';
            position: absolute;
            top: 40px;
            left: 60px;
            right: 60px;
            height: 3px;
            background-color: #ddd;
            z-index: 0;
        }

        .step {
            text-align: center;
            position: relative;
            z-index: 1;
            width: 120px;
        }

        .step-number {
            width: 80px;
            height: 80px;
            background-color: var(--primary);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            margin: 0 auto 20px;
            position: relative;
        }

        .step p {
            font-size: 14px;
            color: var(--secondary);
        }

        /* Footer styles */
        footer {
            background-color: var(--secondary);
            color: var(--light);
            padding: 30px 0;
            text-align: center;
        }

        footer p {
            font-size: 14px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.8);
            }
            70% {
                transform: scale(1.05);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            20% { transform: translateX(-10px); }
            40% { transform: translateX(10px); }
            60% { transform: translateX(-10px); }
            80% { transform: translateX(10px); }
            100% { transform: translateX(0); }
        }

        /* Animation classes */
        .animate-fadeIn { animation: fadeIn 1s; }
        .animate-fadeInUp { animation: fadeInUp 1s; }
        .animate-fadeInDown { animation: fadeInDown 1s; }
        .animate-bounceIn { animation: bounceIn 0.8s; }

        /* Animation delays */
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }

        /* Responsive styles */
        @media (max-width: 768px) {
            .feature-grid {
                grid-template-columns: 1fr;
            }
            
            .steps {
                flex-direction: column;
                align-items: center;
                gap: 40px;
            }
            
            .steps:before {
                left: 50%;
                top: 40px;
                bottom: 40px;
                width: 3px;
                height: auto;
                transform: translateX(-50%);
            }
            
            .url-form {
                padding: 25px;
            }
            
            .form-group {
                flex-direction: column;
            }
            
            .form-group input {
                border-bottom: 1px solid #eee;
            }
            
            .form-group button {
                padding: 15px;
            }
            
            .shortened-url {
                flex-direction: column;
            }
            
            .shortened-url input {
                border-radius: 4px 4px 0 0;
            }
            
            .shortened-url button {
                border-radius: 0 0 4px 4px;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><i class="fas fa-link"></i> URL Shortener</h1>
            <nav>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard/index.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                    <a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="auth/register.php"><i class="fas fa-user-plus"></i> Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="hero-section">
                <h2 class="animate-fadeInDown">Simplify Your Links</h2>
                <p class="animate-fadeIn delay-200">Transform long, unwieldy URLs into clean, memorable, and trackable short links in seconds.</p>
            </section>

            <section class="url-form animate-fadeInUp delay-300">
                <h2>Shorten Your URL</h2>
                <?php if ($error): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="" method="post">
                    <div class="form-group">
                        <input type="url" name="url" id="url" placeholder="Enter your long URL here" required>
                        <button type="submit"><i class="fas fa-bolt"></i> Shorten</button>
                    </div>
                </form>

                <?php if ($shortened_url): ?>
                    <div class="result">
                        <h3>Your Shortened URL:</h3>
                        <div class="shortened-url">
                            <input type="text" id="shortened-url" value="<?php echo $shortened_url; ?>" readonly>
                            <button onclick="copyToClipboard()"><i class="fas fa-copy"></i> Copy</button>
                        </div>
                        <?php if (!isLoggedIn()): ?>
                            <div class="register-prompt">
                                <p><i class="fas fa-info-circle"></i> Create an account to track analytics and manage your shortened URLs!</p>
                                <a href="auth/register.php" class="btn"><i class="fas fa-user-plus"></i> Register Now</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="features">
                <h2>Why Use Our URL Shortener?</h2>
                <div class="feature-grid">
                    <div class="feature animate-fadeInUp delay-100">
                        <i class="fas fa-bolt"></i>
                        <h3>Easy to Use</h3>
                        <p>Shorten your URLs with just one click, no registration required.</p>
                    </div>
                    <div class="feature animate-fadeInUp delay-200">
                        <i class="fas fa-chart-line"></i>
                        <h3>Track Your Links</h3>
                        <p>Create an account to access detailed analytics about your shortened URLs.</p>
                    </div>
                    <div class="feature animate-fadeInUp delay-300">
                        <i class="fas fa-history"></i>
                        <h3>Manage Your History</h3>
                        <p>View and delete your previously created links anytime.</p>
                    </div>
                    <div class="feature animate-fadeInUp delay-400">
                        <i class="fas fa-tachometer-alt"></i>
                        <h3>Fast Redirection</h3>
                        <p>Our system ensures quick redirection to your original URLs.</p>
                    </div>
                </div>
            </section>

            <!-- Features Highlight Section -->
<section class="features-highlight">
    <div class="container">
        <div class="section-header animate-fadeIn">
            <h2>Powerful Features to Supercharge Your Links</h2>
            <p>Everything you need to create, manage, and analyze your shortened URLs</p>
        </div>
        
        <div class="features-grid">
            <!-- Feature Card 1 -->
            <div class="feature-card animate-fadeIn delay-100">
                <div class="feature-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="feature-content">
                    <h3>Instant Shortening</h3>
                    <p>Create short, memorable links in seconds with our lightning-fast URL shortener.</p>
                </div>
            </div>
            
            <!-- Feature Card 2 -->
            <div class="feature-card animate-fadeIn delay-200">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="feature-content">
                    <h3>Detailed Analytics</h3>
                    <p>Track clicks, geographic locations, devices, and more with our comprehensive dashboard.</p>
                </div>
            </div>
            
            <!-- Feature Card 3 -->
            <div class="feature-card animate-fadeIn delay-300">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="feature-content">
                    <h3>Secure & Reliable</h3>
                    <p>Enterprise-grade security and 99.9% uptime ensure your links work when you need them.</p>
                </div>
            </div>
            
            <!-- Feature Card 4 -->
            <div class="feature-card animate-fadeIn delay-400">
                <div class="feature-icon">
                    <i class="fas fa-magic"></i>
                </div>
                <div class="feature-content">
                    <h3>Custom Domains</h3>
                    <p>Use your own branded domain for shortened links to increase trust and recognition.</p>
                </div>
            </div>
            
            <!-- Feature Card 6 (now Card 5) -->
            <div class="feature-card animate-fadeIn delay-500">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="feature-content">
                    <h3>Mobile Optimized</h3>
                    <p>Create and manage links on the go with our fully responsive mobile experience.</p>
                </div>
            </div>
        </div>
        

    </div>
</section>

<style>
/* Features Highlight Section Styling */
.features-highlight {
    padding: 80px 0;
    background: #e6f7ff;
}

.section-header {
    text-align: center;
    margin-bottom: 50px;
}

.section-header h2 {
    font-size: 2.5rem;
    color: #2d3748;
    margin-bottom: 15px;
    font-weight: 700;
}

.section-header p {
    font-size: 1.1rem;
    color: #4a5568;
    max-width: 700px;
    margin: 0 auto;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 50px;
}

.feature-card {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.feature-icon {
    font-size: 2.5rem;
    color: #3182ce;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.feature-card:hover .feature-icon {
    transform: scale(1.1);
}

.feature-content h3 {
    font-size: 1.3rem;
    color: #2d3748;
    margin-bottom: 10px;
    font-weight: 600;
}

.feature-content p {
    color: #4a5568;
    line-height: 1.6;
}

.cta-container {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
}

.btn {
    padding: 12px 25px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 30px;
    text-decoration: none;
    transition: all 0.3s ease;
    text-align: center;
}

.btn-primary {
    background-color: #3182ce;
    color: white;
    box-shadow: 0 4px 6px rgba(49, 130, 206, 0.25);
}

.btn-primary:hover {
    background-color: #2c5282;
    transform: translateY(-2px);
    box-shadow: 0 7px 14px rgba(49, 130, 206, 0.25);
}

.btn-secondary {
    background-color: white;
    color: #3182ce;
    border: 2px solid #3182ce;
}

.btn-secondary:hover {
    background-color: #ebf8ff;
    transform: translateY(-2px);
}

/* Animation classes */
.animate-fadeIn {
    animation: fadeIn 0.8s ease forwards;
    opacity: 0;
}

.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
.delay-400 { animation-delay: 0.4s; }
.delay-500 { animation-delay: 0.5s; }
.delay-600 { animation-delay: 0.6s; }
.delay-700 { animation-delay: 0.7s; }

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header h2 {
        font-size: 2rem;
    }
    
    .cta-container {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>

            <section class="how-it-works">
                <h2>How It Works</h2>
                <div class="steps">
                    <div class="step animate-fadeInUp delay-100">
                        <div class="step-number">1</div>
                        <p>Paste your long URL</p>
                    </div>
                    <div class="step animate-fadeInUp delay-200">
                        <div class="step-number">2</div>
                        <p>Click "Shorten"</p>
                    </div>
                    <div class="step animate-fadeInUp delay-300">
                        <div class="step-number">3</div>
                        <p>Copy your short link</p>
                    </div>
                    <div class="step animate-fadeInUp delay-400">
                        <div class="step-number">4</div>
                        <p>Track performance</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> URL Shortener. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Animation for elements as they scroll into view
        document.addEventListener('DOMContentLoaded', function() {
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.feature, .step, .stat-item');
                
                elements.forEach(element => {
                    const position = element.getBoundingClientRect();
                    
                    // If element is in viewport
                    if(position.top < window.innerHeight && position.bottom >= 0) {
                        if (!element.classList.contains('animated')) {
                            element.classList.add('animated');
                            element.style.opacity = '1';
                            element.style.transform = 'translateY(0)';
                        }
                    }
                });
            };
            
            // Initial setup for animation
            const elements = document.querySelectorAll('.feature, .step, .stat-item');
            elements.forEach(element => {
                if (!element.classList.contains('animated')) {
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(20px)';
                    element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                }
            });
            
            // Run on load
            animateOnScroll();
            
            // Run on scroll
            window.addEventListener('scroll', animateOnScroll);
            
            // Animated counter function
            function animateCounter(element, target, duration = 2000) {
                let start = 0;
                const increment = target > 1000 ? 1000 : 1;
                const stepTime = Math.abs(Math.floor(duration / (target / increment)));
                
                const timer = setInterval(() => {
                    start += increment;
                    element.textContent = start > target ? target : start.toLocaleString() + '+';
                    
                    if (start >= target) {
                        clearInterval(timer);
                    }
                }, stepTime);
            }
            
            // Initialize counters once in viewport
            const statsSection = document.querySelector('.stats-section');
            let countersInitialized = false;
            
            function checkStatsVisible() {
                if (!countersInitialized) {
                    const position = statsSection.getBoundingClientRect();
                    
                    if (position.top < window.innerHeight && position.bottom >= 0) {
                        countersInitialized = true;
                        
                        animateCounter(document.getElementById('stats-links'), 10000000);
                        animateCounter(document.getElementById('stats-users'), 500000);
                        animateCounter(document.getElementById('stats-clicks'), 50000000);
                    }
                }
            }
            
            window.addEventListener('scroll', checkStatsVisible);
            checkStatsVisible(); // Check on load
        });

        // Copy to clipboard function
        function copyToClipboard() {
            const shortenedUrl = document.getElementById('shortened-url');
            shortenedUrl.select();
            document.execCommand('copy');
            
            // Change button text temporarily
            const copyButton = shortenedUrl.nextElementSibling;
            const originalHTML = copyButton.innerHTML;
            
            copyButton.innerHTML = '<i class="fas fa-check"></i> Copied!';
            copyButton.style.backgroundColor = '#27ae60';
            
            setTimeout(() => {
                copyButton.innerHTML = originalHTML;
                copyButton.style.backgroundColor = '';
            }, 2000);
        }
    </script>
</body>
</html>