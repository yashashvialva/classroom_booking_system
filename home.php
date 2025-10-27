<!DOCTYPE html>
<html>
<head>
    <title>Classroom Booking System - Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: #fff;
            padding: 40px 60px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }

        h1 {
            margin-bottom: 30px;
            color: #333;
        }

        .btn {
            display: block;
            width: 250px;
            padding: 15px;
            margin: 15px auto;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            color: #fff;
            background-color: #007BFF;
            transition: background-color 0.3s;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .register-btn {
            background-color: #28a745;
        }

        .register-btn:hover {
            background-color: #1e7e34;
        }

        .signup-btn {
            background-color: #ffc107;
            color: #333;
        }

        .signup-btn:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome to Classroom Booking System</h1>

    <a href="login.php" class="btn">Login</a>
    <a href="activate_account.php" class="btn signup-btn">Activate / Signup Account</a>
    <a href="register_college.php" class="btn register-btn">Register College</a>
</div>

</body>
</html>
