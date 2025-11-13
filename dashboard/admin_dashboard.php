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
        font-family: 'Segoe UI', Arial, sans-serif;
        background-color: #121212;
        color: #e0e0e0;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 95%;
        margin: 20px auto;
    }

    h1, h2, h3 {
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
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.05);
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

    form {
        background-color: #1b1b1b;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-shadow: 0 0 12px rgba(255, 255, 255, 0.05);
    }

    input[type="text"], select {
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #555;
        background-color: #2b2b2b;
        color: #f1f1f1;
        font-size: 14px;
    }

    input[type="text"]:focus, select:focus {
        outline: none;
        border-color: #ffcc66;
        box-shadow: 0 0 5px #ffcc66;
    }

    input[type="submit"] {
        background-color: #ffcc66;
        color: #1a1a1a;
        border: none;
        border-radius: 6px;
        padding: 10px 15px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    input[type="submit"]:hover {
        background-color: #ffd77a;
        transform: scale(1.03);
    }

    a {
        text-decoration: none;
        color: #66b3ff;
        font-weight: bold;
    }

    a:hover {
        text-decoration: underline;
    }

    .actions a {
        margin: 0 5px;
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

    hr {
        border: none;
        border-top: 1px solid #333;
        margin: 40px 0;
    }

    /* Header with optional image */
    .mascot-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        margin-bottom: 20px;
    }

    .mascot-header img {
        width: 220px;
        height: auto;
    }
</style>

</head>
<body>
<div class="container">
    <h2>Welcome, Admin <a class="logout" href="../home.php">Logout</a></h2>

    <?php if (isset($msg)) echo "<div class='msg success'>$msg</div>"; ?>
    <div class="mascot-header">
        <h1>Add Classroom or Lab</h1>
        <img src="imageadmin.png" alt="Student Mascot">
    </div>
    
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
