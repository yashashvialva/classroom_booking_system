<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_POST['submit'])){
    if(isset($_FILES['csv']) && $_FILES['csv']['error'] == 0){
        $file = $_FILES['csv']['tmp_name'];
        if(($handle = fopen($file, "r")) !== FALSE){
            while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
                echo "Row: Name={$data[0]}, Email={$data[1]}, Role={$data[2]}<br>";
            }
            fclose($handle);
        } else {
            echo "Cannot open file!";
        }
    } else {
        echo "File upload failed!";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    Upload CSV: <input type="file" name="csv">
    <input type="submit" name="submit" value="Upload">
</form>
