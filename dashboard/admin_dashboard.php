<?php
session_start();
include('../includes/db_connect.php');

// Check if logged in as admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../index.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Get admin's college
$stmt = $conn->prepare("SELECT college_id FROM users WHERE id=?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($college_id);
$stmt->fetch();
$stmt->close();

// Handle add classroom form
if(isset($_POST['add'])){
    $room_name = $_POST['room_name'];
    $type = $_POST['type'];

    $stmt2 = $conn->prepare("INSERT INTO classrooms (college_id, room_name, type) VALUES (?,?,?)");
    $stmt2->bind_param("iss", $college_id, $room_name, $type);
    if($stmt2->execute()){
        $msg = "Classroom/Lab added successfully!";
    } else {
        $msg = "Error adding room: " . $stmt2->error;
    }
    $stmt2->close();
}

// Handle approve/reject/delete actions
if(isset($_GET['action']) && isset($_GET['id'])){
    $action = $_GET['action'];
    $request_id = (int)$_GET['id'];

    if($action == 'delete'){
        $stmt3 = $conn->prepare("DELETE FROM bookings WHERE id=?");
        $stmt3->bind_param("i", $request_id);
        $stmt3->execute();
        $stmt3->close();
        $msg = "Request deleted successfully!";
    } elseif($action == 'approved' || $action == 'rejected'){
        $stmt3 = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
        $stmt3->bind_param("si", $action, $request_id);
        $stmt3->execute();
        $stmt3->close();
        $msg = "Request $action successfully!";
    }
}

// Fetch all classrooms
$classrooms = $conn->prepare("SELECT * FROM classrooms WHERE college_id=?");
$classrooms->bind_param("i", $college_id);
$classrooms->execute();
$classroom_result = $classrooms->get_result();

// Fetch all booking requests
$bookings = $conn->prepare("
    SELECT b.id, b.date, b.start_time, b.end_time, b.reason, b.status, 
           c.room_name, 
           u.name AS requested_by 
    FROM bookings b
    JOIN classrooms c ON b.room_id = c.id
    JOIN users u ON (b.student_id = u.id OR b.faculty_id = u.id)
    WHERE c.college_id=?
    ORDER BY b.date DESC, b.id DESC
");
$bookings->bind_param("i", $college_id);
$bookings->execute();
$booking_result = $bookings->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
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
        }

        .msg.success { background-color: #d4edda; color: #155724; }
        .msg.error { background-color: #f8d7da; color: #721c24; }

        form {
            margin-bottom: 30px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.05);
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        input[type="submit"] {
            padding: 12px 20px;
            background-color: #4CAF50;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 12px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }

        a {
            text-decoration: none;
            color: #007BFF;
        }

        a:hover { text-decoration: underline; }

        .actions a {
            margin: 0 5px;
            font-weight: bold;
        }

        .logout {
            float: right;
            background-color: #f44336;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
        }

        .logout:hover {
            background-color: #d32f2f;
        }

        hr {
            margin: 30px 0;
            border: none;
            border-top: 1px solid #ccc;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Welcome, Admin <a class="logout" href="../home.php">Logout</a></h2>

    <?php if(isset($msg)) echo "<div class='msg success'>$msg</div>"; ?>

    <h3>Add Classroom or Lab</h3>
    <form method="POST">
        <label>Room Name:</label>
        <input type="text" name="room_name" placeholder="Enter classroom/lab name" required>

        <label>Type:</label>
        <select name="type">
            <option value="class">Classroom</option>
            <option value="lab">Lab</option>
        </select>

        <input type="submit" name="add" value="Add Classroom/Lab">
    </form>

    <hr>

    <h3>All Classrooms/Labs</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Room Name</th>
            <th>Type</th>
        </tr>
        <?php while($row = $classroom_result->fetch_assoc()){ ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['room_name']) ?></td>
                <td><?= htmlspecialchars($row['type']) ?></td>
            </tr>
        <?php } ?>
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
        if($booking_result->num_rows > 0){
            while($row = $booking_result->fetch_assoc()){
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . htmlspecialchars($row['room_name']) . "</td>
                    <td>" . htmlspecialchars($row['requested_by']) . "</td>
                    <td>{$row['date']}</td>
                    <td>{$row['start_time']}</td>
                    <td>{$row['end_time']}</td>
                    <td>" . htmlspecialchars($row['reason']) . "</td>
                    <td>{$row['status']}</td>
                    <td class='actions'>";
                if($row['status'] == 'pending'){
                    echo "<a href='?id={$row['id']}&action=approved'>Approve</a> | 
                          <a href='?id={$row['id']}&action=rejected'>Reject</a> | ";
                }
                echo "<a href='?id={$row['id']}&action=delete' onclick=\"return confirm('Are you sure you want to delete this request?');\">Delete</a>";
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
