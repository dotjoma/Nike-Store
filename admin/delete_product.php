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

    // Check if ID is provided
    if (!isset($_GET['id'])) {
        header("Location: product.php");
        exit();
    }

    $hashed_id = $_GET['id'];

    try {
        // Get the actual ID from the database using the hashed ID
        $id_query = "SELECT id FROM products WHERE MD5(id) = ?";
        $id_stmt = $conn->prepare($id_query);
        $id_stmt->execute([$hashed_id]);
        $product_id = $id_stmt->fetchColumn();

        if (!$product_id) {
            throw new Exception("Product not found");
        }

        // First get the product name for logging
        $get_product_query = "SELECT name FROM products WHERE id = :id";
        $get_product_stmt = $conn->prepare($get_product_query);
        $get_product_stmt->bindParam(':id', $product_id);
        $get_product_stmt->execute();
        $product = $get_product_stmt->fetch(PDO::FETCH_ASSOC);

        // Delete the product
        $query = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$product_id]);

        // Log the activity
        logActivity($conn, $_SESSION['user_id'], "Delete Product", "Deleted product: " . $product['name'] . " (ID: " . $product_id . ")");

        header("Location: product.php?success=Product deleted successfully");
        exit();
    } catch(Exception $e) {
        header("Location: product.php?error=" . urlencode($e->getMessage()));
        exit();
    }
?> 