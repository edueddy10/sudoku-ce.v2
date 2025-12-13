<?php
require_once 'includes/DatabaseManager.php';
require_once 'includes/config.php';

$db = new DatabaseManager();

echo "<h1>Session Creation Test</h1>";

// Test 1: Session erstellen
$userId = 1;
$gameId = $db->getGameIdByDifficulty('easy');
$sessionId = $db->createSession($userId, $gameId, 'easy');

echo "Erstellte Session ID: $sessionId<br>";

// Test 2: Prüfe ob Session existiert
$session = $db->getSession($sessionId);
if ($session) {
    echo "✅ Session gefunden in DB:<br>";
    echo "<pre>";
    print_r($session);
    echo "</pre>";
} else {
    echo "❌ Session NICHT gefunden in DB!<br>";
}

// Test 3: Score speichern
$saved = $db->saveScore($sessionId, 1000, 120, 0);
echo "Score gespeichert: " . ($saved ? '✅ ERFOLG' : '❌ FEHLER') . "<br>";

// Test 4: Prüfe ob Score existiert
$scores = $db->getUserScores($userId, 5);
if ($scores) {
    echo "✅ Scores gefunden:<br>";
    echo "<pre>";
    print_r($scores);
    echo "</pre>";
} else {
    echo "❌ Keine Scores gefunden<br>";
}
?>