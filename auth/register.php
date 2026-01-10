<?php
session_start();
include('../config/db.php');

$msg = "";
$error = "";

if (isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check email exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email already registered";
    } else {

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password)
                VALUES ('$name', '$email', '$hashed')";

        if (mysqli_query($conn, $sql)) {
            $msg = "Registration successful. You can login now.";
        } else {
            $error = "Something went wrong";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<?php include('../includes/header.php'); ?>
<?php include('../includes/navbar.php'); ?>

<div class="container mt-4">
<h3>Create Account</h3>

<?php if ($msg) echo "<p style='color:green;'>$msg</p>"; ?>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST" class="card p-4 shadow-sm">
    <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
    <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

    <button class="btn btn-primary" name="register">Register</button>
</form>

<p class="mt-3">
    Already have an account? <a href="login.php">Login</a>
</p>
</div>

</body>
</html>
