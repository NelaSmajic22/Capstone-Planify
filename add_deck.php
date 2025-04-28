<?php
require "user_session.php";

// subject_id
if (!isset($_GET['subject_id']) || !is_numeric($_GET['subject_id'])) {
    header("Location: view_subjects.php");
    exit();
}

$subject_id = $_GET['subject_id'];
$user_id = $_SESSION['user_id'];
$error = '';

// to verify subject belongs to user
$subject_check = $db->prepare("SELECT id FROM subjects WHERE id = ? AND user_id = ?");
$subject_check->bind_param("ii", $subject_id, $user_id);
$subject_check->execute();
if (!$subject_check->get_result()->fetch_assoc()) {
    header("Location: view_subjects.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_deck'])) {
    $deck_name = htmlspecialchars(trim($_POST['deck_name']));
    
    if (empty($deck_name)) {
        $error = "Deck name cannot be empty";
    } elseif (strlen($deck_name) < 3) {
        $error = "Deck name must be at least 3 characters long";
    } else {
        $stmt = $db->prepare("INSERT INTO decks (deck_name, subject_id, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $deck_name, $subject_id, $user_id);
        
        if ($stmt->execute()) {
            header("Location: decks.php?subject_id=$subject_id&success=1");
            exit();
        } else {
            $error = "Error creating deck: " . $db->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Create New Deck</title>
   <link rel="stylesheet" type="text/css" href="styles.css?v=1">
   <style>
            :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light);
            padding: 0;
            margin: 0;
            padding-top: 80px;
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
       .create-deck-container {
           margin: 90px auto 0 auto;
           padding: 25px;
           width: 90%;
           max-width: 600px;
           background-color: white;
           border-radius: 8px;
           box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
       }
      
       .create-deck-header {
           display: flex;
           align-items: center;
           gap: 10px;
           margin-bottom: 25px;
           color: var(--primary-dark);
       }
      
       .create-deck-header i {
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
           text-decoration: none;
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



   <div class="create-deck-container">
       <div class="create-deck-header">
           <i class="fas fa-plus-circle"></i>
           <h1>Create New Deck</h1>
       </div>
      
       <?php if ($error): ?>
           <div class="alert alert-danger">
               <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
           </div>
       <?php endif; ?>
      
       <form method="POST">
           <div class="form-group">
               <label for="deck_name">Deck Name</label>
               <input type="text" name="deck_name" id="deck_name"
                      placeholder="Enter deck name" required>
           </div>
          
           <div class="form-actions">
               <button type="submit" name="create_deck" class="btn btn-create">
                   <i class="fas fa-save"></i> Create Deck
               </button>
               <a href="decks.php?subject_id=<?= $subject_id ?>" class="btn btn-cancel">
                   <i class="fas fa-times"></i> Cancel
               </a>
           </div>
       </form>
   </div>


</body>
</html>

