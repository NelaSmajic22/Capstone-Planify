<?php
require "user_session.php";

// check 
if (!isset($_GET['flashcard_id']) || !is_numeric($_GET['flashcard_id'])) {
    header("Location: view_subjects.php");
    exit();
}

$flashcard_id = $_GET['flashcard_id'];

// verify
$query = "SELECT f.deck_id FROM flashcards f
          JOIN decks d ON f.deck_id = d.id
          WHERE f.id = ? AND d.user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("ii", $flashcard_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: view_subjects.php");
    exit();
}

$deck = $result->fetch_assoc();
$deck_id = $deck['deck_id'];

// delete
$delete_query = "DELETE FROM flashcards WHERE id = ?";
$delete_stmt = $db->prepare($delete_query);
$delete_stmt->bind_param("i", $flashcard_id);

if ($delete_stmt->execute()) {
    header("Location: edit_deck.php?deck_id=$deck_id&delete_success=1");
} else {
    header("Location: edit_deck.php?deck_id=$deck_id&delete_error=1");
}
exit();
?>