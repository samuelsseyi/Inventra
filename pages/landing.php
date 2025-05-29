<?php
// Landing page content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventra - Smart Inventory Management System</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #00d4d4, #004080);
            --secondary-gradient: linear-gradient(135deg, #004080, #001f40);
            --primary-color: #00d4d4;
            --secondary-color: #004080;
        }
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        .gradient-primary {
            background: var(--primary-gradient);
        }
        .gradient-secondary {
            background: var(--secondary-gradient);
        }
        .btn-custom-primary {
            background: var(--primary-gradient);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-custom-primary:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,212,212,0.4);
        }
        .btn-custom-outline {
            border: 2px solid white;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-custom-outline:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateY(-2px);
        }
        .navbar-custom {
            transition: all 0.3s ease;
            padding: 1rem 0;
        }
        .navbar-custom .navbar-toggler {
            border: none;
            padding: 0;
        }
        .navbar-custom .navbar-toggler:focus {
            box-shadow: none;
        }
        .navbar-custom .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            width: 24px;
            height: 24px;
        }
        .navbar-custom.scrolled {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0.5rem 0;
        }
        .navbar-custom.scrolled .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(0, 0, 0, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        .navbar-custom .nav-link {
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        .navbar-custom.scrolled .nav-link {
            color: var(--secondary-color);
            font-weight: 600;
        }
        .navbar-custom.scrolled .nav-link:hover {
    color: var(--primary-color);
}
@media (max-width: 991px) {
            .navbar-custom .navbar-collapse {
                background: var(--primary-gradient);
                margin: 0 -1rem;
                padding: 1rem;
                border-radius: 0 0 1rem 1rem;
            }
            .navbar-custom.scrolled .navbar-collapse {
                background: white;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .navbar-custom .nav-item {
                margin: 0.5rem 0;
            }
            .navbar-custom .btn {
                width: 100%;
                margin: 0.5rem 0;
            }
        }
        .navbar-custom .btn-custom-outline {
            border: 2px solid white;
            color: white;
            transition: all 0.3s ease;
        }
        .navbar-custom .btn-custom-outline:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateY(-2px);
        }
        .navbar-custom.scrolled .btn-custom-outline {
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
        }
        .navbar-custom.scrolled .btn-custom-outline:hover {
            background: rgba(0,64,128,0.1);
            color: var(--secondary-color);
        }
        .navbar-custom.scrolled .btn-custom-primary {
            background: var(--primary-gradient);
            color: white;
        }
        .hero-section {
            min-height: 100vh;
            padding-top: 5rem;
            position: relative;
            overflow: hidden;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='rgba(255,255,255,0.05)' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.5;
        }
        .stat-card {
            background: rgba(255,255,255,0.1);
            border-radius: 1rem;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.15);
        }
        .feature-card {
            transition: all 0.3s ease;
            border: none;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            transition: all 0.3s ease;
        }
        .feature-card:hover .feature-icon {
            transform: scale(1.1);
        }
        .pricing-card {
            height: 100%;
            transition: all 0.3s ease;
        }
        .pricing-card.popular {
            transform: scale(1.05);
            border: 2px solid var(--primary-color);
            z-index: 1;
        }
        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        .pricing-card.popular:hover {
            transform: scale(1.05) translateY(-5px);
        }
        @media (max-width: 768px) {
            .pricing-card.popular {
                transform: none;
            }
            .pricing-card.popular:hover {
                transform: translateY(-5px);
            }
        }
        .social-link {
            transition: all 0.3s ease;
            opacity: 0.8;
        }
        .social-link:hover {
    opacity: 1;
    transform: translateY(-3px);
}
.card.bg-transparent .card-body h4 {
    color: #00d4d4;
    font-weight: 600;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.card.bg-transparent .card-body p {
    color: rgba(255, 255, 255, 0.9);
}
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/logo-white.png" alt="Inventra Logo" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-custom-outline" href="auth/login.php">Login</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-custom-primary" href="auth/register.php">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-primary text-white hero-section d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Smart Inventory Management for Nigerian Businesses</h1>
                    <p class="lead mb-5">Take control of your inventory with our powerful, easy-to-use management system designed specifically for Nigerian businesses.</p>
                    <div class="d-flex gap-3 justify-content-center mb-5">
                        <a href="auth/register.php" class="btn btn-lg btn-custom-primary">Start Free Trial</a>
                        <a href="#features" class="btn btn-lg btn-custom-outline">Learn More</a>
                    </div>
                    <div class="row g-4 justify-content-center">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <h2 class="fw-bold">1000+</h2>
                                <p class="mb-0">Active Users</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <h2 class="fw-bold">₦10M+</h2>
                                <p class="mb-0">Stock Managed</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <h2 class="fw-bold">99.9%</h2>
                                <p class="mb-0">Uptime</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container py-5">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold mb-3">Why Choose Inventra?</h2>
                    <p class="lead text-muted">Everything you need to manage your inventory efficiently</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card card border-0 shadow-sm h-100 p-4 text-center">
                        <div class="card-body">
                            <div class="feature-icon mb-4">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <h3 class="h4 mb-3">Real-time Tracking</h3>
                            <p class="text-muted mb-0">Monitor your stock levels in real-time and get instant updates on inventory movements.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card border-0 shadow-sm h-100 p-4 text-center">
                        <div class="card-body">
                            <div class="feature-icon mb-4">
                                <i class="fas fa-mobile-alt fa-2x"></i>
                            </div>
                            <h3 class="h4 mb-3">Mobile Friendly</h3>
                            <p class="text-muted mb-0">Access your inventory data anywhere, anytime from any device.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card card border-0 shadow-sm h-100 p-4 text-center">
                        <div class="card-body">
                            <div class="feature-icon mb-4">
                                <i class="fas fa-chart-pie fa-2x"></i>
                            </div>
                            <h3 class="h4 mb-3">Smart Analytics</h3>
                            <p class="text-muted mb-0">Get insights into your business with detailed reports and analytics.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="gradient-secondary text-white py-5">
        <div class="container py-5">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold mb-3">How It Works</h2>
                    <p class="lead">Get started with Inventra in three simple steps</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card bg-transparent border-light text-center h-100">
                        <div class="card-body">
                            <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 60px; height: 60px;">
                                <h3 class="mb-0">1</h3>
                            </div>
                            <h4>Create Account</h4>
                            <p class="text-light mb-0">Sign up for free and set up your business profile</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-transparent border-light text-center h-100">
                        <div class="card-body">
                            <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 60px; height: 60px;">
                                <h3 class="mb-0">2</h3>
                            </div>
                            <h4>Add Products</h4>
                            <p class="text-light mb-0">Import your inventory or add products manually</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-transparent border-light text-center h-100">
                        <div class="card-body">
                            <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 60px; height: 60px;">
                                <h3 class="mb-0">3</h3>
                            </div>
                            <h4>Start Managing</h4>
                            <p class="text-light mb-0">Begin tracking your inventory in real-time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-5">
        <div class="container py-5">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold mb-3">Simple, Transparent Pricing</h2>
                    <p class="lead text-muted">Choose the plan that's right for your business</p>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="pricing-card card border-0 shadow-sm h-100">
                        <div class="card-body p-5">
                            <h3 class="text-center mb-4">Starter</h3>
                            <div class="text-center mb-4">
                                <span class="display-4 fw-bold">₦5,000</span>
                                <span class="text-muted">/month</span>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Up to 500 products</li>
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Basic analytics</li>
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Email support</li>
                            </ul>
                            <a href="auth/register.php" class="btn btn-custom-primary w-100">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-5">
                    <div class="pricing-card card border-0 shadow-sm h-100 popular">
                        <div class="card-body p-5">
                            <div class="position-absolute top-0 end-0 mt-3 me-3">
                                <span class="badge bg-primary">Most Popular</span>
                            </div>
                            <h3 class="text-center mb-4">Professional</h3>
                            <div class="text-center mb-4">
                                <span class="display-4 fw-bold">₦15,000</span>
                                <span class="text-muted">/month</span>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Unlimited products</li>
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Advanced analytics</li>
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Priority support</li>
                            </ul>
                            <a href="auth/register.php" class="btn btn-custom-primary w-100">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="gradient-secondary text-white py-5">
        <div class="container py-4">
            <div class="row g-4">
                <div class="col-lg-4">
                    <img src="assets/images/logo-white.png" alt="Inventra Logo" height="40" class="mb-4">
                    <p>Smart inventory management for Nigerian businesses.</p>
                </div>
                <div class="col-md-4 col-lg-2 offset-lg-2">
                    <h5 class="mb-4">Company</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#about" class="text-white text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="#contact" class="text-white text-decoration-none">Contact</a></li>
                        <li class="mb-2"><a href="#privacy" class="text-white text-decoration-none">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-4 col-lg-2">
                    <h5 class="mb-4">Follow Us</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white social-link"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white social-link"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white social-link"><i class="fab fa-instagram fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-5 bg-white">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Inventra. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navigation scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
                navbar.classList.remove('navbar-dark');
                navbar.classList.add('navbar-light');
            } else {
                navbar.classList.remove('scrolled');
                navbar.classList.add('navbar-dark');
                navbar.classList.remove('navbar-light');
            }
        });
    </script>
</body>
</html> 