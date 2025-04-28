<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "student_app_db";

    $conn = new mysqli($servername, $username, $password, $dbname);
    $GLOBALS['db'] = $conn;
?>