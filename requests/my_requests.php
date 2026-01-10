<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$uid = $_SESSION['user_id'];

$sql = "SELECT 
            requests.id,
            requests.status,
            requests.message,
            skills.skill_name
        FROM requests
        JOIN skills ON requests.skill_id = skills.id
        WHERE requests.requester_id = '$uid'
        ORDER BY requests.id DESC";

$result = mysqli_query($conn, $sql);

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container mt-4">
    <h3 class="mb-4">My Skill Requests</h3>

    <?php if (mysqli_num_rows($result) > 0) { ?>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

            <?php
            // 🔴 Unread message count for this request
            $unread_sql = "SELECT COUNT(*) AS total
                           FROM messages
                           WHERE request_id = '{$row['id']}'
                             AND sender_id != '$uid'
                             AND is_read = 0";
            $unread = mysqli_fetch_assoc(mysqli_query($conn, $unread_sql));
            ?>

            <div class="card shadow-sm mb-3">
                <div class="card-body">

                    <h5 class="card-title">
                        <?php echo htmlspecialchars($row['skill_name']); ?>
                    </h5>

                    <!-- Status -->
                    <p>
                        Status:
                        <?php if ($row['status'] == 'pending') { ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php } elseif ($row['status'] == 'accepted') { ?>
                            <span class="badge bg-success">Accepted</span>
                        <?php } else { ?>
                            <span class="badge bg-secondary">Completed</span>
                        <?php } ?>
                    </p>

                    <!-- Teacher message -->
                    <?php if (!empty($row['message'])) { ?>
                        <div class="alert alert-secondary p-2">
                            <strong>Message from teacher:</strong><br>
                            <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                        </div>
                    <?php } ?>

                    <!-- Chat (only if accepted) -->
                    <?php if ($row['status'] == 'accepted') { ?>
                        <a href="../requests/chat.php?request_id=<?php echo $row['id']; ?>"
                           class="btn btn-outline-primary btn-sm">
                            Open Chat
                            <?php if ($unread['total'] > 0) { ?>
                                <span class="badge bg-danger ms-1">
                                    <?php echo $unread['total']; ?> New
                                </span>
                            <?php } ?>
                        </a>
                    <?php } ?>

                </div>
            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="alert alert-info">
            You have not requested any skills yet.
        </div>

    <?php } ?>

    <div class="text-center mt-4">
        <a href="../dashboard.php" class="btn btn-secondary">
            ← Back to Dashboard
        </a>
    </div>
</div>

</body>
</html>
