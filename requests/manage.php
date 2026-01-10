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
            skills.skill_name,
            users.name AS requester
        FROM requests
        JOIN skills ON requests.skill_id = skills.id
        JOIN users ON requests.requester_id = users.id
        WHERE skills.user_id = '$uid'
          AND requests.status IN ('pending','accepted')
        ORDER BY requests.id DESC";

$result = mysqli_query($conn, $sql);

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container mt-4">
    <h3 class="mb-4">Manage Skill Requests</h3>

    <?php if (mysqli_num_rows($result) > 0) { ?>

        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Requester</th>
                    <th>Skill</th>
                    <th>Status</th>
                    <th width="45%">Action / Message</th>
                </tr>
            </thead>
            <tbody>

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                <?php
                // 🔴 Unread message count
                $unread_sql = "SELECT COUNT(*) AS total
                               FROM messages
                               WHERE request_id = '{$row['id']}'
                                 AND sender_id != '$uid'
                                 AND is_read = 0";
                $unread = mysqli_fetch_assoc(mysqli_query($conn, $unread_sql));
                ?>

                <tr>
                    <td><?php echo htmlspecialchars($row['requester']); ?></td>
                    <td><?php echo htmlspecialchars($row['skill_name']); ?></td>

                    <td>
                        <?php if ($row['status'] == 'pending') { ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php } else { ?>
                            <span class="badge bg-success">Accepted</span>
                        <?php } ?>
                    </td>

                    <td>

                        <!-- 🟡 PENDING REQUEST -->
                        <?php if ($row['status'] == 'pending') { ?>

                            <form method="POST" action="update.php" class="mb-2">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="status" value="accepted">

                                <textarea name="message"
                                          class="form-control mb-2"
                                          rows="2"
                                          placeholder="Contact info / meeting link / schedule"
                                          required></textarea>

                                <button class="btn btn-success btn-sm">
                                    Accept with Message
                                </button>

                                <a href="update.php?id=<?php echo $row['id']; ?>&status=rejected"
                                   class="btn btn-danger btn-sm ms-2">
                                    Reject
                                </a>
                            </form>

                        <?php } ?>

                        <!-- 🟢 ACCEPTED REQUEST -->
                        <?php if ($row['status'] == 'accepted') { ?>

                            <?php if (!empty($row['message'])) { ?>
                                <div class="alert alert-info p-2 mb-2">
                                    <strong>Message / Contact:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                </div>
                            <?php } ?>

                            <a href="chat.php?request_id=<?php echo $row['id']; ?>"
                               class="btn btn-outline-primary btn-sm">
                                Open Chat
                                <?php if ($unread['total'] > 0) { ?>
                                    <span class="badge bg-danger ms-1">
                                        <?php echo $unread['total']; ?> New
                                    </span>
                                <?php } ?>
                            </a>

                            <a href="update.php?id=<?php echo $row['id']; ?>&status=completed"
                               class="btn btn-primary btn-sm ms-2">
                                Mark as Completed
                            </a>

                        <?php } ?>

                    </td>
                </tr>

            <?php } ?>

            </tbody>
        </table>

    <?php } else { ?>

        <div class="alert alert-info">
            No pending or accepted requests at the moment.
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
