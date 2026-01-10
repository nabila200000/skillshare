<?php
include('../config/db.php');
$msg = "";

if (isset($_POST['submit'])) {
    $email = $_POST['email'];

    $token = bin2hex(random_bytes(32));
    $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $sql = "UPDATE users 
            SET reset_token='$token', token_expiry='$expiry' 
            WHERE email='$email'";

    if (mysqli_query($conn, $sql) && mysqli_affected_rows($conn) > 0) {
        $resetLink = "http://localhost/skillshare/auth/reset_password.php?token=$token";
        $msg = "Password reset link generated:<br><a href='$resetLink'>$resetLink</a>";
    } else {
        $msg = "Email not found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
<div class="col-md-5 mx-auto card p-4 shadow-sm">
<h4 class="mb-3">Forgot Password</h4>

<?php if($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>

<form method="POST">
<input type="email" name="email" class="form-control mb-3" placeholder="Enter your email" required>
<button class="btn btn-primary w-100" name="submit">Generate Reset Link</button>
</form>

<a href="login.php" class="d-block mt-3">Back to Login</a>
</div>
</div>

</body>
</html>
