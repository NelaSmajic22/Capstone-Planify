<?php
require "user_session.php";


$user_id = $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = trim($_POST['subject_name']);
    
    if (empty($subject_name)) {
        $error = "Subject name cannot be empty";
    } else {
        $stmt = $db->prepare("INSERT INTO subjects (subject_name, user_id) VALUES (?, ?)");
        $stmt->bind_param("si", $subject_name, $user_id);
        
        if ($stmt->execute()) {
            header("Location: view_subjects.php?success=1");
            exit();
        } else {
            $error = "Error creating subject: " . $db->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Subject</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=1">
    <style>       
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light);
            padding: 0;
            margin: 0;
            padding-top: 100px;
        }
        
        .content {
            max-width: 800px;
            margin: 0 auto 30px auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h2 {
            color: var(--primary-dark);
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.8rem;
        }
        .add-subject-container {
            margin: 90px auto 0 auto;
            padding: 25px;
            width: 90%;
            max-width: 600px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .add-subject-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            color: var(--primary-dark);
        }
        
        .add-subject-header i {
            font-size: 1.5rem;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn-create {
            background-color: var(--primary);
            color: white;
            padding: 12px 25px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-create:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-cancel {
            background-color: var(--light);
            color: var(--danger);
            border: 1px solid #e0e0e0;
            padding: 12px 25px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-cancel:hover {
            background-color: #fff0f0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input[type="text"]:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
    </style>
</head>
<body>


    <div class="add-subject-container">
        <div class="add-subject-header">
            <i class="fas fa-book-medical"></i>
            <h1>Create New Subject</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="subject_name">Subject Name</label>
                <input type="text" name="subject_name" id="subject_name" 
                       placeholder="Enter subject name" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-create">
                    <i class="fas fa-plus"></i> Create Subject
                </button>
                <a href="view_subjects.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>


</body>
</html>