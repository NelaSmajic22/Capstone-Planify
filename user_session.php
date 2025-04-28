<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // to login page if no session data found
    header('Location: login.php');
    exit();
}

// db connection 
if (!isset($db)) {
    require("open_db.php");
}

if (!isset($path)) $path = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planify - Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=1"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Navigation Bar -->
<div class="top-bar">
    <div class="logo">Planify</div>
    <div class="hamburger">&#9776;</div>
    <ul class="nav-links">
        <li><a href="dashboard.php"><i class="fas fa-home"></i><span>Home</span></a></li>
        <li><a href="tasks.php"><i class="fas fa-tasks"></i><span>To-Do</span></a></li>
        <li><a href="study_tips.php"><i class="fas fa-lightbulb"></i><span>Study Tips</span></a></li>
        <li><a href="view_subjects.php"><i class="fas fa-book-open"></i><span>Flashcards</span></a></li>
        <li><a href="pomodoro.php"><i class="fas fa-hourglass"></i><span>Pomodoro Timer</span></a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
    </ul>
</div>

<!-- hamburger menu -->
<script>
    // hamburger menu and nav links
    document.querySelector('.hamburger').addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('active');
    });
</script>

</body>
</html>