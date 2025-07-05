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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Barrietto</title>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
    <link rel="icon" href="assets/img/tab_icon.png" width='100%'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #16191d 0%, #292E33 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("assets/img/flag_bg2.png");
            background-size: cover;
            background-attachment: fixed;
            background-repeat: no-repeat;
            opacity: 0.1;
            z-index: 1;
        }
        
        .login-card {
            background: var(--card-bg);
            border: 2px solid var(--card-border);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 2;
            max-width: 450px;
            width: 90%;
            animation: slideIn 0.6s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-logo {
            width: 180px;
            height: auto;
            margin-bottom: 2rem;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }
        
        .login-title {
            color: var(--base-white);
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .login-subtitle {
            color: var(--card-subtitle);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-floating input {
            background: rgba(255,255,255,0.05);
            border: 2px solid var(--card-border);
            border-radius: 12px;
            color: var(--base-white);
            padding: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-floating input:focus {
            background: rgba(255,255,255,0.08);
            border-color: var(--base-red);
            box-shadow: 0 0 0 0.2rem rgba(220,53,69,0.25);
            color: var(--base-white);
        }
        
        .form-floating label {
            color: var(--card-subtitle);
            padding-left: 1rem;
        }
        
        .btn-login {
            background: linear-gradient(45deg, var(--base-red), #ff4757);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: bold;
            padding: 1rem 1rem;
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-login:hover {
            background: linear-gradient(45deg, #ff4757, var(--base-red));
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220,53,69,0.4);
            color: white;
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: rgba(220,53,69,0.1);
            border: 1px solid rgba(220,53,69,0.3);
            border-radius: 8px;
            color: #ff6b6b;
            padding: 0.75rem;
            margin-top: 1rem;
            text-align: center;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .racing-stripes {
            position: absolute;
            top: 0;
            left: -50%;
            width: 200%;
            height: 100%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 20px,
                rgba(114, 220, 53, 0.03) 20px,
                rgba(114,220,53,0.03) 40px
            );
            animation: move 5s linear infinite;
            z-index: 1;
        }
        
        @keyframes move {
            0% { transform: translateX(-50px); }
            100% { transform: translateX(0px); }
        }
        
        @media (max-width: 768px) {
            .login-card {
                padding: 2rem;
                margin: 1rem;
            }
            
            .login-logo {
                width: 150px;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="racing-stripes"></div>
    
    <div class="login-card">
        <div class="text-center">
            <img src="assets/img/logo_white.png" alt="Barrietto Logo" class="login-logo">
            <h1 class="login-title">Welcome Back</h1>
        </div>
        
        <form method="post">
            <div class="form-floating">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                <label for="username">🏎️ Username</label>
            </div>
            
            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">🔑 Password</label>
            </div>
            
            <button type="submit" class="btn btn-login">
                🏁 Login
            </button>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    ❌ <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
