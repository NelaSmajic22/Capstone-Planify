<?php
session_start();
require "open_db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        $error_message = "Email and password fields cannot be empty.";
    } else {
        $check_stmt = $db->prepare("SELECT id, full_name, password_hash, is_verified FROM users WHERE email = ?");
        
        if (!$check_stmt) {
            $error_message = "Error in database query: " . $db->error;
        } else {
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $check_stmt->bind_result($id, $full_name, $hashed_password, $is_verified);
                $check_stmt->fetch();


                if (password_verify($password, $hashed_password)) {
                    if ($is_verified == 1) {

                        $_SESSION["username"] = $full_name;
                        $_SESSION["user_id"] = $id;

                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error_message = "Please verify your email before logging in.";
                    }
                } else {
                    $error_message = "Invalid email or password.";
                }
            } else {
                $error_message = "Invalid email or password.";
            }

            $check_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Planify</title>
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
        
        .register-link {
            color: var(--primary);
            font-weight: 600;
            margin-left: var(--space-sm);
        }
        
        .register-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Planify</h1>
        <h2 class="text-center">Login to Your Account</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form class="auth-form" method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg">Login</button>
        </form>
        
        <div class="form-footer">
            Don't have an account? <a href="register.php" class="register-link">Register here</a>
        </div>
    </div>
</body>
</html>