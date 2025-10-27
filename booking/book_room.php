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
    $date = date('Y-m-d'); // booking for today (you can modify to accept chosen date)

    // Insert booking as pending
    $stmt = $conn->prepare("INSERT INTO bookings (room_id, faculty_id, date, start_time, end_time, reason, status) VALUES (?,?,?,?,?,?,?)");
    $status = 'pending';
    $stmt->bind_param("iisssss", $room_id, $faculty_id, $date, $start_time, $end_time, $reason, $status);

    if($stmt->execute()){
        echo "<p style='color:green;'>Booking request sent!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
?>
<a href="../dashboard/faculty_dashboard.php">Back to Dashboard</a>
