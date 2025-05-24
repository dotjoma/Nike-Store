<?php
    require_once("../includes/connect.php");
    session_start();

    // Check if user is logged in and is an admin
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }

    // Handle user deletion
    if(isset($_GET['delete'])) {
        try {
            $hashed_id = $_GET['delete'];
            
            // Get the actual ID from the database using the hashed ID
            $id_query = "SELECT id FROM users WHERE MD5(id) = ?";
            $id_stmt = $conn->prepare($id_query);
            $id_stmt->execute([$hashed_id]);
            $user_id = $id_stmt->fetchColumn();
            
            // Don't allow deleting your own account
            if($user_id == $_SESSION['user_id']) {
                $error = "You cannot delete your own account";
            } else {
                $query = "DELETE FROM users WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$user_id]);
                $success = "User deleted successfully";
            }
        } catch(PDOException $e) {
            $error = "Error deleting user: " . $e->getMessage();
        }
    }

    // Fetch all users with pagination
    $items_per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $items_per_page;

    try {
        // Get total records for pagination
        $count_query = "SELECT COUNT(*) FROM users";
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->execute();
        $total_records = $count_stmt->fetchColumn();
        $total_pages = ceil($total_records / $items_per_page);

        // Fetch users with pagination
        $query = "SELECT * FROM users ORDER BY id DESC LIMIT :offset, :limit";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="../assets/favicon.ico" />
        <title>User Management - Admin Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <!-- SweetAlert2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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
                        <h1 class="mt-4">User Management</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Users</li>
                        </ol>

                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-users me-1"></i>
                                    Users List
                                </div>
                                <a href="add_user.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add New User
                                </a>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($users as $user): ?>
                                            <tr class="<?php echo $user['id'] == $_SESSION['user_id'] ? 'table-primary' : ''; ?>">
                                                <td><?php echo $user['id']; ?></td>
                                                <td>
                                                    <?php if($user['image']): ?>
                                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($user['image']); ?>" 
                                                             class="rounded-circle me-2" 
                                                             style="width: 30px; height: 30px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($user['name']); ?>
                                                    <?php if($user['id'] == $_SESSION['user_id']): ?>
                                                        <span class="badge bg-info ms-2">You</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="edit_user.php?id=<?php echo md5($user['id']); ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="javascript:void(0)" 
                                                           class="btn btn-danger btn-sm delete-user"
                                                           data-user-id="<?php echo md5($user['id']); ?>"
                                                           data-user-name="<?php echo htmlspecialchars($user['name']); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
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
                                                    <a class="page-link" href="?page=<?php echo $page-1; ?>">Previous</a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if($page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Add click event listener to all delete buttons
                document.querySelectorAll('.delete-user').forEach(button => {
                    button.addEventListener('click', function() {
                        const userId = this.getAttribute('data-user-id');
                        const userName = this.getAttribute('data-user-name');
                        
                        Swal.fire({
                            title: 'Are you sure?',
                            text: `Do you want to delete user "${userName}"?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = `?delete=${userId}`;
                            }
                        });
                    });
                });
            });
        </script>
    </body>
</html>