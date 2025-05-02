<?php
require "user_session.php";

$user_id = $_SESSION['user_id'];
$deck_id = filter_input(INPUT_GET, 'deck_id', FILTER_VALIDATE_INT);
$error = $_GET['error'] ?? '';
$success = isset($_GET['success']);

// get deck 
$stmt = $db->prepare("SELECT d.id, d.deck_name, s.subject_name, s.id as subject_id
                     FROM decks d
                     JOIN subjects s ON d.subject_id = s.id
                     WHERE d.id = ? AND d.user_id = ?");
$stmt->bind_param("ii", $deck_id, $user_id);
$stmt->execute();
$deck = $stmt->get_result()->fetch_assoc();

if (!$deck) {
    header("Location: view_subjects.php?error=invalid_deck");
    exit();
}

// get flashcards in this deck
$stmt = $db->prepare("SELECT id, question, answer 
                     FROM flashcards 
                     WHERE deck_id = ? AND user_id = ?
                     ORDER BY created_at DESC");
$stmt->bind_param("ii", $deck_id, $user_id);
$stmt->execute();
$flashcards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// handle form submission for updating deck name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deck_name'])) {
    $new_deck_name = trim($_POST['deck_name']);
    
    if (!empty($new_deck_name)) {
        $update_stmt = $db->prepare("UPDATE decks SET deck_name = ? WHERE id = ? AND user_id = ?");
        $update_stmt->bind_param("sii", $new_deck_name, $deck_id, $user_id);
        
        if ($update_stmt->execute()) {
            header("Location: edit_deck.php?deck_id=$deck_id&success=1");
            exit();
        } else {
            header("Location: edit_deck.php?deck_id=$deck_id&error=update_failed");
            exit();
        }
    } else {
        header("Location: edit_deck.php?deck_id=$deck_id&error=empty_name");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Deck</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=1">
    <style>
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light);
            padding: 0;
            margin: 0;
            padding-top: 60px;
            padding-bottom: 30px;
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
        
        .edit-deck-container {
            margin: 90px auto 0 auto;
            padding: 25px;
            width: 90%;
            max-width: 800px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .edit-deck-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--primary-dark);
        }
        
        .edit-deck-header i {
            font-size: 1.5rem;
        }
        
        .deck-path {
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        
        .deck-section {
            margin-bottom: 30px;
        }
        
        .deck-section h2 {
            color: var(--primary-dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-update {
            background-color: var(--primary);
            color: white;
            border: none;
            max-width: 175px; 
        }
        
        .btn-update:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-back {
            background-color: var(--light);
            color: var(--dark);
            border: 1px solid #e0e0e0;
        }
        
        .btn-back:hover {
            background-color: #f0f0f0;
        }
        
        .btn-add-flashcard {
            background-color: var(--success);
            color: white;
        }
        
        .btn-add-flashcard:hover {
            opacity: 0.9;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .flashcard-list {
            margin-top: 20px;
        }
        
        .flashcard-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 15px;
            background: var(--light);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .flashcard-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .flashcard-preview {
            flex: 1;
            min-width: 0;
        }
        
        .flashcard-preview strong {
            color: var(--primary);
        }
        
        .flashcard-actions {
            display: flex;
            gap: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: var(--dark);
        }
        
        .empty-state i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>


    <div class="edit-deck-container">
        <div class="edit-deck-header">
            <i class="fas fa-edit"></i>
            <h1>Manage Deck</h1>
        </div>
        
        <div class="deck-path">
            <?= htmlspecialchars($deck['subject_name']) ?> → <?= htmlspecialchars($deck['deck_name']) ?>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Deck updated successfully!
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> 
                <?= htmlspecialchars($error === 'empty_name' ? 'Deck name cannot be empty' : 
                                    ($error === 'update_failed' ? 'Failed to update deck' : $error)) ?>
            </div>
        <?php endif; ?>
        

        <div class="deck-section">
            <h2><i class="fas fa-info-circle"></i> Deck Information</h2>
            <form method="POST">
                <input type="hidden" name="deck_id" value="<?= $deck['id'] ?>">
                
                <div class="form-group">
                    <label for="deck_name">Deck Name</label>
                    <input type="text" name="deck_name" id="deck_name" 
                           value="<?= htmlspecialchars($deck['deck_name']) ?>" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-update">
                        <i class="fas fa-save"></i> Update Deck
                    </button>
                </div>
            </form>
        </div>
        

        <div class="deck-section">
            <div class="flex-between">
                <h2><i class="fas fa-cards"></i> Flashcards (<?= count($flashcards) ?>)</h2>
                <a href="create_flashcard.php?deck_id=<?= $deck['id'] ?>" class="btn btn-add-flashcard">
                    <i class="fas fa-plus"></i> Add Flashcard
                </a>
            </div>
            
            <?php if (empty($flashcards)): ?>
                <div class="empty-state">
                    <i class="fas fa-cards-blank"></i>
                    <h3>No Flashcards Yet</h3>
                    <p>Get started by adding your first flashcard</p>
                </div>
            <?php else: ?>
                <div class="flashcard-list">
                    <?php foreach ($flashcards as $flashcard): ?>
                        <div class="flashcard-item">
                            <div class="flashcard-preview">
                                <strong>Q:</strong> <?= htmlspecialchars(substr($flashcard['question'], 0, 50)) ?>...
                                <strong>A:</strong> <?= htmlspecialchars(substr($flashcard['answer'], 0, 50)) ?>...
                            </div>
                            <div class="flashcard-actions">
                                <a href="edit_flashcard.php?flashcard_id=<?= $flashcard['id'] ?>" class="btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_flashcard.php?flashcard_id=<?= $flashcard['id'] ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Delete this flashcard?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="form-actions">
            <a href="decks.php?subject_id=<?= $deck['subject_id'] ?>" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Decks
            </a>
        </div>
    </div>

</body>
</html>