<?php
require_once("../includes/connect.php");
session_start();

// Check if user is logged in and is an admin
if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';

// Handle CSV file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['csv_file'])) {
    try {
        $file = $_FILES['csv_file'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed with error code: " . $file['error']);
        }

        // Check file type
        $file_type = mime_content_type($file['tmp_name']);
        if ($file_type !== 'text/csv' && $file_type !== 'text/plain') {
            throw new Exception("Invalid file type. Please upload a CSV file.");
        }

        // Open the CSV file
        $handle = fopen($file['tmp_name'], "r");
        if ($handle === FALSE) {
            throw new Exception("Could not open the CSV file.");
        }

        // Read the header row
        $header = fgetcsv($handle);
        if ($header === FALSE) {
            throw new Exception("The CSV file is empty.");
        }

        // Validate required columns
        $required_columns = ['name', 'description', 'price', 'stock', 'category_id'];
        $missing_columns = array_diff($required_columns, $header);
        if (!empty($missing_columns)) {
            throw new Exception("Missing required columns: " . implode(", ", $missing_columns));
        }

        // Get column indexes
        $name_index = array_search('name', $header);
        $description_index = array_search('description', $header);
        $price_index = array_search('price', $header);
        $stock_index = array_search('stock', $header);
        $category_id_index = array_search('category_id', $header);

        // Prepare the insert statement
        $query = "INSERT INTO products (name, description, price, stock, category_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);

        $row = 2; // Start from row 2 (after header)
        $success_count = 0;
        $error_rows = [];

        // Read and process each row
        while (($data = fgetcsv($handle)) !== FALSE) {
            try {
                // Validate data
                if (empty($data[$name_index])) throw new Exception("Name is required");
                if (!is_numeric($data[$price_index])) throw new Exception("Price must be a number");
                if (!is_numeric($data[$stock_index])) throw new Exception("Stock must be a number");
                if (!is_numeric($data[$category_id_index])) throw new Exception("Category ID must be a number");

                // Check if category exists
                $check_category = $conn->prepare("SELECT id FROM categories WHERE id = ?");
                $check_category->execute([$data[$category_id_index]]);
                if ($check_category->rowCount() === 0) {
                    throw new Exception("Category ID does not exist");
                }

                // Insert the product
                $stmt->execute([
                    $data[$name_index],
                    $data[$description_index],
                    $data[$price_index],
                    $data[$stock_index],
                    $data[$category_id_index]
                ]);

                $success_count++;
            } catch (Exception $e) {
                $error_rows[] = "Row $row: " . $e->getMessage();
            }
            $row++;
        }

        fclose($handle);

        // Set success/error messages
        if ($success_count > 0) {
            $success = "Successfully added $success_count products.";
        }
        if (!empty($error_rows)) {
            $error = "Errors occurred:<br>" . implode("<br>", $error_rows);
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch categories for reference
try {
    $cat_query = "SELECT id, category_name FROM categories ORDER BY category_name";
    $cat_stmt = $conn->prepare($cat_query);
    $cat_stmt->execute();
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $errors[] = "Error fetching categories: " . $e->getMessage();
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
        <title>Bulk Add Products - Admin Dashboard</title>
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
                        <h1 class="mt-4">Bulk Add Products</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="product.php">Products</a></li>
                            <li class="breadcrumb-item active">Bulk Add</li>
                        </ol>

                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-upload me-1"></i>
                                Upload Products CSV
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="csv_file" class="form-label">Select CSV File</label>
                                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                                        <div class="form-text">
                                            The CSV file should have the following columns: name, description, price, stock, category_id
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Upload and Process</button>
                                    <a href="product.php" class="btn btn-secondary">Cancel</a>
                                </form>

                                <hr class="my-4">

                                <h5>CSV Template</h5>
                                <p>Download a template CSV file with the correct format:</p>
                                <a href="templates/products_template.csv" class="btn btn-outline-primary" download>
                                    <i class="fas fa-download me-1"></i>
                                    Download Template
                                </a>

                                <hr class="my-4">

                                <h5>Available Categories</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Category ID</th>
                                                <th>Category Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($categories as $category): ?>
                                                <tr>
                                                    <td><?php echo $category['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
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