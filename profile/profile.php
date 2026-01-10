<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$uid = $_SESSION['user_id'];

$sql = "SELECT name, email, bio, phone FROM users WHERE id='$uid'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container mt-4">
    <h3 class="mb-4 text-center">My Profile</h3>

    <div class="card shadow-sm mx-auto text-center" style="max-width:600px;">
        <div class="card-body">

            <img src="../uploads/profiles/<?php echo !empty($user['profile_photo']) 
    ? htmlspecialchars($user['profile_photo']) 
    : 'default.png'; ?>"
     class="rounded-circle mb-3"
     width="120"
     height="120"
     alt="Profile Photo">

            <h5><?php echo htmlspecialchars($user['name']); ?></h5>
            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>

            <p>
                <?php echo $user['bio']
                    ? nl2br(htmlspecialchars($user['bio']))
                    : 'No bio added yet'; ?>
            </p>

            <p><strong>Phone:</strong>
                <?php echo $user['phone'] ?: 'Not provided'; ?>
            </p>

            <div class="mt-3">
                <a href="edit.php" class="btn btn-primary btn-sm">Edit Profile</a>
                <a href="../dashboard.php" class="btn btn-secondary btn-sm ms-2">
                    Back to Dashboard
                </a>
            </div>

        </div>
    </div>
</div>


</body>
</html>
