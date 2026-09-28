<?php

    class users
    {
        private string $full_name;
        private string $username;
        private string $email;
        private string $password;
        private string $role;
        private string $created_at;
       
       

        public function __construct(string $full_name, string $username,string $email, string $password, string $role, string $created_at)
        {
            $this->full_name = $full_name;
            $this->username = $username;
            $this->full_name = $full_name;
            $this->email = $email;
            $this->password = $password;
            $this->role = $role;
            $this->created_at = $created_at;

           
            
           
        }
       public function add_user($con){
            $sql = "INSERT INTO users (full_name, username, email, password, role, created_at)
                    VALUES ('$this->full_name', '$this->username', '$this->email', '$this->password', '$this->role', '$this->created_at')";
            
            $result = mysqli_query($con, $sql);
            if($result){
            
                    return "<script>alert('$this->full_name successfully Added...'); window.location.href='/landslide/index.php'</script>";
                } else {
                    return "<script>alert('Unsuccessfully... Error or Invalid'); window.location.href='/landslide/index.php'</script>";
            }

       }
      
    }
    class temp_user 
    {   
        private string $username;
        private string $password;

       
        public function __construct( string $username,  string $password)
        {
            $this->username = $username;
            $this->password = $password;
        }
        public function add_user_session($con){
            if($result = mysqli_query($con, "SELECT * FROM users WHERE username = '$this->username' ")){
            while($row = mysqli_fetch_assoc($result)){
                if(($row['role'] === 'admin')&&($this->password === $row['password'])){
                    $_SESSION['USERNAME'] = $row['username'];
                    $_SESSION['FULL_NAME'] = $row['full_name'];
                    $_SESSION['EMAIL'] = $row['email'];
                    $_SESSION['PASSWORD'] = $row['password'];
                    $_SESSION['ROLE'] = $row['role'];
                    $_SESSION['CREATED_AT'] = $row['created_at'];
                    return "<script>alert('Admin Login Successfull!...'); window.location.href='/landslide/components/admins/admin.php';</script>";
                } else if(($row['role'] === 'user')&&($this->password === $row['password'])){
                    $_SESSION['USERNAME'] = $row['username'];
                    $_SESSION['FULL_NAME'] = $row['full_name'];
                    $_SESSION['EMAIL'] = $row['email'];
                    $_SESSION['PASSWORD'] = $row['password'];
                    $_SESSION['ROLE'] = $row['role'];
                    $_SESSION['CREATED_AT'] = $row['created_at'];
                    return "<script>alert('User Login Successfull!...'); window.location.href='/landslide/components/users/user.php';</script>";
                } else {
                    return "<script>alert('INVALID CREDENTIALS!'); window.location.href='/landslide/index.php';</script>";
                }
            }
        } else {
            return "<script>alert('INVALID CREDENTIALS!...'); window.location.href='/landslide/index.php';</script>";
        }

        }
    }



?>