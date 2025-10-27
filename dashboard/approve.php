<?php
include('../includes/db_connect.php');
$id = $_GET['id'];
$action = $_GET['action'];

if(in_array($action, ['approved','rejected'])){
    $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
    $stmt->bind_param("si", $action, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: admin_dashboard.php");
exit;
?>
