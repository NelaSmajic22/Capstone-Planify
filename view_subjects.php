<?php
require "user_session.php";

$user_id = $_SESSION['user_id'];
$success = isset($_GET['success']);
$error = $_GET['error'] ?? '';

// all subjects for the user
$stmt = $db->prepare("SELECT s.id, s.subject_name, 
                     (SELECT COUNT(*) FROM decks WHERE subject_id = s.id) as deck_count
                     FROM subjects s 
                     WHERE s.user_id = ?
                     ORDER BY s.subject_name");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subjects</title>
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
 
        .subjects-content {
            margin: 90px auto 0 auto;
            padding: 20px;
            width: 90%;
            max-width: 800px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .subject-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--primary-dark);
        }
        
        .subject-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--primary);
        }
        
        .subject-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.15);
        }
        
        .subject-card h3 {
            color: var(--primary-dark);
            margin-bottom: 8px;
        }
        
        .deck-count {
            color: var(--dark);
            font-size: 0.9em;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .subject-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-view {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-view:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-edit {
            background-color: var(--light);
            color: var(--primary);
            border: 1px solid #e0e0e0;
        }
        
        .btn-edit:hover {
            background-color: #f0f4ff;
        }
        
        .btn-delete {
            background-color: var(--light);
            color: var(--danger);
            border: 1px solid #e0e0e0;
        }
        
        .btn-delete:hover {
            background-color: #fff0f0;
        }
        
        .add-subject-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>



    <div class="subjects-content">
    <div class="subject-header">
    <i class="fas fa-layer-group"></i>
            <h1>My Subjects</h1>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">Subject created successfully!</div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <a href="add_subject.php" class="btn add-subject-btn">
            <i class="fas fa-plus"></i> Add New Subject
        </a>
        
        <?php if (empty($subjects)): ?>
            <div class="card" style="text-align: center; padding: 30px;">
                <i class="fas fa-book-open" style="font-size: 2rem; color: var(--primary); margin-bottom: 15px;"></i>
                <h3>No Subjects Yet</h3>
                <p>Get started by adding your first subject</p>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($subjects as $subject): ?>
                    <div class="card subject-card">
                        <h3><?= htmlspecialchars($subject['subject_name']) ?></h3>
                        <p class="deck-count">
                            <i class="fas fa-layer-group"></i>
                            <?= $subject['deck_count'] ?> deck<?= $subject['deck_count'] != 1 ? 's' : '' ?>
                        </p>
                        
                        <div class="subject-actions">
                            <a href="decks.php?subject_id=<?= $subject['id'] ?>" class="btn btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="edit_subject.php?subject_id=<?= $subject['id'] ?>" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_subject.php?subject_id=<?= $subject['id'] ?>" 
                               class="btn btn-delete"
                               onclick="return confirm('Delete this subject and all its decks?')">
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