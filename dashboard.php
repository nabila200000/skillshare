<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include('includes/header.php');
include('includes/navbar.php');
?>

<div class="container mt-4">

    <!-- Dashboard Header -->
    <div class="mb-4 text-center">
        <h2 class="fw-bold">
            Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋
        </h2>
        <p class="text-muted">
            Manage your skills, requests, and learning journey!
        </p>
    </div>

    <!-- Dashboard Cards -->
    <div class="row g-4">

        <!-- Add Skill -->
        <div class="col-md-3">
            <div class="card h-100 shadow-sm text-center border-0">
                <div class="card-body">
                    <div class="mb-3 text-primary fs-1">
                        <i class="bi bi-plus-circle"></i>
                    </div>
                    <h5 class="card-title">Add Skill</h5>
                    <p class="card-text text-muted">
                        Share a new skill you want to teach
                    </p>
                    <a href="skills/add.php" class="btn btn-primary btn-sm">
                        Go
                    </a>
                </div>
            </div>
        </div>

        <!-- View Skills -->
        <div class="col-md-3">
            <div class="card h-100 shadow-sm text-center border-0">
                <div class="card-body">
                    <div class="mb-3 text-success fs-1">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <h5 class="card-title">View Skills</h5>
                    <p class="card-text text-muted">
                        Browse skills shared by others
                    </p>
                    <a href="skills/list.php" class="btn btn-success btn-sm">
                        Go
                    </a>
                </div>
            </div>
        </div>

        <!-- Manage Requests -->
        <div class="col-md-3">
            <div class="card h-100 shadow-sm text-center border-0">
                <div class="card-body">
                    <div class="mb-3 text-warning fs-1">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h5 class="card-title">Manage Requests</h5>
                    <p class="card-text text-muted">
                        Accept or reject skill requests
                    </p>
                    <a href="requests/manage.php" class="btn btn-warning btn-sm">
                        Go
                    </a>
                </div>
            </div>
        </div>

        <!-- My Requests -->
        <div class="col-md-3">
            <div class="card h-100 shadow-sm text-center border-0">
                <div class="card-body">
                    <div class="mb-3 text-info fs-1">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h5 class="card-title">My Requests</h5>
                    <p class="card-text text-muted">
                        Track skills you requested to learn
                    </p>
                    <a href="requests/my_requests.php" class="btn btn-info btn-sm text-white">
                        View
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
