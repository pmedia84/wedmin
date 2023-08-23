<?php
if(isset($_POST['action']) && $_POST['action'] =="create"){
    include("../connect.php");
    $story = $db->prepare('INSERT INTO wedding_story (story_body, story_status)VALUES(?,?)');

    //response variable
    $response="";
    //set up variables
    $story_body = htmlentities($_POST['story_body']);
    $story_status = $_POST['story_status'];
    
    $story->bind_param('ss', $story_body, $story_status);
    $story->execute();
    $story->close();
    $response="success";
    echo $response;
}
if(isset($_POST['action']) && $_POST['action'] =="edit"){
    include("../connect.php");
    $story = $db->prepare('UPDATE wedding_story SET story_body=?, story_status=? WHERE story_id =?');

    //response variable
    $response="";
    //set up variables
    $story_id = $_POST['story_id'];
    $story_body = htmlentities($_POST['story_body']);
    $story_status = $_POST['story_status'];
    
    $story->bind_param('ssi', $story_body, $story_status, $story_id);
    $story->execute();
    $story->close();
    $response="success";
    echo $response;
}
