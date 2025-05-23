<?php
    require_once("../includes/connect.php");

    session_start();

    // Check if user is logged in and is an admin
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        // Redirect to home page if not admin
        header("Location: ../index.php");
        exit();
    }

    // Handle form submission for adding category
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            $category_name = $_POST['category_name'];
            
            $query = "INSERT INTO categories (category_name) VALUES (:category_name)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':category_name', $category_name);
            
            if ($stmt->execute()) {
                header("Location: categories.php");
                exit();
            }
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }

    // Fetch all categories
    try {
        $query = "SELECT * FROM categories ORDER BY category_name";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
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
                        <h1 class="mt-4">Category Management</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Categories</li>
                        </ol>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <!-- Add Category Form -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-plus me-1"></i>
                                Add New Category
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="category_name" 
                                                   placeholder="Enter category name" required>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-nike">Add Category</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Categories Table -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Categories List
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Category Name</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                            <td>
                                                <a href="edit_category.php?id=<?php echo $category['id']; ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_category.php?id=<?php echo $category['id']; ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Are you sure you want to delete this category?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
        <script>
            window.addEventListener('DOMContentLoaded', event => {
                const datatablesSimple = document.getElementById('datatablesSimple');
                if (datatablesSimple) {
                    new simpleDatatables.DataTable(datatablesSimple);
                }
            });
        </script>
    </body>
</html>