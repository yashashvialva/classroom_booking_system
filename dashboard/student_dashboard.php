<?php
session_start();
include('../includes/db_connect.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student'){
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// get student's college
$stmt = $conn->prepare("SELECT college_id FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($college_id);
$stmt->fetch();
$stmt->close();

// handle booking request
if(isset($_POST['book'])){
    $room_id = $_POST['classroom_id'];
    $reason = $_POST['reason'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $stmt = $conn->prepare("INSERT INTO bookings (room_id, student_id, reason, date, start_time, end_time, status) VALUES (?,?,?,?,?,?, 'pending')");
    $stmt->bind_param("isssss", $room_id, $user_id, $reason, $date, $start_time, $end_time);
    $stmt->execute();
    $stmt->close();
    $msg = "Booking request sent!";
}

// get all classrooms for this college
$classrooms = $conn->prepare("SELECT * FROM classrooms WHERE college_id=?");
$classrooms->bind_param("i",$college_id);
$classrooms->execute();
$result = $classrooms->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f8;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 20px auto;
        }

        h2, h3 {
            color: #333;
        }

        .msg {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            font-weight: bold;
            color: #155724;
            background-color: #d4edda;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 12px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2196F3;
            color: white;
        }

        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }

        form {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        input[type="date"], input[type="time"], input[type="text"] {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        input[type="submit"] {
            padding: 10px 20px;
            background-color: #2196F3;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #1976D2;
        }

        a.logout {
            float: right;
            background-color: #f44336;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
        }

        a.logout:hover {
            background-color: #d32f2f;
        }

        .status-text {
            font-size: 13px;
            line-height: 1.4;
            color: #333;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome Student <a class="logout" href="../home.php">Logout</a></h2>

    <?php if(isset($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <h3>Available Classrooms / Labs</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Room Name</th>
            <th>Type</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php
        while($row = $result->fetch_assoc()){
            $room_id = $row['id'];

            // fetch all approved bookings for this room
            $stmt = $conn->prepare("SELECT * FROM bookings WHERE room_id=? AND status='approved' ORDER BY date ASC, start_time ASC");
            $stmt->bind_param("i", $room_id);
            $stmt->execute();
            $bookings = $stmt->get_result();

            $status_text = "";
            while($b = $bookings->fetch_assoc()){
                $who = ($b['student_id'] == $user_id) ? "<b>Your booking</b>" : "Booked by someone";
                $status_text .= "<span class='status-text'>$who for " . date("d/m/Y", strtotime($b['date'])) . " from " . date("h:i A", strtotime($b['start_time'])) . " – " . date("h:i A", strtotime($b['end_time'])) . "</span><br>";
            }
            if($status_text == "") $status_text = "<span class='status-text'>Available</span>";

            echo "<tr>
                <td>{$row['id']}</td>
                <td>" . htmlspecialchars($row['room_name']) . "</td>
                <td>" . htmlspecialchars($row['type']) . "</td>
                <td>$status_text</td>
                <td>
                    <form method='POST'>
                        <input type='hidden' name='classroom_id' value='{$row['id']}'>
                        <input type='date' name='date' required>
                        <input type='time' name='start_time' required>
                        <input type='time' name='end_time' required>
                        <input type='text' name='reason' placeholder='Why book?' required>
                        <input type='submit' name='book' value='Book'>
                    </form>
                </td>
            </tr>";
            $stmt->close();
        }
        ?>
    </table>
</div>
</body>
</html>
