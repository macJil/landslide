<?php
    include_once("config.php");
    include("db.php");
    
    if(isset($_POST['register'])){
        $timezone = new DateTimeZone('America/New_York');
        $now = new DateTimeImmutable('now', $timezone);

        $new_user = new users(
            
            $full_name = $_POST['full_name'],
            $username = $_POST['username'],
            $email = $_POST['email'],
            $password = $_POST['password'],
            $role = $_POST['role'],
            $created_at = $now->format('Y-m-d H:i:s'),
            

        );
        echo $new_user->add_user($conn);
        
        
    }

?>