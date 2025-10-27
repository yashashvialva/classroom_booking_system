<?php
$conn = mysqli_connect("localhost", "root", "", "college_booking");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
