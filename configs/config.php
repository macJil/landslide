<?php

    class users
    {
        public string $full_name;
        public string $username;
        public string $email;
        public string $password;
        public string $role;
        public string $created_at;
       
     

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
            
                    echo "<script>alert('$this->full_name successfully Added...'); window.location.href='../index.php'</script>";
                } else {
                    echo "<script>alert('Unsuccessfully... Error or Invalid'); window.location.href='../index.php'</script>";
            }

       }
       
    }



?>