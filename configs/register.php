<?php
include("../configs/add_user.php");


?>
<!DOCTYPE html>
<html lang="en">
<?php include("../components/header.html") ?>
<body>
    <div class="container" style="width: 900px; align-items:center; text-align: center" >
        <h4>Register Page</h4>
        <?php if ($message = flash('register_error')): ?>
            <div class="alert alert-danger" role="alert"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if ($message = flash('register_success')): ?>
            <div class="alert alert-success" role="alert"><?= e($message) ?></div>
        <?php endif; ?>
        <form action="" method="post" class="form-control">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <table class="table table-borderless">
                <tr>
                    <th scope="row"><label for="username">Enter Username</label></th>
                    <td>
                        <input type="text" name="username" id="username" class="form-control"
                            minlength="3" maxlength="50" pattern="[A-Za-z0-9._-]{3,50}"
                            autocomplete="username" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="full_name">Enter Full Name</label></th>
                    <td>
                        <input type="text" name="full_name" id="full_name" class="form-control"
                            maxlength="100" autocomplete="name" required>
                    </td>
                </tr>
               
                <tr>
                    <th scope="row"><label for="email">Enter Email</label></th>
                    <td>
                        <input type="email" name="email" id="email" class="form-control"
                            maxlength="254" autocomplete="email" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="password">Enter Password</label></th>
                    <td>
                        <input type="password" name="password" id="password" class="form-control"
                            minlength="8" maxlength="72" autocomplete="new-password" required>
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
