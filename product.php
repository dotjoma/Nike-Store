<?php
require_once("includes/connect.php");
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Invalid or missing product ID
    header("Location: 404.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch product details from the database
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    // Product not found
    header("Location: 404.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?php echo htmlspecialchars($product['name']); ?> - Nike Store</title>
    <link rel="icon" type="image/x-icon" href="/finalprojectbuaron/assets/favicon.ico" />
    <link href="/finalprojectbuaron/css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        .product-details-container {
            max-width: 900px;
            margin: 3rem auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0,0,0,0.08);
            padding: 2.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
        }
        .product-image {
            flex: 1 1 300px;
            max-width: 350px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            object-fit: cover;
        }
        .product-info {
            flex: 2 1 400px;
        }
        .product-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .product-price {
            font-size: 1.5rem;
            color: #000;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .product-description {
            color: #444;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        .btn-back {
            background: #000;
            color: #fff;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-back:hover {
            background: #333;
            color: #fff;
        }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #000;">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="/finalprojectbuaron/index.php">NIKE</a>
            
        </div>
    </nav>

    <div class="container">
        <div class="product-details-container">
            <img src="data:image/jpeg;base64,<?php echo base64_encode($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
                <div class="product-description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
                <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back to Products</a>
            </div>
        </div>
    </div>

  
    <footer class="py-4 bg-dark">
        <div class="container">
            <p class="m-0 text-center text-white">© 2025 Nike, Inc. All Rights Reserved</p>
        </div>
    </footer>
</body>
</html>