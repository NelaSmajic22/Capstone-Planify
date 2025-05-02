<?php
require "user_session.php";


$user_id = $_SESSION['user_id'];
$flashcard_id = filter_input(INPUT_GET, 'flashcard_id', FILTER_VALIDATE_INT);
$error = '';
$success = '';

// get flashcard
$stmt = $db->prepare("SELECT f.id, f.question, f.answer, f.deck_id, d.deck_name 
                     FROM flashcards f
                     JOIN decks d ON f.deck_id = d.id
                     WHERE f.id = ? AND f.user_id = ?");
$stmt->bind_param("ii", $flashcard_id, $user_id);
$stmt->execute();
$flashcard = $stmt->get_result()->fetch_assoc();

if (!$flashcard) {
    header("Location: view_subjects.php?error=invalid_flashcard");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);
    
    if (empty($question) || empty($answer)) {
        $error = "Both fields are required";
    } else {
        $stmt = $db->prepare("UPDATE flashcards SET question = ?, answer = ? WHERE id = ?");
        $stmt->bind_param("ssi", $question, $answer, $flashcard_id);
        
        if ($stmt->execute()) {
            header("Location: edit_deck.php?deck_id={$flashcard['deck_id']}&success=1");
            exit();
        } else {
            $error = "Error updating flashcard: " . $db->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Flashcard</title>
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
        .edit-flashcard-container {
            margin: 90px auto 0 auto;
            padding: 25px;
            width: 90%;
            max-width: 600px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .edit-flashcard-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--primary-dark);
        }
        
        .edit-flashcard-header i {
            font-size: 1.5rem;
        }
        
        .deck-info {
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
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
        
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
            min-height: 150px;
            resize: vertical;
        }
        
        .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-update:hover {
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
    </style>
</head>
<body>



    <div class="edit-flashcard-container">
        <div class="edit-flashcard-header">
            <i class="fas fa-edit"></i>
            <h1>Edit Flashcard</h1>
        </div>
        
        <div class="deck-info">
            <i class="fas fa-layer-group"></i>
            Deck: <?= htmlspecialchars($flashcard['deck_name']) ?>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="question">Question</label>
                <textarea name="question" id="question" required><?= 
                    htmlspecialchars($flashcard['question']) 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="answer">Answer</label>
                <textarea name="answer" id="answer" required><?= 
                    htmlspecialchars($flashcard['answer']) 
                ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-update">
                    <i class="fas fa-save"></i> Update Flashcard
                </button>
                <a href="edit_deck.php?deck_id=<?= $flashcard['deck_id'] ?>" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>


</body>
</html>