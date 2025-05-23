<?php
function logActivity($conn, $user_id, $action, $details = '') {
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        $query = "INSERT INTO activity_logs (user_id, action, details, ip_address) 
                 VALUES (:user_id, :action, :details, :ip_address)";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':action' => $action,
            ':details' => $details,
            ':ip_address' => $ip_address
        ]);
        
        return true;
    } catch(PDOException $e) {
        // Log error silently
        return false;
    }
}
?>