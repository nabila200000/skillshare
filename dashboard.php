<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include('includes/header.php');
include('includes/navbar.php');
?>

<style>
.dashboard-hero {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    color: #fff;
    border-radius: 16px;
    padding: 40px;
    margin-bottom: 40px;
}

.dashboard-card {
    transition: all 0.3s ease;
    border-radius: 16px;
}

.dashboard-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.12);
}

.dashboard-icon {
    font-size: 42px;
    margin-bottom: 15px;
}
</style>

<div class="container mt-4">

    <!-- HERO SECTION -->
    <div class="dashboard-hero text-center shadow">
        <h2 class="fw-bold mb-2">
            Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋
        </h2>
        <p class="mb-0 opacity-75">
            Manage your skills, learning requests, and conversations from one place
        </p>
    </div>

    <!-- DASHBOARD CARDS -->
    <div class="row g-4">

        <!-- ADD SKILL -->
        <div class="col-md-3">
            <div class="card dashboard-card h-100 text-center border-0">
                <div class="card-body">
                    <div class="dashboard-icon text-primary">
                        <i class="bi bi-plus-circle"></i>
                    </div>
                    <h5 class="fw-semibold">Add Skill</h5>
                    <p class="text-muted small">
                        Share a new skill you want to teach others
                    </p>
                    <a href="skills/add.php" class="btn btn-primary btn-sm px-4">
                        Add Skill
                    </a>
                </div>
            </div>
        </div>

        <!-- VIEW SKILLS -->
        <div class="col-md-3">
            <div class="card dashboard-card h-100 text-center border-0">
                <div class="card-body">
                    <div class="dashboard-icon text-success">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <h5 class="fw-semibold">Explore Skills</h5>
                    <p class="text-muted small">
                        Browse skills shared by other users
                    </p>
                    <a href="skills/list.php" class="btn btn-success btn-sm px-4">
                        View Skills
                    </a>
                </div>
            </div>
        </div>

        <!-- MANAGE REQUESTS -->
        <div class="col-md-3">
            <div class="card dashboard-card h-100 text-center border-0">
                <div class="card-body">
                    <div class="dashboard-icon text-warning">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <h5 class="fw-semibold">Manage Requests</h5>
                    <p class="text-muted small">
                        Accept, reject, and complete requests
                    </p>
                    <a href="requests/manage.php" class="btn btn-warning btn-sm px-4 text-white">
                        Manage
                    </a>
                </div>
            </div>
        </div>

        <!-- MY REQUESTS -->
        <div class="col-md-3">
            <div class="card dashboard-card h-100 text-center border-0">
                <div class="card-body">
                    <div class="dashboard-icon text-info">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h5 class="fw-semibold">My Requests</h5>
                    <p class="text-muted small">
                        Track skills you are learning
                    </p>
                    <a href="requests/my_requests.php" class="btn btn-info btn-sm px-4 text-white">
                        View Requests
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
