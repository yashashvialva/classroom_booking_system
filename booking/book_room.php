<?php
session_start();
include('../includes/db_connect.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty'){
    header("Location: ../index.php");
    exit;
}
if(isset($_POST['book'])){
    $faculty_id = $_SESSION['user_id'];
    $room_id = $_POST['room_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $reason = $_POST['reason'];
    $date = date('Y-m-d'); 
    $status = "pending";

    $query = "INSERT INTO bookings (room_id, faculty_id, date, start_time, end_time, reason, status) 
              VALUES ('$room_id', '$faculty_id', '$date', '$start_time', '$end_time', '$reason', '$status')";

    if(mysqli_query($conn, $query)){
        echo "<p style='color:green;'>Booking request sent successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
    }
}
?>
<a href="../dashboard/faculty_dashboard.php">Back to Dashboard</a>
