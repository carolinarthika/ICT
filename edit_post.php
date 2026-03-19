<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($post_id <= 0){
    header("Location: home.php");
    exit;
}

// Get post - only if it belongs to current user
$result = $conn->query("SELECT * FROM posts WHERE id=$post_id AND user_id=$user_id");
if($result->num_rows == 0){
    header("Location: home.php");
    exit;
}
$post = $result->fetch_assoc();

// Update on form submit
if(isset($_POST['update'])){
    $content = $conn->real_escape_string($_POST['content']);
    $conn->query("UPDATE posts SET content='$content' WHERE id=$post_id AND user_id=$user_id");
    header("Location: home.php");
    exit;
}
?>
<link rel="stylesheet" href="style.css">
<div class="container">
<h2>Edit Post</h2>
<form method="POST">
    <textarea name="content" required><?= htmlspecialchars($post['content']) ?></textarea>
    <button name="update">Update Post</button>
</form>
<a href="home.php">Cancel</a>
</div>