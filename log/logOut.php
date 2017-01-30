<?php
session_start();
//var_dump($_SESSION);

if (!isset($_SESSION['user_log_id'])) {
    header("Location: signIn.php");
}elseif(isset($_SESSION['user_log_id'])!="") {
    header("Location: /index.php");
}


if (isset($_GET['logout'])) {
    unset($_SESSION['user_log_id']);
    session_unset();
    session_destroy();
    header("Location: signIn.php");
    exit;
}