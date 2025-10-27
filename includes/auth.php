<?php
session_start();

function checkRole($role_required){
    if(!isset($_SESSION['user_id']) || $_SESSION['role'] != $role_required){
        header("Location: ../index.php");
        exit;
    }
}
?>
