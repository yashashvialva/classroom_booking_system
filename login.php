<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/db_connect.php');

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
// Fetch user from database
    $sql = "SELECT id, password, role, is_active FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        $id = $row['id'];
        $hashed_password = $row['password'];
        $role = $row['role'];
        $is_active = $row['is_active'];

        // Verify password
        if(password_verify($password, $hashed_password)){
            if($is_active == 0){
                $error = "Please activate your account first!";
            } else {
                $_SESSION['user_id'] = $id;
                $_SESSION['role'] = $role;

                if($role == 'admin'){
                    header("Location: dashboard/admin_dashboard.php");
                } elseif($role == 'faculty'){
                    header("Location: dashboard/faculty_dashboard.php");
                } else {
                    header("Location: dashboard/student_dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #000000ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .login-container {
            background-color: #f2ff00ff;
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
            background-color: #ff8800ff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
        }

        input[type="submit"]:hover {
            background-color: #ffab02ff;
        }

        .error {
            color: red;
            margin-bottom: 20px;
            text-align: center;
        }

        .link {
            text-align: center;
            margin-top: 15px;
        }

        .link a {
            color: #ff7707ff;
            text-decoration: none;
            font-weight: bold;
        }

        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password:</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <input type="submit" name="login" value="Login">
        </form>
        <div class="link">
            <p>First time user? <a href="activate_account.php">Activate your account</a></p>
        </div>
    </div>
</body>
</html>
 