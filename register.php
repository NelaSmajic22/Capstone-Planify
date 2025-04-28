<?php
require "open_db.php"; 

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($full_name) || empty($email) || empty($password)) {
        $message = "All fields are required!";
    } else {
        // validates email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format!";
        } else {

            if (strlen($password) < 8 || !preg_match("/[A-Za-z]/", $password) || !preg_match("/[0-9]/", $password)) {
                $message = "Password must be at least 8 characters long and contain at least one letter and one number.";
            } else {

                $hashed_password = password_hash($password, PASSWORD_BCRYPT);


                $sql = $db->prepare("INSERT INTO users (email, password_hash, full_name, is_verified) 
                                    VALUES (?, ?, ?, 1)");
                $sql->bind_param("sss", $email, $hashed_password, $full_name);

                if ($sql->execute()) {
                    header("Location: login.php");
                    exit();
                } else {
                    $message = "Error: " . $sql->error;
                }

                $sql->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Planify</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .auth-container {
            width: 100%;
            max-width: 400px;
            margin: var(--space-xxl) auto;
            padding: var(--space-xl);
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow);
        }
        
        .auth-title {
            color: var(--primary);
            margin-bottom: var(--space-lg);
            text-align: center;
        }
        
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: var(--space-lg);
        }
        
        .form-footer {
            margin-top: var(--space-lg);
            text-align: center;
            font-size: var(--font-size-md);
            color: var(--dark);
        }
        
        .login-link {
            color: var(--primary);
            font-weight: 600;
            margin-left: var(--space-sm);
        }
        
        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Planify</h1>
        <h2 class="text-center">Create an Account</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form class="auth-form" action="register.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name</label>
                <input class="form-control" type="text" id="full_name" name="full_name" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
                <small class="text-muted">Must be at least 8 characters with a number and letter</small>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg">Register</button>
        </form>
        
        <div class="form-footer">
            Already have an account? <a href="login.php" class="login-link">Login</a>
        </div>
    </div>
</body>
</html>