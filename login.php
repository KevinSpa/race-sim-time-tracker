<?php
session_start();
if (isset($_SESSION['user_id'])) {
    // user is already logged in, redirect to main page
    header('Location: ./');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // form submitted, validate username and password
    $postUser = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $postPass = $_POST['password'];
    include("inc/conn.php");
    $stmt = $pdo->prepare('SELECT * FROM users WHERE Username = :username');
    $stmt->execute(array(':username' => $postUser));
    $row = $stmt->fetch();

    if (password_verify($postPass, $row['Password'])) {
        // password is correct, create session and redirect to main page
        $_SESSION['user_id'] = $row['ID'];
        header('Location: ./');
        exit();
    }

    // username or password is incorrect, display error message
    $error_message = 'Invalid username or password.';
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
    <link rel="icon" href="assets/img/tab_icon.png" width='100%'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">

</head>
<body>


<div class="container">
    <p class="text-center mt-5">
        <img src="assets/img/logo_white.png" class="loginImg">
    </p>

    <div class="col-12 d-flex justify-content-center">
        <form method="post" class="loginForm m-0">
            <br>
            <input type="text" name="username" placeholder="User" required>
            <br>
            <input type="password" name="password" placeholder="Token" required>
            <br>
            <input type="submit" value="Login">
            <?php if (isset($error_message)) { ?>
                <p class="text-danger"><?php echo $error_message; ?></p>
            <?php } ?>
        </form>
    </div>
</div>

</body>
</html>
