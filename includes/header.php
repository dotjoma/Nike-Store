<?php
    // Get current user data if logged in
    if(isset($_SESSION['user_id'])) {
        try {
            $query = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$_SESSION['user_id']]);
            $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            // Handle error silently
        }
    }
?>
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="<?php echo isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' ? 'index.php' : 'index.php'; ?>">
        <?php echo isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' ? 'Admin Dashboard' : 'Nike Store'; ?>
    </a>
    
    <!-- Sidebar Toggle-->
    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
            <i class="fas fa-bars"></i>
        </button>
    <?php endif; ?>

    <!-- Navbar Search-->
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        <div class="input-group">
            <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
            <button class="btn btn-primary" id="btnNavbarSearch" type="button">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>

    <!-- Navbar-->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <?php if(isset($_SESSION['user_id'])): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php if(isset($current_user['image'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($current_user['image']); ?>" 
                             class="rounded-circle me-2" 
                             style="width: 25px; height: 25px; object-fit: cover;">
                    <?php else: ?>
                        <i class="bi bi-person-circle me-2"></i>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li>
                        <a class="dropdown-item" href="profile.php">
                            <i class="bi bi-person-gear me-2"></i>Profile Settings
                        </a>
                    </li>
                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li>
                            <a class="dropdown-item" href="activity_log.php">
                                <i class="bi bi-clock-history me-2"></i>Activity Log
                            </a>
                        </li>
                        <li><hr class="dropdown-divider" /></li>
                    <?php endif; ?>
                    <li>
                        <a class="dropdown-item" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="login.php">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="register.php">
                    <i class="bi bi-person-plus me-2"></i>Register
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>