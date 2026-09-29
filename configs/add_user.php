<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        flash('register_error', 'Your session expired. Please try again.');
        redirect_to('register.php');
    }

    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($fullName === '' || strlen($fullName) > 100 || $username === '' || strlen($username) > 50
        || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($password) < 8 || strlen($password) > 72) {
        flash('register_error', 'Enter a name, username, valid email, and password between 8 and 72 characters.');
        redirect_to('register.php');
    }

    try {
        (new UserRepository($pdo))->create($fullName, $username, $email, $password);
        flash('register_success', 'Account created. You can now sign in.');
        redirect_to('register.php');
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            flash('register_error', 'That username or email is already registered.');
            redirect_to('register.php');
        }
        error_log('SmartSlope registration failed: ' . $exception->getMessage());
        flash('register_error', 'The account could not be created. Please try again.');
        redirect_to('register.php');
    }
}
