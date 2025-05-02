<?php
require "user_session.php";


$user_id = $_SESSION['user_id'];
$deck_id = filter_input(INPUT_GET, 'deck_id', FILTER_VALIDATE_INT);
$error = '';
$temp_count = 0;
$editing = null;
$deck = [];
$_SESSION['temp_flashcards'] = $_SESSION['temp_flashcards'] ?? [];


$stmt = $db->prepare("SELECT d.id, d.deck_name, s.subject_name, s.id as subject_id 
                     FROM decks d
                     JOIN subjects s ON d.subject_id = s.id
                     WHERE d.id = ? AND d.user_id = ?");
$stmt->bind_param("ii", $deck_id, $user_id);
$stmt->execute();
$deck = $stmt->get_result()->fetch_assoc();

if (!$deck) {
    header("Location: decks.php?error=invalid_deck");
    exit();
}


$_SESSION['last_subject_id'] = $deck['subject_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clear_all'])) {
        $_SESSION['temp_flashcards'] = [];
    }
    elseif (isset($_POST['add_another']) || isset($_POST['save_deck'])) {
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        
        if (!empty($question) && !empty($answer)) {
            $_SESSION['temp_flashcards'][] = [
                'question' => $question,
                'answer' => $answer
            ];
        }
    }

    if (isset($_POST['save_deck'])) {
        try {
            $db->begin_transaction();
            
            foreach ($_SESSION['temp_flashcards'] as $card) {
                $stmt = $db->prepare("INSERT INTO flashcards 
                                    (question, answer, deck_id, user_id) 
                                    VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssii", 
                    $card['question'], 
                    $card['answer'], 
                    $deck_id, 
                    $user_id);
                $stmt->execute();
            }
            
            unset($_SESSION['temp_flashcards']);
            $db->commit();
            
            header("Location: flashcard.php?deck_id=$deck_id&success=1");
            exit();
            
        } catch (Exception $e) {
            $db->rollback();
            $error = "Error saving flashcards: " . $e->getMessage();
        }
    }
    elseif (isset($_POST['edit_card'])) {
        $edit_index = $_POST['edit_index'] ?? null;
        if (isset($_SESSION['temp_flashcards'][$edit_index])) {
            $_SESSION['editing_index'] = $edit_index;
        }
    }
    elseif (isset($_POST['update_card'])) {
        $edit_index = $_POST['edit_index'] ?? null;
        if (isset($_SESSION['temp_flashcards'][$edit_index])) {
            $_SESSION['temp_flashcards'][$edit_index] = [
                'question' => trim($_POST['question'] ?? ''),
                'answer' => trim($_POST['answer'] ?? '')
            ];
            unset($_SESSION['editing_index']);
        }
    }
    elseif (isset($_POST['delete_card'])) {
        $delete_index = $_POST['delete_index'] ?? null;
        if (isset($_SESSION['temp_flashcards'][$delete_index])) {
            array_splice($_SESSION['temp_flashcards'], $delete_index, 1);
        }
    }
    elseif (isset($_POST['cancel_edit'])) {
        unset($_SESSION['editing_index']);
    }
}

$temp_count = count($_SESSION['temp_flashcards']);
$editing = $_SESSION['editing_index'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($editing) ? 'Edit Flashcard' : 'Create Flashcards' ?></title>
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
        .create-flashcard-container {
            margin: 90px auto 0 auto;
            padding: 25px;
            width: 90%;
            max-width: 800px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .create-flashcard-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--primary-dark);
        }
        
        .create-flashcard-header i {
            font-size: 1.5rem;
        }
        
        .deck-info {
            color: var(--dark);
            margin-bottom: 20px;
            font-size: 0.9em;
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
            min-height: 100px;
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
            flex-wrap: wrap;
        }
        
        .btn-primary-action {
            background-color: var(--primary);
            color: white;
            padding: 12px 25px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary-action:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-success-action {
            background-color: var(--success);
            color: white;
            padding: 12px 25px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-success-action:hover {
            opacity: 0.9;
        }
        
        .btn-secondary-action {
            background-color: var(--light);
            color: var(--dark);
            border: 1px solid #e0e0e0;
            padding: 12px 25px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-secondary-action:hover {
            background-color: #f0f0f0;
        }
        
        .btn-danger-action {
            background-color: var(--light);
            color: var(--danger);
            border: 1px solid #e0e0e0;
            padding: 12px 25px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-danger-action:hover {
            background-color: #fff0f0;
        }
        
        .pending-cards-container {
            margin-top: 30px;
        }
        
        .pending-cards-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: var(--primary-dark);
        }
        
        .pending-card {
            padding: 15px;
            margin-bottom: 15px;
            background: var(--light);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
        }
        
        .pending-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .pending-card-content {
            flex: 1;
            min-width: 0;
        }
        
        .pending-card-content strong {
            color: var(--primary);
        }
        
        .pending-card-actions {
            display: flex;
            gap: 8px;
        }
        
        .pending-card-btn {
            padding: 6px 12px;
            font-size: 0.8rem;
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



    <div class="create-flashcard-container">
        <div class="create-flashcard-header">
            <i class="fas fa-plus-circle"></i>
            <h1><?= isset($editing) ? 'Edit Flashcard' : 'Create Flashcards' ?></h1>
        </div>
        
        <div class="deck-info">
            <i class="fas fa-layer-group"></i> Deck: <?= htmlspecialchars($deck['deck_name']) ?>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" onsubmit="return validateForm()">
            <?php if (isset($editing)): ?>
                <input type="hidden" name="edit_index" value="<?= $editing ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="question">Question</label>
                <textarea name="question" id="question" required><?= 
                    isset($editing) ? htmlspecialchars($_SESSION['temp_flashcards'][$editing]['question']) : '' 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="answer">Answer</label>
                <textarea name="answer" id="answer" required><?= 
                    isset($editing) ? htmlspecialchars($_SESSION['temp_flashcards'][$editing]['answer']) : '' 
                ?></textarea>
            </div>
            
            <div class="form-actions">
                <?php if (isset($editing)): ?>
                    <button type="submit" name="update_card" class="btn btn-primary-action">
                        <i class="fas fa-save"></i> Update Flashcard
                    </button>
                    <button type="submit" name="cancel_edit" class="btn btn-secondary-action">
                        <i class="fas fa-times"></i> Cancel Edit
                    </button>
                <?php else: ?>
                    <button type="submit" name="add_another" class="btn btn-primary-action">
                        <i class="fas fa-plus"></i> Add Another
                    </button>
                    
                    <?php if ($temp_count > 0): ?>
                        <button type="submit" name="save_deck" class="btn btn-success-action">
                            <i class="fas fa-save"></i> Save Deck (<?= $temp_count ?>)
                        </button>
                        
                        <a href="decks.php?subject_id=<?= $deck['subject_id'] ?>" class="btn btn-secondary-action">
                            <i class="fas fa-arrow-left"></i> Back to Decks
                        </a>
                        <button type="submit" name="clear_all" class="btn btn-danger-action" 
                                onclick="return confirm('Are you sure? All pending flashcards will be lost.')">
                            <i class="fas fa-trash"></i> Cancel All
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if ($temp_count > 0): ?>
            <div class="pending-cards-container">
                <div class="pending-cards-header">
                    <i class="fas fa-list"></i>
                    <h2>Pending Flashcards (<?= $temp_count ?>)</h2>
                </div>
                
                <?php if ($temp_count === 0): ?>
                    <div class="empty-state">
                        <i class="fas fa-cards-blank"></i>
                        <h3>No Flashcards Yet</h3>
                        <p>Get started by adding your first flashcard</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($_SESSION['temp_flashcards'] as $index => $card): ?>
                        <div class="pending-card">
                            <div class="pending-card-content">
                                <strong>Q<?= $index + 1 ?>:</strong> 
                                <?= htmlspecialchars(substr($card['question'], 0, 50)) ?><?= strlen($card['question']) > 50 ? '...' : '' ?>
                                <br>
                                <strong>A:</strong> 
                                <?= htmlspecialchars(substr($card['answer'], 0, 50)) ?><?= strlen($card['answer']) > 50 ? '...' : '' ?>
                            </div>
                            <div class="pending-card-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="edit_index" value="<?= $index ?>">
                                    <button type="submit" name="edit_card" class="btn btn-secondary-action pending-card-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="delete_index" value="<?= $index ?>">
                                    <button type="submit" name="delete_card" class="btn btn-danger-action pending-card-btn"
                                            onclick="return confirm('Delete this flashcard?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>

        function validateForm() {
            const question = document.getElementById('question').value.trim();
            const answer = document.getElementById('answer').value.trim();
            
            if (!question || !answer) {
                alert('Both question and answer are required');
                return false;
            }

            
            return true;
        }
        

        function confirmClear() {
            return confirm('Are you sure you want to clear all pending flashcards?');
        }
    </script>
</body>
</html>