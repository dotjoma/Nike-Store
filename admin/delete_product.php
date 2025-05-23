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

    if (isset($_GET['id'])) {
        try {
            $id = $_GET['id'];
            
            // First get the product name for logging
            $get_product_query = "SELECT name FROM products WHERE id = :id";
            $get_product_stmt = $conn->prepare($get_product_query);
            $get_product_stmt->bindParam(':id', $id);
            $get_product_stmt->execute();
            $product = $get_product_stmt->fetch(PDO::FETCH_ASSOC);
            
            // First check if the product exists
            $check_query = "SELECT id FROM products WHERE id = :id";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':id', $id);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() == 0) {
                header("Location: product.php?error=Product not found");
                exit();
            }

            // Delete the product
            $query = "DELETE FROM products WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                // Log the activity
                logActivity($conn, $_SESSION['user_id'], "Delete Product", "Deleted product: " . $product['name'] . " (ID: " . $id . ")");
                
                header("Location: product.php?success=Product deleted successfully");
                exit();
            } else {
                header("Location: product.php?error=Failed to delete product");
                exit();
            }
        } catch(PDOException $e) {
            header("Location: product.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    }

    // If no ID is provided, redirect to products page
    header("Location: product.php");
    exit();
?> 