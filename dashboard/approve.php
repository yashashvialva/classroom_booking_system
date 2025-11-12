<?php
include('../includes/db_connect.php');
$id = $_GET['id'];
$action = $_GET['action'];
if ($action=='approved'||$action=='rejected') {
    $sql="UPDATE bookings SET status='$action' WHERE id=$id";
    mysqli_query($conn, $sql);
}
header("Location: admin_dashboard.php");
exit;
?>