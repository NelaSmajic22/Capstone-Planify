<?php
require 'user_session.php';

if (isset($_GET['task_id']) && isset($_GET['status'])) {
    $task_id = $_GET['task_id'];
    $status = $_GET['status'];

    $sql = "UPDATE tasks SET status=? WHERE id=? AND user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $status, $task_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        echo 'Task status updated';
    } else {
        echo 'Failed to update task status';
    }
}
?>
