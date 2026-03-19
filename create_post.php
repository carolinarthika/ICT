<?php
session_start();
include "db.php";

if(isset($_POST['post'])){
    $user_id = $_SESSION['user_id'];
    $content = $_POST['content'];
    $conn->query("INSERT INTO posts(user_id, content) VALUES('$user_id','$content')");
    header("Location: home.php");
}
?>