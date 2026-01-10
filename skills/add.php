<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$msg = "";

if (isset($_POST['add_skill'])) {
    $skill = $_POST['skill'];
    $desc  = $_POST['description'];
    $uid   = $_SESSION['user_id'];

    $sql = "INSERT INTO skills (user_id, skill_name, description)
            VALUES ('$uid', '$skill', '$desc')";

    if (mysqli_query($conn, $sql)) {
        $msg = "Skill added successfully";
    }
}
?>

<?php include('../includes/header.php'); ?>
<?php include('../includes/navbar.php'); ?>

<div class="container mt-4">
    <h3 class="mb-3">Add Skill</h3>

    <?php if ($msg != "") { ?>
        <div class="alert alert-success">
            <?php echo $msg; ?>
        </div>
    <?php } ?>

    <form method="POST" class="card p-4 shadow-sm">
        <label class="form-label">Skill Name</label>
        <input type="text" name="skill" class="form-control mb-3" required>

        <label class="form-label">Description</label>
        <textarea name="description" class="form-control mb-3" rows="4" required></textarea>

        <button type="submit" name="add_skill" class="btn btn-primary">
            Add Skill
        </button>
    </form>

    <a href="../dashboard.php" class="btn btn-link mt-3">
        ← Back to Dashboard
    </a>
</div>

</body>
</html>
