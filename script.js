function likePost(post_id){
    var xhr = new XMLHttpRequest();
    xhr.open("POST","like.php",true);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.onload = function(){
        document.getElementById("like-count-"+post_id).innerHTML = this.responseText;
    };
    xhr.send("post_id="+post_id);
}

function commentPost(post_id){
    var comment = document.getElementById("comment-input-"+post_id).value;

    var xhr = new XMLHttpRequest();
    xhr.open("POST","comment.php",true);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.onload = function(){
        alert(this.responseText);
    };
    xhr.send("post_id="+post_id+"&comment="+comment);
}