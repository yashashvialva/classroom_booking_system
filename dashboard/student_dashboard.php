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
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #121212; /* Deep dark gray */
            color: #e0e0e0; /* Soft white text */
            margin: 0;
            padding: 0;
        }

        .container {
            width: 95%;
            margin: 20px auto;
        }

        h2, h3,h1 {
            color: #ffcc66;
        }

        .msg {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            background-color: #1f3b1f;
            color: #b7ffb7;
            border: 1px solid #2ecc71;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #1e1e1e;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #333;
        }

        th {
            background-color: #2a2a2a;
            color: #ffd966;
        }

        tr:hover {
            background-color: #2f2f2f;
        }

        .status-text {
            display: block;
            font-size: 12px;
            margin: 2px 0;
            color: #bfbfbf;
        }

        form {
            background-color: #1b1b1b;
            padding: 15px;
            border-radius: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.05);
        }

        input, select, button {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #555;
            background-color: #2b2b2b;
            color: #f1f1f1;
            font-size: 14px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #ffcc66;
            box-shadow: 0 0 5px #ffcc66;
        }

        button {
            background-color: #ffcc66;
            color: #1a1a1a;
            border: none;
            font-weight: bold;
            transition: all 0.3s;
        }

        button:hover {
            background-color: #ffd77a;
            transform: scale(1.03);
        }

        a {
            text-decoration: none;
            color: #66b3ff;
        }

        a:hover {
            text-decoration: underline;
        }

        .logout {
            float: right;
            background-color: #ff6666;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            transition: 0.3s;
        }

        .logout:hover {
            background-color: #ff8585;
        }

        /* Header (title + mascot image side by side) */
        .mascot-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 10px;
        }

        .mascot-header h3 {
            font-size: 1.8em;
            margin: 0;
        }

        .mascot-header img {
            width: 200px;
            height: auto;
            
        }

        

    </style>
</head>
<body>
<div class="container">
    <h2>Welcome Student <a href="../home.php" class="logout">Logout</a></h2>

    <?php if (isset($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <!-- Header: Title + Image Side by Side -->
    <div class="mascot-header">
        <h1>Available Classrooms /Labs</h1>
        <img src="imagestudent.png" alt="Student Mascot">
    </div>

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
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
