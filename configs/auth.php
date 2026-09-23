<?php
    include_once("config.php");
    include("db.php");
    if(isset($_POST['login'])){
        session_start();
        
        $session_user = new temp_user(
            $username = $_POST['username'],
            $password = $_POST['password']
        );
    
        echo $session_user->add_user_session($conn);        
    }

?>