<?php
include('includes/db_connect.php');

if (isset($_POST['activate'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password != $confirm) {
        $msg = "<p class='error'>Passwords do not match!</p>";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password='$hashed', is_active=1 WHERE email='$email' AND is_active=0";
        $conn->query($sql);

        if ($conn->affected_rows > 0) {
            $msg = "<p class='success'>Account activated! You can now <a href='login.php'>login</a>.</p>";
        } else {
            $msg = "<p class='error'>Invalid email or account already activated.</p>";
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
            background-image:url('image.png');
            font-family: Arial;
            
            display: flex; justify-content: center; align-items: center;
            height: 100vh; margin: 0;
        }
        .box {
            background: #ecf819ff; padding: 40px; border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 400px;
        }
        h2 { text-align: center; }
        input {
            width: 100%; padding: 10px; margin: 8px 0;
            border: 1px solid #ccc; border-radius: 5px;
        }
        button {
            width: 100%; background: #ff9d00ff; color: white;
            border: none; padding: 10px; border-radius: 5px;
            font-size: 16px; cursor: pointer;
        }
        button:hover { background: #ff9811ff; }
        .msg { text-align: center; margin-bottom: 10px; }
        .error { color: red; }
        .success { color: green; }
        a { color: #ff7300ff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Activate Your Account</h2>
        <div class="msg"><?= $msg ?? '' ?></div>

        <form method="POST">
            <input type="email" name="email" placeholder="Enter email" required>
            <input type="password" name="password" placeholder="Enter password" required>
            <input type="password" name="confirm_password" placeholder="Confirm password" required>
            <button type="submit" name="activate">Activate</button>
        </form>

        <p style="text-align:center; margin-top:15px;">
            Already activated? <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>
