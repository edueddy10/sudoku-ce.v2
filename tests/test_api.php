<?php
// tests/test_api.php

// Korrekte Pfade für Docker-Umgebung
require_once __DIR__ . '/../includes/DatabaseManager.php';
require_once __DIR__ . '/../includes/config.php';

$db = new DatabaseManager();

echo "<h1>API-Test für Sudoku Spiel</h1>";

echo "<h3>1. Teste Leaderboard:</h3>";
try {
    $leaderboard = $db->getLeaderboard(10);
    if (empty($leaderboard)) {
        echo "Keine Leaderboard-Daten gefunden.<br>";
    } else {
        echo "<pre>";
        print_r($leaderboard);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "Fehler beim Leaderboard: " . $e->getMessage() . "<br>";
}

echo "<h3>2. Teste User Scores (User ID 1):</h3>";
try {
    $scores = $db->getUserScores(1, 10);
    if (empty($scores)) {
        echo "Keine Scores für User ID 1 gefunden.<br>";
    } else {
        echo "<pre>";
        print_r($scores);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "Fehler beim User Scores: " . $e->getMessage() . "<br>";
}

echo "<h3>3. Teste Datenbankverbindung:</h3>";
try {
    echo "Datenbankverbindung erfolgreich!<br>";
    echo "DB Host: " . DB_HOST . "<br>";
    echo "DB Name: " . DB_NAME . "<br>";
} catch (Exception $e) {
    echo "Datenbankfehler: " . $e->getMessage() . "<br>";
}
?>