<?php
session_start();

// Handle logout only if explicitly requested
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: /landslide/index.php");
    exit;
}

// Redirect if no active session
if (empty($_SESSION['USERNAME'])) {
    header("Location: /landslide/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landslide</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <script src="../../assets/js/bootstrap.bundle.js"></script>
</head>
<header class="nav" style="background-color: aliceblue; display: flex; justify-content: space-between; align-items: center; padding: 10px 20px;">
    <h1>BARANGAY IRISAN (Baguio City) - Landslide Warning System</h1>
    <div>
        <ul class="nav justify-content-end" style="display: flex; list-style: none; margin: 0; padding: 0; gap: 15px;">
            <li class="nav-item">
                <a class="btn btn-outline-danger" aria-current="page" href="">User Mode</a>
            </li>
            <li class="nav-item">
                <!-- Switch button triggers JS confirm -->
                <button class="btn btn-outline-primary" onclick="confirmLogout()">Switch to Admin</button>
            </li>
        </ul>
    </div>
</header>

<body>
    <div class="container">
        <h1>Welcome User</h1>
        <table class="table table-borderless">
            <tr>
                <th>User Name</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Password</th>
                <th>Role</th>
                <th>Created At</th>
            </tr>
            <tr>
                <td><?php echo $_SESSION['USERNAME']?></td>
                <td><?php echo $_SESSION['FULL_NAME']?></td>
                <td><?php echo $_SESSION['EMAIL']?></td>
                <td><?php echo $_SESSION['PASSWORD']?></td>
                <td><?php echo $_SESSION['ROLE']?></td>
                <td><?php echo $_SESSION['CREATED_AT']?></td>
            </tr>
        </table>
    </div>
    <div class="container text-center">
        <div class="row align-items-start">
            <div class="col">
                <?php include("risk_area.php")?>
            </div>
            <div class="col">
                <?php include("report.php")?>
            </div>
           
        </div>
    </div>
    <script>
        function confirmLogout() {
            let userChoice = confirm('Are you sure you want to Log out?');
            if (userChoice) {
                // Redirect with logout flag
                window.location.href = "?logout=true";
            } else {
                // Do nothing, stay on the page
            }
        }
    </script>
</body>

<?php include("../footer.html"); ?>
</html>
