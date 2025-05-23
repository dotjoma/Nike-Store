<?php
    require_once("../includes/connect.php");
    session_start();

    // Check if user is logged in and is an admin
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }

    // Pagination settings
    $items_per_page = 20;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $items_per_page;

    // Filter settings
    $user_filter = isset($_GET['user']) ? $_GET['user'] : '';
    $action_filter = isset($_GET['action']) ? $_GET['action'] : '';
    $date_filter = isset($_GET['date']) ? $_GET['date'] : '';

    try {
        // Build the base query
        $query = "SELECT al.*, u.name as user_name 
                 FROM activity_logs al 
                 LEFT JOIN users u ON al.user_id = u.id 
                 WHERE 1=1";
        $params = [];

        // Add filters
        if($user_filter) {
            $query .= " AND u.name LIKE ?";
            $params[] = "%$user_filter%";
        }
        if($action_filter) {
            $query .= " AND al.action LIKE ?";
            $params[] = "%$action_filter%";
        }
        if($date_filter) {
            $query .= " AND DATE(al.created_at) = ?";
            $params[] = $date_filter;
        }

        // Get total records for pagination
        $count_query = str_replace("al.*, u.name as user_name", "COUNT(*) as total", $query);
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->execute($params);
        $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total_records / $items_per_page);

        // Add sorting and pagination
        $query .= " ORDER BY al.created_at DESC LIMIT :offset, :limit";
        $params[':offset'] = $offset;
        $params[':limit'] = $items_per_page;

        // Execute the main query
        $stmt = $conn->prepare($query);
        foreach($params as $key => $value) {
            if($key === ':offset' || $key === ':limit') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
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
        <title>Activity Log - Admin Dashboard</title>
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
                        <h1 class="mt-4">Activity Log</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Activity Log</li>
                        </ol>

                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <!-- Filters -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-filter me-1"></i>
                                Filters
                            </div>
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-4">
                                        <label for="user" class="form-label">User</label>
                                        <input type="text" class="form-control" id="user" name="user" 
                                               value="<?php echo htmlspecialchars($user_filter); ?>" 
                                               placeholder="Search by user name">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="action" class="form-label">Action</label>
                                        <input type="text" class="form-control" id="action" name="action" 
                                               value="<?php echo htmlspecialchars($action_filter); ?>" 
                                               placeholder="Search by action">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="date" class="form-label">Date</label>
                                        <input type="date" class="form-control" id="date" name="date" 
                                               value="<?php echo htmlspecialchars($date_filter); ?>">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                                        <a href="activity_log.php" class="btn btn-secondary">Clear Filters</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Activity Log Table -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-history me-1"></i>
                                Activity Log
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Details</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($logs as $log): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                                <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                                <td><?php echo htmlspecialchars($log['details']); ?></td>
                                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <!-- Pagination -->
                                <?php if($total_pages > 1): ?>
                                    <nav aria-label="Page navigation" class="mt-4">
                                        <ul class="pagination justify-content-center">
                                            <?php if($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&user=<?php echo urlencode($user_filter); ?>&action=<?php echo urlencode($action_filter); ?>&date=<?php echo urlencode($date_filter); ?>">Previous</a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>&user=<?php echo urlencode($user_filter); ?>&action=<?php echo urlencode($action_filter); ?>&date=<?php echo urlencode($date_filter); ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if($page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&user=<?php echo urlencode($user_filter); ?>&action=<?php echo urlencode($action_filter); ?>&date=<?php echo urlencode($date_filter); ?>">Next</a>
                                                </li>
                                            <?php endif; ?>
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
    </body>
</html>