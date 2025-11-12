<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$result = $conn->query("SELECT college_id FROM users WHERE id=$user_id");
$row = $result->fetch_assoc();
$college_id = $row['college_id'];

if (isset($_POST['book'])) {
    $room_id = $_POST['classroom_id'];
    $reason = $_POST['reason'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $conn->query("INSERT INTO bookings (room_id, student_id, reason, date, start_time, end_time, status) 
                  VALUES ('$room_id', '$user_id', '$reason', '$date', '$start_time', '$end_time', 'pending')");
    $msg = "Booking request sent!";
}

$classrooms = $conn->query("SELECT * FROM classrooms WHERE college_id=$college_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 95%;
            margin: 20px auto;
        }
        h2, h3 { color: #ffe600ff; }
        .msg {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            background-color: #ddff03ff;
            color: #155724;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #fde006ff;
            color: #ffe600;
        }
        th { background-color: black; }
        tr:hover { background-color: #ffe60033; }
        form { 
            background-color: black; 
            padding: 15px; 
            border-radius: 10px; 
            box-shadow: 0 0 12px rgba(0,0,0,0.05); 
            display: flex; 
            flex-wrap: wrap; 
            gap: 5px; 
            justify-content: center;
        }
        input, select, button {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid yellow;
            font-size: 14px;
        }
        button {
            background-color: orange;
            color: black;
            border: none;
            cursor: pointer;
        }
        button:hover { background-color: #ffbf00; }
        a { text-decoration: none; color: #007BFF; }
        a:hover { text-decoration: underline; }
        .logout {
            float: right;
            background-color: #cad70fff;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
        }
        .logout:hover { background-color: #ffff17ff; }
        .status-text { display:block; font-size:12px; margin:2px 0; }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome Student <a href="../home.php" class="logout">Logout</a></h2>

    <?php if (isset($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <h3>Available Classrooms / Labs</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Room Name</th>
            <th>Type</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while ($row = $classrooms->fetch_assoc()): ?>
            <?php
            $room_id = $row['id'];
            $bookings = $conn->query("SELECT * FROM bookings WHERE room_id=$room_id AND status='approved' ORDER BY date, start_time");

            if ($bookings->num_rows > 0) {
                $status_text = "";
                while ($b = $bookings->fetch_assoc()) {
                    $who = ($b['student_id'] == $user_id) ? "<b>Your booking</b>" : "Booked by someone";
                    $status_text .= "<span class='status-text'>$who for " . date("d/m/Y", strtotime($b['date'])) . " (" . $b['start_time'] . " - " . $b['end_time'] . ")</span>";
                }
            } else {
                $status_text = "<span class='status-text'>Available</span>";
            }
            ?>
            <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['room_name'] ?></td>
            <td><?= $row['type'] ?></td>
            <td><?= $status_text ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="classroom_id" value="<?= $row['id'] ?>">
                    <input type="date" name="date" required>
                    <input type="time" name="start_time" required>
                    <input type="time" name="end_time" required>
                    <input type="text" name="reason" placeholder="Why book?" required>
                    <button type="submit" name="book">Book</button>
        </form>
</td>
 
        </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
