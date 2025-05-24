<?php
require_once("includes/connect.php");

// Get the search query
$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';

// If search query is empty, redirect to home page
if (empty($search_query)) {
    header("Location: index.php");
    exit();
}

try {
    // Prepare the search query
    $query = "SELECT p.*, c.category_name 
              FROM products p 
              JOIN categories c ON p.category_id = c.id 
              WHERE p.status = 'active' 
              AND (p.name LIKE :search 
              OR c.category_name LIKE :search)
              ORDER BY p.name";
    
    $stmt = $conn->prepare($query);
    $search_param = "%" . $search_query . "%";
    $stmt->bindParam(':search', $search_param);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Search Results - Nike Store" />
        <meta name="author" content="" />
        <title>Search Results - Nike Store</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
        <link href="css/global-styles.css" rel="stylesheet" />
        <style>
            .product-card {
                transition: transform 0.3s ease;
                height: 100%;
            }
            .product-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .product-image {
                height: 200px;
                object-fit: cover;
            }
            .price {
                font-weight: bold;
                color: #000;
            }
            .stock {
                font-size: 0.9em;
                color: #666;
            }
            .add-to-cart {
                width: 100%;
                border-radius: 30px;
                text-transform: uppercase;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container px-4 px-lg-5">
                <a class="navbar-brand" href="index.php">NIKE STORE</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    </ul>
                    <form class="d-flex me-3" action="search.php" method="GET">
                        <input class="form-control me-2" type="search" name="query" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>" aria-label="Search">
                        <button class="btn btn-outline-light" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                    <div class="d-flex align-items-center">
                        <form class="d-flex me-3">
                            <button class="btn btn-outline-light" type="submit">
                                <i class="bi-cart-fill me-1"></i>
                                Cart
                                <span class="badge bg-light text-dark ms-1 rounded-pill">0</span>
                            </button>
                        </form>
                        <?php
                        session_start();
                        if(isset($_SESSION['user_id'])): ?>
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php if(isset($_SESSION['user_image'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($_SESSION['user_image']); ?>" 
                                             class="profile-image me-2" alt="Profile">
                                    <?php else: ?>
                                        <i class="bi bi-person-circle me-1"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="orders.php">Orders</a></li>
                                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                        <li><a class="dropdown-item" href="admin/index.php">Admin Dashboard</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="d-flex gap-2">
                                <a href="login.php" class="btn btn-outline-light">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Login
                                </a>
                                <a href="register.php" class="btn btn-light">
                                    <i class="bi bi-person-plus me-1"></i>Register
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">
                <div class="text-center text-white">
                    <h1 class="display-4 fw-bolder">Search Results</h1>
                    <p class="lead fw-normal text-white-50 mb-0">Showing results for: "<?php echo htmlspecialchars($search_query); ?>"</p>
                </div>
            </div>
        </header>

        <!-- Products section-->
        <section class="py-5">
            <div class="container px-4 px-lg-5">
                <?php if (empty($products)): ?>
                    <div class="text-center">
                        <h3>No products found matching your search.</h3>
                        <p>Try different keywords or browse our categories.</p>
                    </div>
                <?php else: ?>
                    <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                        <?php foreach($products as $product): ?>
                            <div class="col mb-5">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                                    <div class="card h-100 product-card">
                                        <!-- Product image-->
                                        <?php if($product['image']): ?>
                                            <img class="card-img-top product-image" 
                                                 src="data:image/jpeg;base64,<?php echo base64_encode($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" />
                                        <?php else: ?>
                                            <img class="card-img-top product-image" 
                                                 src="assets/no-image.jpg" 
                                                 alt="No image available" />
                                        <?php endif; ?>
                                        
                                        <!-- Product details-->
                                        <div class="card-body p-4">
                                            <div class="text-center">
                                                <!-- Product name-->
                                                <h5 class="fw-bolder"><?php echo htmlspecialchars($product['name']); ?></h5>
                                                <!-- Product category-->
                                                <div class="text-muted mb-2"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                                <!-- Product price-->
                                                <div class="price">₱<?php echo number_format($product['price'], 2); ?></div>
                                                <!-- Product stock-->
                                                <div class="stock">In Stock: <?php echo $product['stock']; ?></div>
                                            </div>
                                        </div>
                                        <!-- Product actions-->
                                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                            <div class="text-center">
                                                <button class="btn btn-outline-dark add-to-cart" 
                                                        onclick="addToCart(<?php echo $product['id']; ?>)">
                                                    Add to Cart
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Footer-->
        <footer class="py-5 bg-dark">
            <div class="container">
                <p class="m-0 text-center text-white">Copyright &copy; Buaron Store 2025</p>
            </div>
        </footer>

        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
        <script>
            function addToCart(productId) {
                // addtocart function, no need!
            }
        </script>
    </body>
</html> 