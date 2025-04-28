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
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .decks-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--primary-dark);
        }
        
        .mastery-meter {
            height: 20px;
            background: #e0e0e0;
            border-radius: 10px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .mastery-progress {
            height: 100%;
            background: #4CAF50;
            width: <?= $mastery_percentage ?>%;
            transition: width 0.3s ease;
        }
        
        .study-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
        }
        
        .btn-correct {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-incorrect {
            background-color: #f72585;
            color: white;
        }
        
        .flashcard-container {
            perspective: 1000px;
            margin: 30px auto;
            max-width: 600px;
        }
        
        .flashcard {
            position: relative;
            width: 100%;
            height: 300px;
            transform-style: preserve-3d;
            transition: transform 0.6s;
            cursor: pointer;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .flashcard.flipped {
            transform: rotateY(180deg);
        }
        
        .flashcard-front, .flashcard-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
            background: white;
            border-radius: 10px;
            border: 1px solid #ddd;
            overflow: auto;
        }
        
        .flashcard-back {
            background: #f9f9f9;
            transform: rotateY(180deg);
        }
        
        .flashcard-content {
            font-size: 1.5rem;
            line-height: 1.6;
            text-align: center;
            width: 100%;
            padding: 20px;
            color: #333;
            word-wrap: break-word;
        }
        
        .deck-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .deck-actions {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
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
        
// keyboard shortcuts testing!!
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