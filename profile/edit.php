<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$uid = $_SESSION['user_id'];
$msg = "";

if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $bio = $_POST['bio'];
    $phone = $_POST['phone'];

    // Photo upload
    if (!empty($_FILES['photo']['name'])) {
        $photoName = time() . '_' . $_FILES['photo']['name'];
        $target = "../uploads/profiles/" . $photoName;
        move_uploaded_file($_FILES['photo']['tmp_name'], $target);

        $sql = "UPDATE users 
                SET name='$name', bio='$bio', phone='$phone', profile_photo='$photoName'
                WHERE id='$uid'";
    } else {
        $sql = "UPDATE users 
                SET name='$name', bio='$bio', phone='$phone'
                WHERE id='$uid'";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['user_name'] = $name;
        $msg = "Profile updated successfully";
    }
}

$result = mysqli_query($conn, "SELECT name, bio, phone, profile_photo FROM users WHERE id='$uid'");
$user = mysqli_fetch_assoc($result);

include('../includes/header.php');
include('../includes/navbar.php');
?>

<div class="container mt-4">
    <h3 class="mb-3 text-center">Edit Profile</h3>

    <div class="card shadow-sm mx-auto" style="max-width:600px;">
        <div class="card-body">

            <?php if ($msg) { ?>
                <div class="alert alert-success"><?php echo $msg; ?></div>
            <?php } ?>

            <form method="POST" enctype="multipart/form-data">

                <label class="form-label">Name</label>
                <input type="text" name="name"
                       value="<?php echo htmlspecialchars($user['name']); ?>"
                       class="form-control mb-3" required>

                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control mb-3"
                          rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>

                <label class="form-label">Phone</label>
                <input type="text" name="phone"
                       value="<?php echo htmlspecialchars($user['phone']); ?>"
                       class="form-control mb-3">

                <label class="form-label">Profile Photo</label>
                <input type="file" name="photo" class="form-control mb-3">

                <div class="text-center">
                    <button name="update" class="btn btn-success btn-sm">Save Changes</button>
                    <a href="profile.php" class="btn btn-secondary btn-sm ms-2">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>
