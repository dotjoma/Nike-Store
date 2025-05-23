<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>404 - Page Not Found | Nike Store</title>
    <link rel="icon" type="image/x-icon" href="/finalprojectbuaron/assets/favicon.ico" />
    <link href="/finalprojectbuaron/css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .error-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);
        }

        .error-content {
            text-align: center;
            max-width: 800px;
            padding: 4rem;
            background: white;
            border-radius: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }

        .error-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #000 0%, #333 100%);
        }

        .error-code {
            font-size: 12rem;
            font-weight: 800;
            color: #000;
            line-height: 1;
            margin-bottom: 1rem;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(45deg, #000 0%, #333 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #000;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .error-message {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 3rem;
            line-height: 1.6;
        }

        .btn-home {
            background: #000;
            color: white;
            padding: 1.2rem 3rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn-home:hover {
            background: #333;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .btn-home i {
            font-size: 1.2rem;
        }

        .nike-logo {
            width: 80px;
            margin-bottom: 3rem;
            filter: brightness(0);
        }

        .error-image {
            width: 70px;
            max-width: 90vw;
            height: auto;
            margin: 2rem auto;
            display: block;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .navbar {
            background-color: #000 !important;
            padding: 1.2rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 800;
            letter-spacing: 2px;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #fff !important;
            transform: translateY(-1px);
        }

        .footer {
            background: #000;
            padding: 2rem 0;
            margin-top: auto;
        }

        .footer p {
            color: #fff;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .error-content {
                padding: 2rem;
                margin: 1rem;
            }

            .error-code {
                font-size: 8rem;
            }

            .error-title {
                font-size: 2rem;
            }

            .error-message {
                font-size: 1rem;
            }

            .error-image {
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="/finalprojectbuaron/index.php">NIKE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link" href="/finalprojectbuaron/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">About</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Error section-->
    <div class="error-container">
        <div class="error-content">
            <img src="/finalprojectbuaron/assets/nike-logo.png" alt="Nike Logo" class="nike-logo">
            <div class="error-code">404</div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-message">We couldn't find the page you're looking for. It might have been moved or doesn't exist.</p>
            <img src="/finalprojectbuaron/assets/404-shoe.png" alt="404 Illustration" class="error-image">
            <a href="/finalprojectbuaron/index.php" class="btn-home">
                <i class="bi bi-house-door"></i> Return to Home
            </a>
        </div>
    </div>

    <!-- Footer-->
    <footer class="footer">
        <div class="container">
            <p class="m-0 text-center">© 2024 Nike, Inc. All Rights Reserved</p>
        </div>
    </footer>

    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 