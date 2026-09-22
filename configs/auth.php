<?php
    include_once("config.php");
    include("db.php");
    if(isset($_POST['login'])){
        session_start();
        
        $username = $_POST['username'];
        $password = $_POST['password'];

        if($result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' ")){
            while($row = mysqli_fetch_assoc($result)){
                if(($row['role'] === 'admin')&&($password === $row['password'])){
                    $_SESSION['USERNAME'] = $row['username'];
                    $_SESSION['FULL_NAME'] = $row['full_name'];
                    $_SESSION['EMAIL'] = $row['email'];
                    $_SESSION['PASSWORD'] = $row['password'];
                    $_SESSION['ROLE'] = $row['role'];
                    $_SESSION['CREATED_AT'] = $row['created_at'];
                    echo "<script>alert('Admin Login Successfull!...'); window.location.href='../components/admins/admin.php';</script>";
                } else if(($row['role'] === 'user')&&($password === $row['password'])){
                    $_SESSION['USERNAME'] = $row['username'];
                    $_SESSION['FULL_NAME'] = $row['full_name'];
                    $_SESSION['EMAIL'] = $row['email'];
                    $_SESSION['PASSWORD'] = $row['password'];
                    $_SESSION['ROLE'] = $row['role'];
                    $_SESSION['CREATED_AT'] = $row['created_at'];
                    echo "<script>alert('User Login Successfull!...'); window.location.href='../components/users/user.php';</script>";
                } else {
                    echo "<script>alert('INVALID CREDENTIALS!'); window.location.href='../index.php';</script>";
                }
            }
        } else {
            echo "<script>alert('INVALID CREDENTIALS!...'); window.location.href='../index.php';</script>";
        }

        
            
        
        
    }

?>