<?php
include("../configs/add_user.php");


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Landslide Warning System</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script src="../assets/js/bootstrap.bundle.js" defer></script>
</head>
<body>
    <header class="nav" style="background-color: aliceblue; display:flex; justify-content:space-between; align-items:center; padding:10px 20px;">
        <h1>BARANGAY IRISAN (Baguio City) - Landslide Warning System</h1>
    </header>
    <div class="container" style="width: 900px; align-items:center; text-align: center" >
        <h4>Register Page</h4>
        <form action="" method="post" class="form-control">
            <table class="table table-borderless">
                <tr>
                    <th>Enter Username</th>
                    <td>
                        <input type="text" name="username" id="" class="form-control">
                    </td>
                </tr>
                <tr>
                    <th>Enter Full Name</th>
                    <td>
                        <input type="text" name="full_name" id="" class="form-control">
                    </td>
                </tr>
               
                <tr>
                    <th>Enter Email</th>
                    <td>
                        <input type="email" name="email" id="" class="form-control">
                    </td>
                </tr>
                <tr>
                    <th>Enter Password</th>
                    <td>
                        <input type="password" name="password" id="" class="form-control">
                    </td>
                </tr>
                <tr>
                    <th>Enter Role</th>
                    <td>
                        <select type="text" name="role" id="" class="form-control">
                            <option value="" disabled selected></option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                        
                    </td>
                </tr>
                

            </table>
            <button class="btn btn-outline-success" name="register">Register</button>
            <a href="../index.php" class="btn btn-outline-danger">Cancel</a>
        </form>
    </div>
</body>
<?php include("../components/footer.html")?>
</html>
