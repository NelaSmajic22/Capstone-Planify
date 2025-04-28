<?php
require "user_session.php";

// check subject_id 
if (!isset($_GET['subject_id']) || !is_numeric($_GET['subject_id'])) {
    header("Location: view_subjects.php");
    exit();
}

$subject_id = $_GET['subject_id'];

// verify 
$query = "SELECT id FROM subjects WHERE id = ? AND user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("ii", $subject_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: view_subjects.php");
    exit();
}

// delete subject and associated decks/flashcards
$delete_query = "DELETE FROM subjects WHERE id = ?";
$delete_stmt = $db->prepare($delete_query);
$delete_stmt->bind_param("i", $subject_id);

if ($delete_stmt->execute()) {
    header("Location: view_subjects.php?delete_success=1");
} else {
    header("Location: view_subjects.php?delete_error=1");
}
exit();
?>