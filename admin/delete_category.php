<?php
    require_once("../includes/connect.php");

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
            
            // Check if category is being used by any products
            $check_query = "SELECT COUNT(*) FROM products WHERE category_id = :id";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bindParam(':id', $id);
            $check_stmt->execute();
            $count = $check_stmt->fetchColumn();

            if ($count > 0) {
                header("Location: categories.php?error=Category is in use by products");
                exit();
            }

            // Delete category
            $query = "DELETE FROM categories WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                header("Location: categories.php");
                exit();
            }
        } catch(PDOException $e) {
            header("Location: categories.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    }

    header("Location: categories.php");
    exit();
?>