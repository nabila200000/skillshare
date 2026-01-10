<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$request_id = $_GET['request_id'];

/* ===============================
   1️⃣ Request access check
================================ */
$check = mysqli_query($conn, "
    SELECT requests.*, skills.skill_name 
    FROM requests 
    JOIN skills ON requests.skill_id = skills.id
    WHERE requests.id = '$request_id'
      AND (requests.requester_id = '$user_id' OR skills.user_id = '$user_id')
      AND requests.status = 'accepted'
");

if (mysqli_num_rows($check) == 0) {
    die("Access denied");
}

$request = mysqli_fetch_assoc($check);

/* ===============================
   2️⃣ Send message
================================ */
if (isset($_POST['send'])) {
    $message = trim($_POST['message']);

    if ($message !== '') {
        mysqli_query($conn, "
            INSERT INTO messages (request_id, sender_id, message, is_read)
            VALUES ('$request_id', '$user_id', '$message', 0)
        ");
    }
}

/* ===============================
   3️⃣ Mark incoming messages as read
================================ */
mysqli_query($conn, "
    UPDATE messages
    SET is_read = 1
    WHERE request_id = '$request_id'
      AND sender_id != '$user_id'
");

/* ===============================
   4️⃣ Fetch messages
================================ */
$messages = mysqli_query($conn, "
    SELECT messages.*, users.name
    FROM messages
    JOIN users ON messages.sender_id = users.id
    WHERE messages.request_id = '$request_id'
    ORDER BY messages.created_at ASC
");

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container mt-4">
    <h4 class="mb-3">
        Conversation: <?php echo htmlspecialchars($request['skill_name']); ?>
    </h4>

    <div class="card shadow-sm mb-3">
        <div class="card-body" style="max-height:350px; overflow-y:auto;">

            <?php while ($row = mysqli_fetch_assoc($messages)) { ?>

                <div class="mb-2">
                    <strong><?php echo htmlspecialchars($row['name']); ?>:</strong>

                    <div class="alert alert-secondary p-2 mb-1">
                        <?php echo nl2br(htmlspecialchars($row['message'])); ?>

                        <!-- ✅ READ / UNREAD INDICATOR -->
                        <?php if ($row['sender_id'] != $user_id && $row['is_read'] == 0) { ?>
                            <span class="badge bg-warning text-dark ms-2">Unread</span>
                        <?php } ?>

                        <?php if ($row['sender_id'] == $user_id && $row['is_read'] == 1) { ?>
                            <span class="badge bg-success ms-2">Seen</span>
                        <?php } ?>
                    </div>
                </div>

            <?php } ?>

        </div>
    </div>

    <form method="POST">
        <textarea name="message"
                  class="form-control mb-2"
                  rows="3"
                  placeholder="Type your message..."
                  required></textarea>

        <button name="send" class="btn btn-primary btn-sm">
            Send Message
        </button>

        <a href="../dashboard.php" class="btn btn-secondary btn-sm ms-2">
            Back
        </a>
    </form>
</div>

</body>
</html>
