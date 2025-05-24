<?php
    require_once("includes/connect.php");

    // Fetch all categories with their products
    try {
        $query = "SELECT c.id as category_id, c.category_name, 
                        p.id as product_id, p.name, p.price, p.stock, p.image, p.status
                 FROM categories c
                 LEFT JOIN products p ON c.id = p.category_id
                 WHERE p.status = 'active'
                 ORDER BY c.category_name, p.name";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group products by category
        $categories = [];
        foreach($results as $row) {
            if(!isset($categories[$row['category_name']])) {
                $categories[$row['category_name']] = [];
            }
            if($row['product_id']) { // Only add if product exists
                $categories[$row['category_name']][] = $row;
            }
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
        <meta name="description" content="Nike Store - Your One-Stop Shop for Nike Products" />
        <meta name="author" content="" />
        <title>Nike Store</title>
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
            .category-title {
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
                margin-bottom: 20px;
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
            .profile-image {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                object-fit: cover;
            }
            .btn {
                border-radius: 20px;
                padding: 0.5rem 1rem;
                font-weight: 500;
            }
            .gap-2 {
                gap: 0.5rem;
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
                        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    </ul>
                    <form class="d-flex me-3" action="search.php" method="GET">
                        <input class="form-control me-2" type="search" name="query" placeholder="Search products..." aria-label="Search">
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
                    <h1 class="display-4 fw-bolder">Welcome to Nike Store</h1>
                    <p class="lead fw-normal text-white-50 mb-0">Find your perfect Nike products</p>
                </div>
            </div>
        </header>

        <!-- Products section-->
        <section class="py-5">
            <div class="container px-4 px-lg-5">
                <?php foreach($categories as $category_name => $products): ?>
                    <h2 class="category-title"><?php echo htmlspecialchars($category_name); ?></h2>
                    <div class="product-carousel-container position-relative mb-5">
                        <button class="carousel-arrow carousel-arrow-left btn btn-light position-absolute top-50 start-0 translate-middle-y" style="z-index: 10;">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="product-carousel d-flex flex-nowrap overflow-auto">
                            <?php foreach($products as $product): ?>
                                <div class="col-md-3 flex-shrink-0 me-3">
                                    <a href="product.php?id=<?php echo $product['product_id']; ?>" class="text-decoration-none text-dark">
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
                                                            onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                                        Add to Cart
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-arrow carousel-arrow-right btn btn-light position-absolute top-50 end-0 translate-middle-y" style="z-index: 5;">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
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

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.product-carousel-container').forEach(container => {
                    const carousel = container.querySelector('.product-carousel');
                    const leftArrow = container.querySelector('.carousel-arrow-left');
                    const rightArrow = container.querySelector('.carousel-arrow-right');
            
                    const toggleArrows = () => {
                        if (carousel.scrollLeft <= 5) {
                            leftArrow.style.display = 'none';
                        } else {
                            leftArrow.style.display = 'block';
                        }

                        if (carousel.scrollWidth <= carousel.clientWidth + 5) {
                            leftArrow.style.display = 'none';
                            rightArrow.style.display = 'none';
                        } else if (carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 5) {
                             rightArrow.style.display = 'none';
                        } else {
                             rightArrow.style.display = 'block';
                        }
                    };

                    toggleArrows();

                    carousel.addEventListener('scroll', toggleArrows);

                    leftArrow.addEventListener('click', () => {
                        const scrollAmount = carousel.querySelector('.col-md-3').offsetWidth + 16; 
                        carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                    });

                    rightArrow.addEventListener('click', () => {
                        const scrollAmount = carousel.querySelector('.col-md-3').offsetWidth + 16;
                        carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                    });

                    window.addEventListener('resize', toggleArrows);
                });
            });
        </script>
    </body>
</html>
