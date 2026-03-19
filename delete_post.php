<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($post_id > 0){
    // Delete only own post
    $conn->query("DELETE FROM posts WHERE id=$post_id AND user_id=$user_id");
}

header("Location: home.php");
exit;
?>