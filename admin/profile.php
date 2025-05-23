<?php
    require_once("../includes/connect.php");
    require_once("../includes/activity_logger.php");
    session_start();

    // Check if user is logged in and is an admin
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $image = null;
            $changes = array(); // Track what changes were made

            // Get current user data
            $query = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Track name change
            if($name !== $user['name']) {
                $changes[] = "Name changed from '{$user['name']}' to '$name'";
            }

            // Track email change
            if($email !== $user['email']) {
                $changes[] = "Email changed from '{$user['email']}' to '$email'";
            }

            // Verify current password if changing password
            if(!empty($new_password)) {
                if(!password_verify($current_password, $user['password'])) {
                    throw new Exception("Current password is incorrect");
                }
                $password = password_hash($new_password, PASSWORD_DEFAULT);
                $changes[] = "Password updated";
            }

            // Handle image upload
            if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB

                if(!in_array($_FILES['image']['type'], $allowed_types)) {
                    throw new Exception("Invalid file type. Only JPEG, PNG and GIF are allowed.");
                }
                if($_FILES['image']['size'] > $max_size) {
                    throw new Exception("File size too large. Maximum size is 2MB.");
                }
                $image = file_get_contents($_FILES['image']['tmp_name']);
                $changes[] = "Profile picture updated";
            }

            // Check if email is already taken by another user
            if($email !== $user['email']) {
                $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->execute([$email, $_SESSION['user_id']]);
                
                if($check_stmt->rowCount() > 0) {
                    throw new Exception("Email already exists");
                }
            }

            // Update user profile
            if(!empty($new_password)) {
                if($image) {
                    $query = "UPDATE users SET name = ?, email = ?, password = ?, image = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':image', $image, PDO::PARAM_LOB);
                    $stmt->execute([$name, $email, $password, $image, $_SESSION['user_id']]);
                } else {
                    $query = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$name, $email, $password, $_SESSION['user_id']]);
                }
            } else {
                if($image) {
                    $query = "UPDATE users SET name = ?, email = ?, image = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':image', $image, PDO::PARAM_LOB);
                    $stmt->execute([$name, $email, $image, $_SESSION['user_id']]);
                } else {
                    $query = "UPDATE users SET name = ?, email = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$name, $email, $_SESSION['user_id']]);
                }
            }

            // Update session variables
            $_SESSION['user_name'] = $name;
            if($image) {
                $_SESSION['user_image'] = $image;
            }

            // Log the profile update if any changes were made
            if(!empty($changes)) {
                $change_details = implode(", ", $changes);
                logActivity($conn, $_SESSION['user_id'], "Profile Update", $change_details);
            }

            $success = "Profile updated successfully";
            
            // Refresh user data
            $query = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch(Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        // Fetch current user data
        try {
            $query = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Profile Settings - Admin Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
    </head>
    <body class="sb-nav-fixed">
        <!-- Start of Header -->
        <?php require_once("../includes/header.php"); ?>
        <!-- End of Header -->

        <div id="layoutSidenav">
            <!-- Start of Menu -->
            <?php require_once("../includes/menu.php"); ?>
            <!-- End of Menu -->
             
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Profile Settings</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Profile Settings</li>
                        </ol>

                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-xl-4">
                                <!-- Profile picture card-->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-user-circle me-1"></i>
                                        Profile Picture
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if($user['image']): ?>
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($user['image']); ?>" 
                                                 class="img-account-profile rounded-circle mb-2" 
                                                 style="width: 150px; height: 150px; object-fit: cover;">
                                        <?php else: ?>
                                            <img src="../assets/default-avatar.png" 
                                                 class="img-account-profile rounded-circle mb-2" 
                                                 style="width: 150px; height: 150px; object-fit: cover;">
                                        <?php endif; ?>
                                        <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 2 MB</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8">
                                <!-- Account details card-->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-user-cog me-1"></i>
                                        Account Details
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <!-- Form Group (name)-->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="name">Full Name</label>
                                                <input class="form-control" id="name" name="name" type="text" 
                                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                            </div>
                                            <!-- Form Group (email address)-->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="email">Email address</label>
                                                <input class="form-control" id="email" name="email" type="email" 
                                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            </div>
                                            <!-- Form Group (image)-->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="image">Profile Picture</label>
                                                <input class="form-control" id="image" name="image" type="file" accept="image/*">
                                            </div>
                                            <!-- Form Group (current password)-->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="current_password">Current Password</label>
                                                <input class="form-control" id="current_password" name="current_password" type="password">
                                            </div>
                                            <!-- Form Group (new password)-->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="new_password">New Password</label>
                                                <input class="form-control" id="new_password" name="new_password" type="password">
                                                <small class="text-muted">Leave blank to keep current password</small>
                                            </div>
                                            <!-- Save changes button-->
                                            <button class="btn btn-primary" type="submit">Save changes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                <!-- Start of Footer -->
                <?php require_once("../includes/footer.php"); ?>
                <!-- End of Footer -->
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
    </body>
</html>