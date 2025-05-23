<?php
    require_once("../includes/connect.php");
    session_start();

    // Check if user is logged in and is an admin
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        // Redirect to home page if not admin
        header("Location: ../index.php");
        exit();
    }

    // Fetch statistics from database
    try {
        // Total Users
        $users_query = "SELECT COUNT(*) as total_users FROM users";
        $users_stmt = $conn->prepare($users_query);
        $users_stmt->execute();
        $total_users = $users_stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

        // Total Products
        $products_query = "SELECT COUNT(*) as total_products FROM products";
        $products_stmt = $conn->prepare($products_query);
        $products_stmt->execute();
        $total_products = $products_stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

        // Active Products
        $active_products_query = "SELECT COUNT(*) as active_products FROM products WHERE status = 'active'";
        $active_products_stmt = $conn->prepare($active_products_query);
        $active_products_stmt->execute();
        $active_products = $active_products_stmt->fetch(PDO::FETCH_ASSOC)['active_products'];

        // Total Categories
        $categories_query = "SELECT COUNT(*) as total_categories FROM categories";
        $categories_stmt = $conn->prepare($categories_query);
        $categories_stmt->execute();
        $total_categories = $categories_stmt->fetch(PDO::FETCH_ASSOC)['total_categories'];

    } catch(PDOException $e) {
        // Handle any database errors
        $error = "Error: " . $e->getMessage();
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
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="../assets/favicon.ico" />
        <title>Dashboard - SB Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
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
                        <h1 class="mt-4">Dashboard</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                        <!-- Analytics Cards -->
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h4 class="mb-0">Total Users</h4>
                                                <h2 class="mb-0"><?php echo number_format($total_users); ?></h2>
                                            </div>
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="users.php">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h4 class="mb-0">Total Products</h4>
                                                <h2 class="mb-0"><?php echo number_format($total_products); ?></h2>
                                            </div>
                                            <i class="fas fa-box fa-2x"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="product.php">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h4 class="mb-0">Active Products</h4>
                                                <h2 class="mb-0"><?php echo number_format($active_products); ?></h2>
                                            </div>
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="product.php?status=active">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-info text-white mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h4 class="mb-0">Categories</h4>
                                                <h2 class="mb-0"><?php echo number_format($total_categories); ?></h2>
                                            </div>
                                            <i class="fas fa-tags fa-2x"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="categories.php">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Products and Quick Actions Section -->
                        <div class="row">
                            <!-- Recent Products Table -->
                            <div class="col-xl-8">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-table me-1"></i>
                                        Recent Products
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th>Category</th>
                                                    <th>Price</th>
                                                    <th>Stock</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                try {
                                                    $recent_products_query = "SELECT p.*, c.category_name 
                                                                           FROM products p 
                                                                           LEFT JOIN categories c ON p.category_id = c.id 
                                                                           ORDER BY p.id DESC LIMIT 5";
                                                    $recent_products_stmt = $conn->prepare($recent_products_query);
                                                    $recent_products_stmt->execute();
                                                    $recent_products = $recent_products_stmt->fetchAll(PDO::FETCH_ASSOC);

                                                    foreach($recent_products as $product) {
                                                        echo "<tr>";
                                                        echo "<td>{$product['name']}</td>";
                                                        echo "<td>{$product['category_name']}</td>";
                                                        echo "<td>₱" . number_format($product['price'], 2) . "</td>";
                                                        echo "<td>{$product['stock']}</td>";
                                                        echo "<td><span class='badge " . ($product['status'] == 'active' ? 'bg-success' : 'bg-danger') . "'>{$product['status']}</span></td>";
                                                        echo "</tr>";
                                                    }
                                                } catch(PDOException $e) {
                                                    echo "<tr><td colspan='5'>Error loading recent products</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions and Alerts -->
                            <div class="col-xl-4">
                                <!-- Low Stock Alert -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Low Stock Alert
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group">
                                            <?php
                                            try {
                                                $low_stock_query = "SELECT name, stock FROM products WHERE stock <= 5 AND status = 'active' ORDER BY stock ASC LIMIT 5";
                                                $low_stock_stmt = $conn->prepare($low_stock_query);
                                                $low_stock_stmt->execute();
                                                $low_stock_products = $low_stock_stmt->fetchAll(PDO::FETCH_ASSOC);

                                                if(count($low_stock_products) > 0) {
                                                    foreach($low_stock_products as $product) {
                                                        echo "<a href='product.php' class='list-group-item list-group-item-action'>";
                                                        echo "<div class='d-flex w-100 justify-content-between'>";
                                                        echo "<h6 class='mb-1'>{$product['name']}</h6>";
                                                        echo "<small class='text-danger'>Stock: {$product['stock']}</small>";
                                                        echo "</div>";
                                                        echo "</a>";
                                                    }
                                                } else {
                                                    echo "<p class='text-muted mb-0'>No low stock items</p>";
                                                }
                                            } catch(PDOException $e) {
                                                echo "<p class='text-danger'>Error loading low stock items</p>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-bolt me-1"></i>
                                        Quick Actions
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="add_product.php" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i> Add New Product
                                            </a>
                                            <a href="categories.php" class="btn btn-success">
                                                <i class="fas fa-tags me-1"></i> Manage Categories
                                            </a>
                                            <a href="users.php" class="btn btn-info text-white">
                                                <i class="fas fa-users me-1"></i> Manage Users
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php require_once("../includes/modal.php"); ?>
                </main>
                <!-- Start of Footer -->
                <?php require_once("../includes/footer.php"); ?>
                <!-- End of Footer -->
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    </body>
</html>
