<?php
    require_once("../includes/connect.php");

    session_start();

    // Check if user is logged in and is an admin
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        // Redirect to home page if not admin
        header("Location: ../index.php");
        exit();
    }
    
    // Pagination settings
    $items_per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $items_per_page;

    // Get total records for pagination
    $count_query = "SELECT COUNT(*) FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE 1=1";
    $params = [];

    // Search filter
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $count_query .= " AND p.name LIKE :search";
        $params[':search'] = '%' . $_GET['search'] . '%';
    }

    // Category filter
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $count_query .= " AND p.category_id = :category";
        $params[':category'] = $_GET['category'];
    }

    // Status filter
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $count_query .= " AND p.status = :status";
        $params[':status'] = $_GET['status'];
    }

    try {
        $count_stmt = $conn->prepare($count_query);
        foreach($params as $key => $value) {
            $count_stmt->bindValue($key, $value);
        }
        $count_stmt->execute();
        $total_records = $count_stmt->fetchColumn();
        $total_pages = ceil($total_records / $items_per_page);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Build the main query with filters and sorting
    $query = "SELECT p.*, c.category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE 1=1";

    // Add filters
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $query .= " AND p.name LIKE :search";
    }
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $query .= " AND p.category_id = :category";
    }
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $query .= " AND p.status = :status";
    }

    // Add sorting
    $sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'id';
    $sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
    $allowed_columns = ['id', 'name', 'price', 'stock', 'category_name', 'status'];
    
    if (!in_array($sort_column, $allowed_columns)) {
        $sort_column = 'id';
    }
    if ($sort_order !== 'ASC' && $sort_order !== 'DESC') {
        $sort_order = 'DESC';
    }

    $query .= " ORDER BY p.$sort_column $sort_order LIMIT :offset, :limit";
    
    try {
        $stmt = $conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Fetch categories for the filter dropdown
    try {
        $cat_query = "SELECT * FROM categories ORDER BY category_name";
        $cat_stmt = $conn->prepare($cat_query);
        $cat_stmt->execute();
        $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Function to generate sort URL
    function getSortUrl($column) {
        $params = $_GET;
        $params['sort'] = $column;
        $params['order'] = (isset($_GET['sort']) && $_GET['sort'] === $column && $_GET['order'] === 'ASC') ? 'DESC' : 'ASC';
        return '?' . http_build_query($params);
    }

    // Function to generate pagination URL
    function getPageUrl($page) {
        $params = $_GET;
        $params['page'] = $page;
        return '?' . http_build_query($params);
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
        <title>Product Management - SB Admin</title>
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
                        <h1 class="mt-4">Product Management</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Products</li>
                        </ol>

                        <!-- Add Product Button -->
                        <div class="mb-4">
                            <a href="add_product.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Product
                            </a>
                        </div>

                        <!-- Search and Filter -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-search me-1"></i>
                                Search Products
                            </div>
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="search" 
                                               placeholder="Search by name..." 
                                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="category">
                                            <option value="">All Categories</option>
                                            <?php foreach($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="status">
                                            <option value="">All Status</option>
                                            <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">Search</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Products Table -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Products List
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>
                                                <a href="<?php echo getSortUrl('name'); ?>" class="text-dark text-decoration-none">
                                                    Name
                                                    <?php if(isset($_GET['sort']) && $_GET['sort'] === 'name'): ?>
                                                        <i class="fas fa-sort-<?php echo $_GET['order'] === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th>
                                                <a href="<?php echo getSortUrl('price'); ?>" class="text-dark text-decoration-none">
                                                    Price
                                                    <?php if(isset($_GET['sort']) && $_GET['sort'] === 'price'): ?>
                                                        <i class="fas fa-sort-<?php echo $_GET['order'] === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th>
                                                <a href="<?php echo getSortUrl('stock'); ?>" class="text-dark text-decoration-none">
                                                    Stock
                                                    <?php if(isset($_GET['sort']) && $_GET['sort'] === 'stock'): ?>
                                                        <i class="fas fa-sort-<?php echo $_GET['order'] === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th>
                                                <a href="<?php echo getSortUrl('category_name'); ?>" class="text-dark text-decoration-none">
                                                    Category
                                                    <?php if(isset($_GET['sort']) && $_GET['sort'] === 'category_name'): ?>
                                                        <i class="fas fa-sort-<?php echo $_GET['order'] === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th>
                                                <a href="<?php echo getSortUrl('status'); ?>" class="text-dark text-decoration-none">
                                                    Status
                                                    <?php if(isset($_GET['sort']) && $_GET['sort'] === 'status'): ?>
                                                        <i class="fas fa-sort-<?php echo $_GET['order'] === 'ASC' ? 'up' : 'down'; ?>"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($products as $product): ?>
                                        <tr>
                                            <td>
                                                <?php if($product['image']): ?>
                                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($product['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                         class="img-thumbnail" 
                                                         style="max-width: 50px;">
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                            <td><?php echo $product['stock']; ?></td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($product['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" 
                                                   onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')" 
                                                   class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <!-- Pagination -->
                                <?php if($total_pages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo getPageUrl($page - 1); ?>">Previous</a>
                                        </li>
                                        
                                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="<?php echo getPageUrl($i); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo getPageUrl($page + 1); ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                                <?php endif; ?>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script>
            // Initialize DataTables
            window.addEventListener('DOMContentLoaded', event => {
                const datatablesSimple = document.getElementById('datatablesSimple');
                if (datatablesSimple) {
                    new simpleDatatables.DataTable(datatablesSimple);
                }
            });
        </script>
        <script>
        function confirmDelete(id, name) {
            return Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `delete_product.php?id=${id}`;
                }
            });
        }
        </script>
        <!-- Add SweetAlert2 CSS and JS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    </body>
</html>