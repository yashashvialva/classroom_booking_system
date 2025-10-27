<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
include('includes/db_connect.php');

if(isset($_POST['activate'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($password !== $confirm_password){
        $error = "Passwords do not match!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=?, is_active=1 WHERE email=? AND is_active=0");
        $stmt->bind_param("ss", $hash, $email);

        if($stmt->execute() && $stmt->affected_rows > 0){
            $success = "Account activated! You can now log in.";
        } else {
            $error = "Invalid email or account already activated.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Activate Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .activate-container {
            background-color: #fff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .message.error { color: red; }
        .message.success { color: green; }

        .link {
            text-align: center;
            margin-top: 15px;
        }

        .link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }

        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="activate-container">
        <h2>Activate Your Account</h2>
        <?php 
        if(isset($error)) echo "<p class='message error'>$error</p>"; 
        if(isset($success)) echo "<p class='message success'>$success</p>"; 
        ?>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password:</label>
            <input type="password" name="password" placeholder="Enter password" required>

            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" placeholder="Confirm password" required>

            <input type="submit" name="activate" value="Activate">
        </form>
        <div class="link">
            <p>Already activated? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
