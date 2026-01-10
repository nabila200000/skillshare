<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id     = $_REQUEST['id'] ?? '';
$status = $_REQUEST['status'] ?? '';
$msg    = $_POST['message'] ?? '';

$allowed = ['accepted', 'rejected', 'completed'];

if ($id && in_array($status, $allowed)) {

    $uid = $_SESSION['user_id'];

    if ($status == 'accepted') {
        $sql = "UPDATE requests r
                JOIN skills s ON r.skill_id = s.id
                SET r.status='accepted',
                    r.message='$msg'
                WHERE r.id='$id'
                AND s.user_id='$uid'";
    } else {
        $sql = "UPDATE requests r
                JOIN skills s ON r.skill_id = s.id
                SET r.status='$status'
                WHERE r.id='$id'
                AND s.user_id='$uid'";
    }

    mysqli_query($conn, $sql);
}

header("Location: manage.php");
exit();
