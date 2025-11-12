<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty') {
    header("Location: ../index.php");
    exit;
}

$faculty_id = $_SESSION['user_id'];

$query = "SELECT college_id FROM users WHERE id='$faculty_id'";
$result = mysqli_query($conn, $query) or die("Error fetching college!");
$row = mysqli_fetch_array($result); 
$college_id = $row['college_id'];


if (isset($_POST['book'])) {
    $room_id = $_POST['classroom_id'];
    $reason = $_POST['reason'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $sql = "INSERT INTO bookings (room_id, faculty_id, reason, date, start_time, end_time, status)
            VALUES ('$room_id', '$faculty_id', '$reason', '$date', '$start_time', '$end_time', 'pending')";
    mysqli_query($conn, $sql) or die("Booking failed!");

    $msg = "Booking request sent!";
}

$classroom_query = "SELECT * FROM classrooms WHERE college_id='$college_id'";
$classroom_result = mysqli_query($conn, $classroom_query) or die("Classroom fetch failed!");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Dashboard</title>
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
    .status-text { display:block; font-size:12px; color:#fff; margin:2px 0; }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome Faculty <a class="logout" href="../home.php">Logout</a></h2>

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
        <?php
        while ($row = mysqli_fetch_array($classroom_result)) {
            $room_id = $row['id'];

            $book_query = "SELECT * FROM bookings WHERE room_id='$room_id' AND status='approved' ORDER BY date ASC, start_time ASC";
            $bookings = mysqli_query($conn, $book_query) or die("Booking fetch failed!");

            $status_text = "";
            while ($b = mysqli_fetch_array($bookings)) { 
                $who = ($b['faculty_id'] == $faculty_id) ? "<b>Your booking</b>" : "Booked by someone";
                $status_text .= "<span class='status-text'>$who for " .
                    date("d/m/Y", strtotime($b['date'])) . " from " .
                    date("h:i A", strtotime($b['start_time'])) . " – " .
                    date("h:i A", strtotime($b['end_time'])) . "</span><br>";
            }
            if ($status_text == "") $status_text = "<span class='status-text'>Available</span>";

            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['room_name']}</td>
                <td>{$row['type']}</td>
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
        }
        ?>
    </table>
</div>
</body>
</html>
