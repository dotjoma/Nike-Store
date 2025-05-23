<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>About Us - Nike Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="icon" type="image/x-icon" href="/finalprojectbuaron/assets/favicon.ico" />
    <link href="/finalprojectbuaron/css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        .about-container {
            max-width: 900px;
            margin: 3rem auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0,0,0,0.08);
            padding: 3rem 2rem;
        }
        .about-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .about-logo {
            display: block;
            margin: 0 auto 2rem auto;
            width: 100px;
        }
        .about-text {
            font-size: 1.15rem;
            color: #333;
            line-height: 1.7;
            margin-bottom: 2rem;
            text-align: center;
        }
        .about-values {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .value-card {
            background: #f8f9fa;
            border-radius: 14px;
            padding: 2rem 1.5rem;
            flex: 1 1 220px;
            min-width: 220px;
            max-width: 300px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        .value-icon {
            font-size: 2.5rem;
            color: #000;
            margin-bottom: 1rem;
        }
        .value-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #000;
            margin-bottom: 0.5rem;
        }
        .value-desc {
            color: #555;
            font-size: 1rem;
        }
        .about-footer {
            text-align: center;
            margin-top: 2rem;
            color: #888;
            font-size: 0.95rem;
        }
        @media (max-width: 768px) {
            .about-container { padding: 2rem 0.5rem; }
            .about-title { font-size: 2rem; }
            .about-values { flex-direction: column; gap: 1rem; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #000;">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="/finalprojectbuaron/index.php">NIKE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link" href="/finalprojectbuaron/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/finalprojectbuaron/about.php">About</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="about-container">
        <img src="/finalprojectbuaron/assets/nike-logo.png" alt="Nike Logo" class="about-logo">
        <div class="about-title">About Our Store</div>
        <div class="about-text">
            Welcome to our Store!<br>
            This project is a tribute to the innovation, style, and performance that Nike represents. Our platform is designed for sneaker enthusiasts and athletes alike, offering a seamless and modern shopping experience inspired by the real Nike online store.<br><br>
            <b>Note:</b> This website is a student project and not an official Nike website. All product images, names, and branding are for educational purposes only.
        </div>
        <div class="about-values">
            <div class="value-card">
                <div class="value-icon"><i class="bi bi-lightning-charge"></i></div>
                <div class="value-title">Innovation</div>
                <div class="value-desc">We strive to bring the latest features and a smooth user experience, just like Nike leads in sports innovation.</div>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="bi bi-bag-check"></i></div>
                <div class="value-title">Quality</div>
                <div class="value-desc">Our code and design aim for the highest quality, reflecting the standards of the Nike brand.</div>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="bi bi-people"></i></div>
                <div class="value-title">Community</div>
                <div class="value-desc">We believe in the power of community, just as Nike empowers athletes and fans worldwide.</div>
            </div>
        </div>
        <div class="about-footer">
            For educational purposes only.<br>
        </div>
    </div>

    <footer class="py-4 bg-dark">
        <div class="container">
            <p class="m-0 text-center text-white">© 2025 Buaron Store. All Rights Reserved</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>