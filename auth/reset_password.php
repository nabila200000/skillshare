<?php
include('../config/db.php');

$msg = "";
$token = $_GET['token'] ?? "";

$sql = "SELECT * FROM users 
        WHERE reset_token='$token' 
        AND token_expiry > NOW()";

$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("Invalid or expired token");
}

if (isset($_POST['reset'])) {
    $newpass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $update = "UPDATE users 
               SET password='$newpass', 
                   reset_token=NULL, 
                   token_expiry=NULL 
               WHERE id='{$user['id']}'";

    if (mysqli_query($conn, $update)) {
        $msg = "Password reset successful. <a href='login.php'>Login</a>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
<div class="col-md-5 mx-auto card p-4 shadow-sm">
<h4 class="mb-3">Reset Password</h4>

<?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>

<form method="POST">
<input type="password" name="password" class="form-control mb-3" placeholder="New Password" required>
<button class="btn btn-success w-100" name="reset">Reset Password</button>
</form>
</div>
</div>

</body>
</html>
