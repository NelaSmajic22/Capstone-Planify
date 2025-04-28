<?php
require "user_session.php";


// ccheck if subject_id is provided in the URL
if (!isset($_GET['subject_id']) || !is_numeric($_GET['subject_id'])) {
    header("Location: view_subjects.php");
    exit();
}

$subject_id = $_GET['subject_id'];
$user_id = $_SESSION['user_id'];

// query to get decks with mastery information
$query = "SELECT d.*, 
          (SELECT COUNT(*) FROM flashcards WHERE deck_id = d.id AND user_id = d.user_id) as total_cards,
          (SELECT COUNT(*) FROM flashcard_progress WHERE deck_id = d.id AND user_id = d.user_id AND mastered = 1) as mastered_cards
          FROM decks d 
          WHERE d.subject_id = ? AND d.user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("ii", $subject_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$decks = $result->fetch_all(MYSQLI_ASSOC);

// query to get subject name
$query_subject = "SELECT subject_name FROM subjects WHERE id = ? AND user_id = ?";
$stmt_subject = $db->prepare($query_subject);
$stmt_subject->bind_param("ii", $subject_id, $user_id);
$stmt_subject->execute();
$subject_result = $stmt_subject->get_result();
$subject = $subject_result->fetch_assoc();


if (!$subject) {
    header("Location: view_subjects.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decks for <?php echo htmlspecialchars($subject['subject_name']); ?></title>
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
        .decks-content {
            margin: 90px auto 0 auto;
            padding: 25px;
            width: 90%;
            max-width: 800px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .decks-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--primary-dark);
        }
        
        .deck-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--success);
            margin-bottom: 20px;
            padding: 15px;
        }
        
        .deck-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.15);
        }
        
        .deck-card h3 {
            color: var(--primary-dark);
            margin-bottom: 10px;
        }
        
        .deck-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        .mastery-info {
            margin: 10px 0;
        }
        
        .mastery-meter {
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            margin: 5px 0;
            overflow: hidden;
        }
        
        .mastery-progress {
            height: 100%;
            background: #4CAF50;
            transition: width 0.3s ease;
        }
        
        .btn-study {
            background-color: var(--success);
            color: white;
        }
        
        .btn-study:hover {
            opacity: 0.9;
        }
        
        .btn-edit {
            background-color: var(--light);
            color: var(--primary);
            border: 1px solid #e0e0e0;
        }
        
        .btn-edit:hover {
            background-color: #f0f4ff;
        }
        
        .add-deck-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            margin-right: 15px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--light);
            color: var(--dark);
            border: 1px solid #e0e0e0;
        }
        
        .back-btn:hover {
            background-color: #f0f0f0;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
    </style>
</head>
<body>


    <div class="decks-content">
        <div class="decks-header">
            <i class="fas fa-layer-group"></i>
            <h1>Decks for <?php echo htmlspecialchars($subject['subject_name']); ?></h1>
        </div>
        
        <div class="action-buttons">
            <a href="add_deck.php?subject_id=<?php echo $subject_id; ?>" class="btn add-deck-btn">
                <i class="fas fa-plus"></i> Add New Deck
            </a>
            <a href="view_subjects.php" class="btn back-btn">
                <i class="fas fa-arrow-left"></i> Back to Subjects
            </a>
        </div>
        
        <?php if (empty($decks)): ?>
            <div class="card" style="text-align: center; padding: 30px;">
                <i class="fas fa-cards-blank" style="font-size: 2rem; color: var(--primary); margin-bottom: 15px;"></i>
                <h3>No Decks Yet</h3>
                <p>Get started by adding your first deck</p>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($decks as $deck): 
                    $mastery_percentage = ($deck['total_cards'] > 0) ? round(($deck['mastered_cards'] / $deck['total_cards']) * 100) : 0;
                ?>
                    <div class="card deck-card">
                        <h3><?php echo htmlspecialchars($deck['deck_name']); ?></h3>
                        
                        <div class="mastery-info">
                            <div class="mastery-meter">
                                <div class="mastery-progress" style="width: <?php echo $mastery_percentage; ?>%"></div>
                            </div>
                            <small>
                                <?php echo $mastery_percentage; ?>% mastered 
                                (<?php echo $deck['mastered_cards']; ?>/<?php echo $deck['total_cards']; ?> cards)
                            </small>
                        </div>
                        
                        <div class="deck-actions">
                            <a href="flashcard.php?deck_id=<?php echo $deck['id']; ?>" class="btn btn-study">
                                <i class="fas fa-book-open"></i> Study
                            </a>
                            <a href="edit_deck.php?deck_id=<?php echo $deck['id']; ?>" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_deck.php?deck_id=<?php echo $deck['id']; ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Are you sure you want to delete this deck and all its flashcards?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>