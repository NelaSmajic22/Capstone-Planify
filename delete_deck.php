<?php
require "user_session.php";


$deck_id = filter_input(INPUT_GET, 'deck_id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

// verify ownership and get subject_id for redirect
$stmt = $db->prepare("SELECT subject_id FROM decks 
                      WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $deck_id, $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    header("Location: view_subjects.php?error=invalid_deck");
    exit();
}

// delete deck 
$stmt = $db->prepare("DELETE FROM decks WHERE id = ?");
$stmt->bind_param("i", $deck_id);

if ($stmt->execute()) {
    header("Location: decks.php?subject_id={$result['subject_id']}&success=1");
} else {
    header("Location: decks.php?subject_id={$result['subject_id']}&error=1");
}
exit();
?>