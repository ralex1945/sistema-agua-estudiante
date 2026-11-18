<?php
session_start();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] === false) {
    header("Location: login.php");
    exit();
} else {
    header("Location: forms/index.php");
    exit();
}
?>
