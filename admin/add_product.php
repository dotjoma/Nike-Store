<?php
    require_once("../includes/connect.php");
    require_once("../includes/activity_logger.php");

    session_start();

    // Check if user is logged in and is an admin
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        // Redirect to home page if not admin
        header("Location: ../index.php");
        exit();
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            // Get form data
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category_id = $_POST['category_id'];
            $status = $_POST['status'];

            // Handle image upload
            $image = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/avif'];
                $filetype = $_FILES['image']['type'];

                // Verify file type
                if (in_array($filetype, $allowed)) {
                    // Read file content
                    $image = file_get_contents($_FILES['image']['tmp_name']);
                } else {
                    throw new Exception("Invalid file type. Only JPG, PNG, GIF and AVIF are allowed.");
                }
            }

            // Insert into database
            $query = "INSERT INTO products (name, description, price, stock, category_id, status, image) 
                     VALUES (:name, :description, :price, :stock, :category_id, :status, :image)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':image', $image, PDO::PARAM_LOB);
            
            if ($stmt->execute()) {
                // Log the activity
                logActivity($conn, $_SESSION['user_id'], "Add Product", "Added new product: " . $name);
                
                header("Location: product.php");
                exit();
            }

        } catch(Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }

    // Fetch categories for dropdown
    try {
        $cat_query = "SELECT * FROM categories ORDER BY category_name";
        $cat_stmt = $conn->prepare($cat_query);
        $cat_stmt->execute();
        $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Add New Product - Nike Admin Dashboard" />
        <meta name="author" content="" />
        <title>Add New Product - Nike Admin</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="../assets/favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="../css/styles.css" rel="stylesheet" />
        <style>
            :root {
                --nike-black: #000000;
                --nike-white: #ffffff;
                --nike-gray: #757575;
            }
            body {
                font-family: 'Helvetica Neue', Arial, sans-serif;
                background-color: #f8f9fa;
            }
            .navbar {
                background-color: var(--nike-black) !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .navbar-brand, .nav-link {
                color: var(--nike-white) !important;
                font-weight: 600;
            }
            .nav-link:hover {
                color: var(--nike-gray) !important;
            }
            .btn-nike {
                background-color: var(--nike-black);
                color: var(--nike-white);
                border-radius: 30px;
                padding: 12px 30px;
                font-weight: 600;
                text-transform: uppercase;
            }
            .btn-nike:hover {
                background-color: var(--nike-gray);
                color: var(--nike-white);
            }
        </style>
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
                        <h1 class="mt-4">Add New Product</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="product.php">Products</a></li>
                            <li class="breadcrumb-item active">Add Product</li>
                        </ol>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-plus me-1"></i>
                                Add Product Form
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Product Name</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="price" class="form-label">Price</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="stock" class="form-label">Stock</label>
                                            <input type="number" class="form-control" id="stock" name="stock" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="category_id" class="form-label">Category</label>
                                            <div class="input-group">
                                                <select class="form-select" id="category_id" name="category_id" required>
                                                    <option value="">Select Category</option>
                                                    <?php foreach($categories as $category): ?>
                                                        <option value="<?php echo $category['id']; ?>">
                                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <a href="categories.php" class="btn btn-primary">
                                                    <i class="fas fa-plus"></i> New Category
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="image" class="form-label">Product Image</label>
                                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                            <small class="text-muted">Allowed formats: JPG, JPEG, PNG, GIF, AVIF</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary">Add Product</button>
                                        <a href="product.php" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </main>
                <!-- Start of Footer -->
                <?php require_once("../includes/footer.php"); ?>
                <!-- End of Footer -->
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../js/scripts.js"></script>
    </body>
</html> 