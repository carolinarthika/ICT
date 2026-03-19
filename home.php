<?php
session_start();
include "db.php";

// ===================== AJAX HANDLERS =====================
// Like Post
if(isset($_POST['like_post']) && isset($_POST['post_id']) && isset($_SESSION['user_id'])){
    $post_id = (int)$_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    $conn->query("INSERT IGNORE INTO likes(user_id, post_id) VALUES($user_id,$post_id)");
    $result = $conn->query("SELECT COUNT(*) as total FROM likes WHERE post_id=$post_id");
    echo $result->fetch_assoc()['total'];
    exit;
}

// Add Comment
if(isset($_POST['add_comment']) && isset($_POST['post_id']) && isset($_POST['comment']) && isset($_SESSION['user_id'])){
    $post_id = (int)$_POST['post_id'];
    $comment = $conn->real_escape_string($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    if($post_id > 0 && !empty($comment)){
        $conn->query("INSERT INTO comments(post_id,user_id,comment) VALUES($post_id,$user_id,'$comment')");
        $result = $conn->query("SELECT comments.*, users.name FROM comments JOIN users ON comments.user_id=users.id WHERE post_id=$post_id ORDER BY comments.id ASC");
        while($row = $result->fetch_assoc()){
            echo "<p><b>".htmlspecialchars($row['name']).":</b> ".htmlspecialchars($row['comment'])."</p>";
        }
    }
    exit;
}

// Fetch Comments (for live refresh)
if(isset($_GET['fetch_comments']) && isset($_GET['post_id'])){
    $post_id = (int)$_GET['post_id'];
    $result = $conn->query("SELECT comments.*, users.name FROM comments JOIN users ON comments.user_id=users.id WHERE post_id=$post_id ORDER BY comments.id ASC");
    while($row = $result->fetch_assoc()){
        echo "<p><b>".htmlspecialchars($row['name']).":</b> ".htmlspecialchars($row['comment'])."</p>";
    }
    exit;
}

// ===================== REGISTRATION =====================
if(isset($_POST['register'])){
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users(name,email,password) VALUES('$name','$email','$password')");
    header("Location: home.php");
    exit;
}

// ===================== LOGIN =====================
if(isset($_POST['login'])){
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if($result->num_rows>0){
        $user = $result->fetch_assoc();
        if(password_verify($password,$user['password'])){
            $_SESSION['user_id'] = $user['id'];
            header("Location: home.php");
            exit;
        } else {
            $login_error="Invalid password.";
        }
    } else {
        $login_error="User not found.";
    }
}

// ===================== LOGOUT =====================
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: home.php");
    exit;
}

// ===================== POST ACTIONS =====================
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Create post
if(isset($_POST['post']) && $user_id){
    $content = $conn->real_escape_string($_POST['content']);
    $conn->query("INSERT INTO posts(user_id, content) VALUES('$user_id','$content')");
    header("Location: home.php");
    exit;
}

// Edit post
if(isset($_POST['update']) && $user_id){
    $post_id = (int)$_POST['post_id'];
    $content = $conn->real_escape_string($_POST['content']);
    $conn->query("UPDATE posts SET content='$content' WHERE id=$post_id AND user_id=$user_id");
    header("Location: home.php");
    exit;
}

// Delete post
if(isset($_GET['delete_id']) && $user_id){
    $delete_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM posts WHERE id=$delete_id AND user_id=$user_id");
    header("Location: home.php");
    exit;
}

// Edit fetch
$edit_post = null;
if(isset($_GET['edit_id']) && $user_id){
    $edit_id = (int)$_GET['edit_id'];
    $res = $conn->query("SELECT * FROM posts WHERE id=$edit_id AND user_id=$user_id");
    if($res->num_rows>0) $edit_post=$res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Social Feed</title>
<style>
body{font-family:sans-serif;background:#f2f2f2;margin:0;padding:0;}
.container{width:600px;margin:20px auto;background:white;padding:20px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.1);}
textarea{width:100%;padding:10px;margin-bottom:10px;border-radius:4px;border:1px solid #ccc;}
button{padding:6px 12px;border:none;background:#007bff;color:white;border-radius:4px;cursor:pointer;}
button:hover{background:#0056b3;}
.post{border:1px solid #ddd;padding:10px;margin-top:10px;border-radius:4px;background:#fafafa;}
.post b{font-size:16px;}
.post a{margin-left:10px;color:#007bff;text-decoration:none;}
.post a:hover{text-decoration:underline;}
input[type=text]{width:80%;padding:6px;margin-top:5px;}
.edit-post{border:1px solid #ddd;padding:10px;margin-top:10px;border-radius:4px;background:#fff3cd;}
</style>
</head>
<body>
<div class="container">

<?php if(!$user_id): ?>
<!-- Registration -->
<form method="POST">
<h2>Create Account</h2>
<input type="text" name="name" placeholder="Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button name="register">Register</button>
</form>

<hr>

<!-- Login -->
<form method="POST">
<h2>Login</h2>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button name="login">Login</button>
<?php if(isset($login_error)) echo "<p style='color:red;'>$login_error</p>"; ?>
</form>

<?php else: ?>
<a href="?logout=1" style="float:right;">Logout</a>

<!-- New Post -->
<form method="POST">
<textarea name="content" placeholder="What's on your mind?" required></textarea>
<button name="post">Post</button>
</form>

<!-- Edit Post -->
<?php if($edit_post): ?>
<div class="edit-post">
<h2>Edit Post</h2>
<form method="POST">
<input type="hidden" name="post_id" value="<?= $edit_post['id'] ?>">
<textarea name="content" required><?= htmlspecialchars($edit_post['content']) ?></textarea>
<button name="update">Update Post</button>
</form>
<a href="home.php">Cancel</a>
</div>
<?php endif; ?>

<!-- Posts -->
<h2>Posts</h2>
<?php
$posts = $conn->query("SELECT posts.*, users.name FROM posts JOIN users ON posts.user_id=users.id ORDER BY posts.id DESC");
while($row = $posts->fetch_assoc()):
$is_own = ($row['user_id']==$user_id);
?>
<div class="post" id="post-<?= $row['id'] ?>">
<b><?= htmlspecialchars($row['name']) ?></b>
<?php if($is_own): ?>
<a href="?edit_id=<?= $row['id'] ?>">Edit</a>
<a href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Delete this post?')">Delete</a>
<?php endif; ?>
<p><?= htmlspecialchars($row['content']) ?></p>

<button onclick="likePost(<?= $row['id'] ?>)">Like</button>
<span id="like-count-<?= $row['id'] ?>">
<?php
$likes = $conn->query("SELECT COUNT(*) as total FROM likes WHERE post_id=".$row['id']);
echo $likes->fetch_assoc()['total'];
?>
</span>

<div id="comments-<?= $row['id'] ?>">
<?php
$comments = $conn->query("SELECT comments.*, users.name FROM comments JOIN users ON comments.user_id=users.id WHERE post_id=".$row['id']." ORDER BY comments.id ASC");
while($c = $comments->fetch_assoc()){
    echo "<p><b>".htmlspecialchars($c['name']).":</b> ".htmlspecialchars($c['comment'])."</p>";
}
?>
</div>
<input type="text" id="comment-input-<?= $row['id'] ?>" placeholder="Write a comment...">
<button onclick="commentPost(<?= $row['id'] ?>)">Comment</button>
</div>
<?php endwhile; ?>

<?php endif; ?>
</div>

<script>
function likePost(post_id){
    var xhr=new XMLHttpRequest();
    xhr.open("POST","",true);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.onload=function(){ document.getElementById("like-count-"+post_id).innerText=this.responseText; };
    xhr.send("like_post=1&post_id="+post_id);
}

function commentPost(post_id){
    var comment=document.getElementById("comment-input-"+post_id).value;
    if(comment.trim()==="") return;
    var xhr=new XMLHttpRequest();
    xhr.open("POST","",true);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.onload=function(){ document.getElementById("comment-input-"+post_id).value=""; fetchComments(post_id); };
    xhr.send("add_comment=1&post_id="+post_id+"&comment="+encodeURIComponent(comment));
}

function fetchComments(post_id){
    var xhr=new XMLHttpRequest();
    xhr.open("GET","?fetch_comments=1&post_id="+post_id,true);
    xhr.onload=function(){ document.getElementById("comments-"+post_id).innerHTML=this.responseText; };
    xhr.send();
}

// Auto-refresh comments for all posts
<?php
$all_posts = $conn->query("SELECT id FROM posts ORDER BY id DESC");
while($p=$all_posts->fetch_assoc()){
    echo "setInterval(function(){ fetchComments(".$p['id']."); },3000);\n";
}
?>
</script>
</body>
</html>