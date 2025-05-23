<?php
    require_once("includes/connect.php");
    session_start();

    // Check if user is logged in
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Handle profile update
    if(isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $image = null;

        try {
            // Get current user data
            $query = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            // Verify current password if changing password
            if(!empty($new_password)) {
                if(!password_verify($current_password, $user['password'])) {
                    $error = "Current password is incorrect";
                } else {
                    $password = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }

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
                // Check if email is already taken by another user
                if($email !== $user['email']) {
                    $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
                    $check_stmt = $conn->prepare($check_query);
                    $check_stmt->execute([$email, $_SESSION['user_id']]);
                    
                    if($check_stmt->rowCount() > 0) {
                        $error = "Email already exists";
                    }
                }

                if(!isset($error)) {
                    // Update user profile
                    if(!empty($new_password)) {
                        $query = "UPDATE users SET name = ?, email = ?, password = ?, image = ? WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->execute([$name, $email, $password, $image, $_SESSION['user_id']]);
                    } else {
                        $query = "UPDATE users SET name = ?, email = ?, image = ? WHERE id = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->execute([$name, $email, $image, $_SESSION['user_id']]);
                    }

                    // Update session variables
                    $_SESSION['user_name'] = $name;
                    if($image) {
                        $_SESSION['user_image'] = $image;
                    }

                    $success = "Profile updated successfully";
                }
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }

    // Get current user data
    try {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Profile - Nike Store</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0;
            background: white;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .profile-header {
            background: #000;
            color: white;
            padding: 2rem;
            position: relative;
        }

        .profile-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .profile-content {
            padding: 2.5rem;
        }

        .profile-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: -75px auto 2rem;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .image-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #000;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .image-upload:hover {
            transform: scale(1.1);
        }

        .image-upload i {
            color: white;
            font-size: 1.2rem;
        }

        .image-upload input[type="file"] {
            display: none;
        }

        .form-control {
            border-radius: 8px;
            padding: 1rem 1.25rem;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #000;
            box-shadow: none;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-update {
            background: #000;
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-update:hover {
            background: #333;
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 2px solid #000;
            color: #000;
            background: transparent;
            padding: 1rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: #000;
            color: white;
        }

        .quick-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 1rem;
        }

        .quick-nav .btn {
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .alert-danger {
            background: #ffebee;
            color: #c62828;
        }

        .form-text {
            color: #666;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .breadcrumb {
            background: transparent;
            padding: 1rem 0;
        }

        .breadcrumb-item a {
            color: #666;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-item.active {
            color: #000;
            font-weight: 600;
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
                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="admin/dashboard.php">Admin Dashboard</a></li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile section-->
    <section class="py-5">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </nav>

            <div class="profile-container">
                <div class="profile-header">
                    <h1>Profile Management</h1>
                </div>

                <div class="profile-content">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="profile-image-container">
                            <img id="preview" src="<?php echo isset($user['image']) ? 'data:image/jpeg;base64,'.base64_encode($user['image']) : 'assets/default-avatar.png'; ?>" 
                                 class="profile-image" alt="Profile Preview">
                            <label for="image" class="image-upload">
                                <i class="bi bi-camera"></i>
                            </label>
                            <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                        </div>

                        <div class="quick-nav">
                            <a href="#profile-info" class="btn btn-outline">Profile Info</a>
                            <a href="#security" class="btn btn-outline">Security</a>
                        </div>

                        <div id="profile-info" class="mb-5">
                            <h3 class="section-title">Profile Information</h3>
                            <div class="mb-4">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>

                        <div id="security" class="mb-5">
                            <h3 class="section-title">Security Settings</h3>
                            <div class="mb-4">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" 
                                       placeholder="Enter current password to change password">
                            </div>

                            <div class="mb-4">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       placeholder="Enter new password">
                                <div class="form-text">Leave blank if you don't want to change password</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline">Cancel</a>
                            <button type="submit" name="update_profile" class="btn btn-update">Update Profile</button>
                        </div>
                    </form>
                </div>
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

        // Smooth scroll for quick navigation
        document.querySelectorAll('.quick-nav a').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                document.querySelector(targetId).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>