<?php


include("./configs/auth.php");



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landslide</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <script src="assets/js/bootstrap.bundle.js"></script>
</head>
<header class="nav" style="background-color: aliceblue; display: flex; justify-content: space-between; align-items: center; padding: 10px 20px;">
    <h1>BARANGAY IRISAN (Baguio City) - Landslide Warning System</h1>
    
</header>

<body>
    
    <div class="container" style="width: 300px; align-items:center;text-align:center">
        <h4>Login Page</h4>
        <form action="" method="post">
            <div class="card" >
                <div class="card-header">
                    <p>Enter credentials</p>
                </div>
                <div class="body">
                    <div class="form-control ">
                        <label for="" >Username:</label><br>
                        <input type="text" name="username" class="form-control"><br>
                        <label for="" class="form">Password:</label><br>
                        <input type="password" name="password" class="form-control"><br>
                      
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-outline-success" name="login">Login</button>
                    <a href="configs/register.php" class="btn btn-outline-primary">Register</a>
                </div>

            </div>
        
        </form>
    </div>
<?php include("components/footer.html")?>
</body>
</html>