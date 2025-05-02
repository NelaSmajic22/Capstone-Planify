<?php
require "user_session.php";

// subject id
if (!isset($_GET['subject_id']) || !is_numeric($_GET['subject_id'])) {
    header("Location: view_subjects.php");
    exit();
}

$subject_id = $_GET['subject_id'];


$query = "SELECT * FROM subjects WHERE id = ? AND user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("ii", $subject_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$subject = $result->fetch_assoc();

if (!$subject) {
    header("Location: view_subjects.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_subject'])) {
    $subject_name = htmlspecialchars(trim($_POST['subject_name']));
    
    if (!empty($subject_name)) {
        $update_query = "UPDATE subjects SET subject_name = ? WHERE id = ?";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bind_param("si", $subject_name, $subject_id);
        
        if ($update_stmt->execute()) {
            header("Location: view_subjects.php?success=1");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject</title>
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
        .edit-subject-container {
            margin: 90px auto 0 auto;
            padding: 25px;
            width: 90%;
            max-width: 600px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .edit-subject-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            color: var(--primary-dark);
        }
        
        .edit-subject-header i {
            font-size: 1.5rem;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn-update {
            background-color: var(--primary);
            color: white;
            padding: 12px 25px;
            border: none;
        }
        
        .btn-update:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-cancel {
            background-color: var(--light);
            color: var(--danger);
            border: 1px solid #e0e0e0;
            padding: 12px 25px;
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


    <div class="edit-subject-container">
        <div class="edit-subject-header">
            <i class="fas fa-edit"></i>
            <h1>Edit Subject</h1>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="subject_name">Subject Name</label>
                <input type="text" name="subject_name" id="subject_name" 
                       value="<?php echo htmlspecialchars($subject['subject_name']); ?>" 
                       placeholder="Enter subject name" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="update_subject" class="btn btn-update">
                    <i class="fas fa-save"></i> Update Subject
                </button>
                <a href="view_subjects.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>


</body>
</html>