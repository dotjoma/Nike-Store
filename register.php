<?php
    require_once("includes/connect.php");
    session_start();

    if(isset($_POST['register'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'user'; // Default role for new registrations
        $image = null;

        // Handle image upload
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB

            if(!in_array($_FILES['image']['type'], $allowed_types)) {
                $error = "Invalid file type. Only JPEG, PNG and GIF are allowed.";
            } elseif($_FILES['image']['size'] > $max_size) {
                $error = "File size too large. Maximum size is 2MB.";
            } else {
                $image = file_get_contents($_FILES['image']['tmp_name']);
            }
        }

        if(!isset($error)) {
            try {
                // Check if email already exists
                $check_query = "SELECT id FROM users WHERE email = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->execute([$email]);
                
                if($check_stmt->rowCount() > 0) {
                    $error = "Email already exists";
                } else {
                    $query = "INSERT INTO users (name, email, password, role, image) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$name, $email, $password, $role, $image]);
                    
                    $_SESSION['success'] = "Registration successful! Please login.";
                    header("Location: login.php");
                    exit();
                }
            } catch(PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Join Nike - Nike Store</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        .register-container {
            max-width: 500px;
            margin: 3rem auto;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.08);
            background: white;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.85rem 1.25rem;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #000;
            box-shadow: none;
        }
        .btn-register {
            border-radius: 8px;
            padding: 0.85rem 1.25rem;
            text-transform: uppercase;
            font-weight: 600;
            background-color: #000;
            color: #fff;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background-color: #333;
            color: #fff;
            transform: translateY(-1px);
        }
        .image-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 10px auto;
            display: block;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .image-upload {
            text-align: center;
            margin-bottom: 25px;
        }
        .image-upload label {
            cursor: pointer;
            padding: 8px 16px;
            background: #f8f9fa;
            border-radius: 8px;
            display: inline-block;
            margin-top: 10px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .image-upload label:hover {
            background: #e9ecef;
        }
        .image-upload input[type="file"] {
            display: none;
        }
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .alert {
            border-radius: 8px;
            padding: 1rem;
        }
        .navbar {
            background-color: #000 !important;
            padding: 1rem 0;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 1px;
        }
        .nav-link {
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        .register-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
            color: #000;
        }
        .login-link {
            color: #000;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link:hover {
            color: #333;
            text-decoration: underline;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .password-requirements {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.5rem;
        }
        .text-muted {
            color: #666 !important;
        }
    </style>
</head>
<body>
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="index.php">NIKE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#!">About</a></li>
                    <?php
                    // Check if user is logged in and is an admin
                    if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                        echo '<li class="nav-item"><a class="nav-link" href="admin/dashboard.php">Admin Dashboard</a></li>';
                    }
                    ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Register section-->
    <section class="py-5">
        <div class="container">
            <div class="register-container">
                <h1 class="register-title">BECOME A MEMBER</h1>
                <p class="text-center text-muted mb-4">Sign up for free. Join the community.</p>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="image-upload">
                        <img id="preview" src="assets/default-avatar.png" class="image-preview" alt="Profile Preview">
                        <label for="image" class="btn btn-outline-secondary">
                            <i class="bi bi-camera"></i> Add Photo
                        </label>
                        <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
                        <div class="password-requirements">
                            Password must be at least 8 characters long
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="register" class="btn btn-register">Join Us</button>
                    </div>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Already a member? <a href="login.php" class="login-link">Sign In</a></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer-->
    <footer class="py-4 bg-dark">
        <div class="container">
            <p class="m-0 text-center text-white">© 2024 Nike, Inc. All Rights Reserved</p>
        </div>
    </footer>

    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>