<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$uid = $_SESSION['user_id'];

$sql = "SELECT skills.id, skills.skill_name, skills.description, users.name
        FROM skills
        JOIN users ON skills.user_id = users.id
        WHERE skills.user_id != '$uid'";

$result = mysqli_query($conn, $sql);
?>

<?php include('../includes/header.php'); ?>
<?php include('../includes/navbar.php'); ?>

<div class="container mt-4">
    <h3 class="mb-4">Available Skills</h3>

    <div class="row">
        <?php if (mysqli_num_rows($result) > 0) { ?>

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <?php echo $row['skill_name']; ?>
                            </h5>

                            <p class="card-text">
                                <?php echo $row['description']; ?>
                            </p>

                            <p class="text-muted small mt-auto">
                                Shared by <?php echo $row['name']; ?>
                            </p>

                            <a href="../requests/send.php?skill_id=<?php echo $row['id']; ?>"
                               class="btn btn-outline-primary btn-sm mt-2">
                                Request Skill
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>

        <?php } else { ?>
            <div class="col-12">
                <div class="alert alert-info">
                    No skills available right now.
                </div>
            </div>
        <?php } ?>
    </div>

    <a href="../dashboard.php" class="btn btn-link mt-3">
        ← Back to Dashboard
    </a>
</div>

</body>
</html>
