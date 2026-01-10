<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$skill_id = $_GET['skill_id'];
$requester_id = $_SESSION['user_id'];

$sql = "INSERT INTO requests (skill_id, requester_id)
        VALUES ('$skill_id', '$requester_id')";

mysqli_query($conn, $sql);

header("Location: ../skills/list.php");
exit();
