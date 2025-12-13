<?php
// File: index.php
/**
 * index.php
 * Haupteinstiegspunkt des Sudoku-Projekts
 */

session_start();

// Wenn bereits eingeloggt, gehe zu Spielseite
if (isset($_SESSION['user_id'])) {
    header('Location: game.php');
    exit;
}

// Sonst gehe zu Login
header('Location: login.php');
exit;
?>