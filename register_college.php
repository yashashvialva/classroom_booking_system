<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/db_connect.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register College</title>
    <style>
        body {
            background-image:url('image.png');
            font-family: Arial, sans-serif;
            background-color: black;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .register-container {
            background-color: #e2ff27ff;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 450px;
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

        input[type="text"],
        input[type="file"] {
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
            background-color: #ffa200ff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #11ff8cff;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .message.error { color: red; }
        .message.success { color: green; }

        .link {
            text-align: center;
            margin-top: 15px;
        }

        .link a {
            color: #5043e0ff;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register College</h2>

        <?php
        if(isset($_POST['submit'])){
            $college_name = $_POST['college_name'];

            if(isset($_FILES['csv']) && $_FILES['csv']['error'] == 0){
                $file = $_FILES['csv']['tmp_name'];
                $sql_college = "INSERT INTO colleges (college_name) VALUES ('$college_name')";
                if(mysqli_query($conn, $sql_college)){
                    $college_id = mysqli_insert_id($conn);
                    echo "<p class='message success'>College inserted with ID: $college_id</p>";

                    // Insert users from CSV
                    if(($handle = fopen($file, "r")) !== FALSE){
                        fgetcsv($handle); // skip header
                        $row = 1;
                        while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
                            $name = $data[0];
                            $email = $data[1];
                            $role = $data[2];
                            $password = password_hash("12345", PASSWORD_DEFAULT);

                            $sql_user = "INSERT INTO users (name,email,password,role,college_id)
                                         VALUES ('$name','$email','$password','$role','$college_id')";
                            if(mysqli_query($conn, $sql_user)){
                                echo "<p>Row $row inserted: Name=$name, Email=$email, Role=$role</p>";
                            } else {
                                echo "<p class='message error'>Error inserting user $name: " . mysqli_error($conn) . "</p>";
                            }
                            $row++;
                        }
                        fclose($handle);
                        echo "<p class='message success'>All users processed successfully!</p>";
                    } else {
                        echo "<p class='message error'>Cannot open CSV file.</p>";
                    }
                } else {
                    echo "<p class='message error'>Error inserting college: " . mysqli_error($conn) . "</p>";
                }
            } else {
                echo "<p class='message error'>CSV file upload failed!</p>";
            }
        }
        ?>

        <form method="POST" enctype="multipart/form-data">
            <label>College Name:</label>
            <input type="text" name="college_name" placeholder="Enter college name" required>

            <label>Upload CSV (name,email,role):</label>
            <input type="file" name="csv" accept=".csv" required>

            <input type="submit" name="submit" value="Register">
        </form>

        <div class="link">
            <p><a href="home.php">Back to Home</a></p>
        </div>
    </div>
</body>
</html>
