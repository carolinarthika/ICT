<?php
session_start();
include "db.php";

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

$conn->query("INSERT IGNORE INTO likes(user_id,post_id) VALUES('$user_id','$post_id')");

$result = $conn->query("SELECT COUNT(*) as total FROM likes WHERE post_id='$post_id'");
echo $result->fetch_assoc()['total'];
?>