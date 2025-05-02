<?php
require "user_session.php";


$user_id = $_SESSION['user_id'];
$deck_id = filter_input(INPUT_GET, 'deck_id', FILTER_VALIDATE_INT);
$shuffle = isset($_GET['shuffle']);

// get deck info and mastery status
$stmt = $db->prepare("SELECT d.*, s.subject_name, 
                     (SELECT COUNT(*) FROM flashcards WHERE deck_id = d.id AND user_id = d.user_id) as total_cards,
                     (SELECT COUNT(*) FROM flashcard_progress WHERE deck_id = d.id AND user_id = d.user_id AND mastered = 1) as mastered_cards
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

// calculate mastery percentage
$mastery_percentage = ($deck['total_cards'] > 0) ? round(($deck['mastered_cards'] / $deck['total_cards']) * 100) : 0;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flashcard_id = filter_input(INPUT_POST, 'flashcard_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    
    if ($flashcard_id && in_array($action, ['correct', 'incorrect'])) {
        $stmt = $db->prepare("INSERT INTO flashcard_progress 
                            (flashcard_id, deck_id, user_id, mastered, last_reviewed, next_review) 
                            VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY))
                            ON DUPLICATE KEY UPDATE 
                            mastered = VALUES(mastered),
                            last_reviewed = NOW(),
                            next_review = IF(VALUES(mastered) = 1, DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY))");
        $mastered = ($action === 'correct') ? 1 : 0;
        $stmt->bind_param("iiii", $flashcard_id, $deck_id, $user_id, $mastered);
        $stmt->execute();
        
        header("Location: flashcard.php?deck_id=$deck_id" . ($shuffle ? '&shuffle=1' : ''));
        exit();
    }
}

// current flashcards
$order_clause = $shuffle ? "ORDER BY RAND()" : "ORDER BY fp.next_review IS NULL DESC, fp.next_review ASC, fp.mastered ASC";
$stmt = $db->prepare("SELECT f.* 
                     FROM flashcards f
                     LEFT JOIN flashcard_progress fp ON f.id = fp.flashcard_id AND fp.user_id = ?
                     WHERE f.deck_id = ? AND f.user_id = ?
                     $order_clause
                     LIMIT 1");
$stmt->bind_param("iii", $user_id, $deck_id, $user_id);
$stmt->execute();
$flashcard = $stmt->get_result()->fetch_assoc();

$_SESSION['last_subject_id'] = $deck['subject_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Flashcards - <?= htmlspecialchars($deck['deck_name']) ?></title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=1">
    <style>
    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--light);
    padding: 0;
    margin: 0;
    padding-top: 100px;
    }
    .flashcard-container {
        perspective: 1000px;
        margin: 40px auto;
        max-width: 650px;
        width: 100%;
    }
    
    .flashcard {
        position: relative;
        width: 100%;
        height: 400px;
        transform-style: preserve-3d;
        transition: transform 0.7s cubic-bezier(0.4, 0.2, 0.2, 1);
        cursor: pointer;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        background: white;
    }
    
    .flashcard.flipped {
        transform: rotateY(180deg);
    }
    
    /* Front and Back Faces */
    .flashcard-front, .flashcard-back {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 30px;
        box-sizing: border-box;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .flashcard-front {
        background: white;
        color: var(--dark);
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .flashcard-back {
        background: white;
        transform: rotateY(180deg);
        color: var(--dark);
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .flashcard-content {
        font-size: 1.8rem;
        line-height: 1.6;
        text-align: center;
        width: 100%;
        padding: 30px;
        word-wrap: break-word;
        overflow-y: auto;
        max-height: 100%;
    }
    
    .flashcard-back .flashcard-content {
        color: var(--dark);
    }
    
  
    .flashcard-indicator {
        position: absolute;
        bottom: 15px;
        right: 20px;
        font-size: 0.9rem;
        opacity: 0.7;
        color: inherit;
    }
    

    .study-controls {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 40px;
    }
    
    .btn {
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);

    }

    .btn-correct {
        background-color: #4cc9f0;
        color: white;
    }
    
    .btn-correct:hover {
        background-color: #3ab7dc;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(76, 201, 240, 0.3);
    }
    
    .btn-incorrect {
        background-color: #f72585;
        color: white;
    }
    
    .btn-incorrect:hover {
        background-color: #e3126f;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(247, 37, 133, 0.3);
    }
    

    .mastery-meter {
        height: 12px;
        background: #e0e0e0;
        border-radius: 6px;
        margin: 15px 0;
        overflow: hidden;
        width: 100%;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .mastery-progress {
        height: 100%;
        background: #4CAF50;
        width: <?= $mastery_percentage ?>%;
        transition: width 0.5s ease;
    }
    

    .deck-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .deck-header h1 {
        font-size: 2.2rem;
        color: var(--primary-dark);
        margin-bottom: 10px;
    }
    
 
    @media (max-width: 768px) {
        .flashcard {
            height: 350px;
        }
        
        .flashcard-content {
            font-size: 1.5rem;
            padding: 20px;
        }
        
        .study-controls {
            flex-direction: column;
            gap: 15px;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .flashcard {
            height: 300px;
        }
        
        .flashcard-content {
            font-size: 1.3rem;
        }
    }
    
    /* Flip Animation Hint */
    .flip-hint {
        text-align: center;
        margin-top: 15px;
        color: var(--gray);
        font-size: 0.9rem;
        opacity: 0.8;
    }
</style>
</head>
<body>
    
    <div class="main-content">
        <div class="container">
            <div class="deck-header">
                <h1><?= htmlspecialchars($deck['deck_name']) ?></h1>
                <p class="text-muted"><?= htmlspecialchars($deck['subject_name']) ?></p>
                
                <div class="mastery-info">
                    <p>Mastered: <?= $mastery_percentage ?>%</p>
                    <div class="mastery-meter">
                        <div class="mastery-progress"></div>
                    </div>
                    <p><?= $deck['mastered_cards'] ?> out of <?= $deck['total_cards'] ?> cards mastered</p>
                </div>
                
                <div class="deck-actions">
                    <a href="flashcard.php?deck_id=<?= $deck_id ?>&shuffle=1" class="btn">
                        <i class="fas fa-random"></i> Shuffle Cards
                    </a>
                    <a href="decks.php?subject_id=<?= $deck['subject_id'] ?>" class="btn">
                        <i class="fas fa-arrow-left"></i> Back to Decks
                    </a>
                </div>
            </div>
            
            <?php if (!$flashcard): ?>
                <div class="alert">
                    No flashcards found in this deck. Add some cards to get started!
                </div>
            <?php else: ?>
                <div class="flashcard-container">
                    <div class="flashcard" id="flashcard">
                        <div class="flashcard-front">
                            <div class="flashcard-content">
                                <?= nl2br(htmlspecialchars($flashcard['question'])) ?>
                            </div>
                        </div>
                        <div class="flashcard-back">
                            <div class="flashcard-content">
                                <?= nl2br(htmlspecialchars($flashcard['answer'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" class="study-controls">
                        <input type="hidden" name="flashcard_id" value="<?= $flashcard['id'] ?>">
                        <button type="submit" name="action" value="incorrect" class="btn btn-incorrect">
                            <i class="fas fa-times"></i> Incorrect
                        </button>
                        <button type="submit" name="action" value="correct" class="btn btn-correct">
                            <i class="fas fa-check"></i> Correct
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // flashcard flip 
        document.getElementById('flashcard').addEventListener('click', function() {
            this.classList.toggle('flipped');
        });
        
// keyboard shortcuts// incorrect and correct kinda control iffy
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' || e.key === 'd') {
                document.querySelector('button[value="correct"]').click();
            } else if (e.key === 'ArrowLeft' || e.key === 'a') {
                document.querySelector('button[value="incorrect"]').click();
            } else if (e.key === ' ' || e.key === 'Spacebar') {
                document.getElementById('flashcard').classList.toggle('flipped');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>