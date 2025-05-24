<?php
    require_once("../includes/connect.php");

    session_start();

    // Check if user is logged in and is an admin
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        // Redirect to home page if not admin
        header("Location: ../index.php");
        exit();
    }

    // Check if ID is provided
    if (!isset($_GET['id'])) {
        header("Location: categories.php");
        exit();
    }

    $hashed_id = $_GET['id'];

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            // Get form data
            $category_name = $_POST['category_name'];

            // Get the actual ID from the database using the hashed ID
            $id_query = "SELECT id FROM categories WHERE MD5(id) = ?";
            $id_stmt = $conn->prepare($id_query);
            $id_stmt->execute([$hashed_id]);
            $category_id = $id_stmt->fetchColumn();

            if (!$category_id) {
                throw new Exception("Category not found");
            }

            // Update database
            $query = "UPDATE categories SET category_name = :category_name WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':category_name', $category_name);
            $stmt->bindParam(':id', $category_id);
            
            if ($stmt->execute()) {
                header("Location: categories.php");
                exit();
            }

        } catch(Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }

    // Fetch category data
    try {
        $query = "SELECT * FROM categories WHERE MD5(id) = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$hashed_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            header("Location: categories.php");
            exit();
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Edit Category - Nike Admin Dashboard" />
        <meta name="author" content="" />
        <title>Edit Category - Nike Admin</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="../assets/favicon.ico" />
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
                        <h1 class="mt-4">Edit Category</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="categories.php">Categories</a></li>
                            <li class="breadcrumb-item active">Edit Category</li>
                        </ol>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-edit me-1"></i>
                                Edit Category Form
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="category_name" class="form-label">Category Name</label>
                                        <input type="text" class="form-control" id="category_name" name="category_name" 
                                               value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary">Update Category</button>
                                        <a href="categories.php" class="btn btn-secondary">Cancel</a>
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