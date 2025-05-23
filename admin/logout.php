<?php
    require_once("../includes/connect.php");
    require_once("../includes/activity_logger.php");
    session_start();
    
    // Log the logout before destroying the session
    if(isset($_SESSION['user_id'])) {
        logActivity($conn, $_SESSION['user_id'], "Logout", "Admin user logged out");
    }
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page with a success message
    header("Location: ../login.php?logout=success");
    exit();
?>