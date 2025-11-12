<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../home.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

$query = "SELECT college_id FROM users WHERE id='$admin_id'";
$result = mysqli_query($conn, $query) or die("Query failed!");
$row = mysqli_fetch_array($result);
$college_id = $row['college_id'];

// Add classroom/lab
if (isset($_POST['add'])) {
    $room_name = $_POST['room_name'];
    $type = $_POST['type'];

    $query = "INSERT INTO classrooms (college_id, room_name, type) VALUES ('$college_id', '$room_name', '$type')";
    if (mysqli_query($conn, $query)) {
        $msg = "Classroom/Lab added successfully!";
    } else {
        $msg = "Error adding room: " . mysqli_error($conn);
    }
}

// Accept / Decline / Delete booking requests
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    if ($action == 'delete') {
        mysqli_query($conn, "DELETE FROM bookings WHERE id='$id'") or die("Delete failed!");
        $msg = "Request deleted successfully!";
    } elseif ($action == 'approved' || $action == 'rejected') {
        $requestquery = "UPDATE bookings SET status='$action' WHERE id='$id'";
        mysqli_query($conn, $requestquery) or die("Update failed!");
        $msg = "Request $action successfully!";
    }
}

$query_disp = "SELECT * FROM classrooms WHERE college_id='$college_id'";
$classrooms = mysqli_query($conn, $query_disp) or die("Classroom fetch failed!");

// Fetch booking requests
$booking_query = "SELECT b.id, b.date, b.start_time, b.end_time, b.reason, b.status,
                         c.room_name,
                         u.name AS requested_by
                  FROM bookings b
                  JOIN classrooms c ON b.room_id = c.id
                  JOIN users u ON (b.student_id = u.id OR b.faculty_id = u.id)
                  WHERE c.college_id='$college_id'
                  ORDER BY b.date DESC, b.id DESC";

$bookings = mysqli_query($conn, $booking_query) or die("Booking fetch failed!");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
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
        }
        .msg.success { background-color: #ddff03ff; color: #155724; }
        .msg.error { background-color: #f8d7da; color: #721c24; }
        form {
            margin-bottom: 30px;
            background-color: black;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #fde006ff;
            color: white;
        }
        th { background-color: black; color:#ffe600; }
        tr:hover { background-color: #ffe60033; }
        a { text-decoration: none; color: #007BFF; }
        a:hover { text-decoration: underline; }
        .actions a { margin: 0 5px; font-weight: bold; }
        .logout {
            float: right;
            background-color: #cad70fff;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
        }
        .logout:hover { background-color: #ffff17ff; }
        hr { margin: 30px 0; border: none; border-top: 1px solid #ccc; }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome, Admin <a class="logout" href="../home.php">Logout</a></h2>

    <?php if (isset($msg)) echo "<div class='msg success'>$msg</div>"; ?>

    <h3>Add Classroom or Lab</h3>
    <form method="POST">
        <label>Room Name:</label>
        <input type="text" name="room_name" placeholder="Enter classroom/lab name"
               style="width:100%; padding:10px; border:1px solid yellow; border-radius:5px;">

        <label>Type:</label>
        <select name="type">
            <option value="class">Classroom</option>
            <option value="lab">Lab</option>
        </select>

        <input type="submit" name="add" value="Add Classroom/Lab"
               style="padding:12px 20px; background-color:orange; border:none;
                      color:white; border-radius:8px; cursor:pointer; font-size:16px;">
    </form>

    <hr>

    <h3>All Classrooms/Labs</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Room Name</th>
            <th>Type</th>
        </tr>
        <?php
        while ($row = mysqli_fetch_array($classrooms)) {
            echo "<tr>";
            echo "<td>".$row['id']."</td>";
            echo "<td>".$row['room_name']."</td>";
            echo "<td>".$row['type']."</td>";
            echo "</tr>";
        }
        ?>
    </table>

    <hr>

    <h3>Booking Requests</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Room</th>
            <th>Requested By</th>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php
        if (mysqli_num_rows($bookings) > 0) {
            while ($row = mysqli_fetch_array($bookings)) {
                echo "<tr>";
                echo "<td>".$row['id']."</td>";
                echo "<td>".$row['room_name']."</td>";
                echo "<td>".$row['requested_by']."</td>";
                echo "<td>".$row['date']."</td>";
                echo "<td>".$row['start_time']."</td>";
                echo "<td>".$row['end_time']."</td>";
                echo "<td>".$row['reason']."</td>";
                echo "<td>".$row['status']."</td>";
                echo "<td class='actions'>";

                if ($row['status'] == 'pending') {
                    echo "<a href='admin_dashboard.php?action=approved&id=".$row['id']."'>Approve</a> | ";
                    echo "<a href='admin_dashboard.php?action=rejected&id=".$row['id']."'>Reject</a>";
                }

                echo " | <a href='admin_dashboard.php?action=delete&id=".$row['id']."' onclick=\"return confirm('Are you sure you want to delete this request?');\">Delete</a>";

                echo "</td></tr>";
            }
        } else {
            echo "<tr><td colspan='9'>No booking requests found.</td></tr>";
        }
        ?>
    </table>
</div>
</body>
</html>
