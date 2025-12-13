<?php
// test_fixed.php
// Korrekte Pfade für Docker-Umgebung
require_once __DIR__ . '/../includes/DatabaseManager.php';
require_once __DIR__ . '/../includes/config.php';

echo "<h1>Test mit korrigierter DatabaseManager.php</h1>";

try {
    $db = new DatabaseManager();

    echo "<h3>1. Teste Datenbankverbindung:</h3>";
    echo "✓ Verbindung erfolgreich<br>";

    echo "<h3>2. Teste Leaderboard mit LIMIT 5:</h3>";
    $leaderboard = $db->getLeaderboard(5);

    if (empty($leaderboard)) {
        echo "ℹ️ Keine Leaderboard-Daten gefunden (Score-Tabelle ist wahrscheinlich leer)<br>";
    } else {
        echo "<pre>";
        print_r($leaderboard);
        echo "</pre>";
    }

    echo "<h3>3. Teste User Scores für User ID 1:</h3>";
    $scores = $db->getUserScores(1, 5);

    if (empty($scores)) {
        echo "ℹ️ Keine Scores für User ID 1 gefunden<br>";
        echo "<h4>Mögliche Ursachen:</h4>";
        echo "<ul>";
        echo "<li>Der User hat noch keine Spiele gespielt</li>";
        echo "<li>Das Spiel wurde noch nicht korrekt beendet</li>";
        echo "<li>Die score Tabelle ist leer</li>";
        echo "</ul>";
    } else {
        echo "<pre>";
        print_r($scores);
        echo "</pre>";
    }

    echo "<h3>4. Prüfe ob Tabellen existieren:</h3>";

    // Direkte Abfrage
    $pdo = $db->pdo;
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    echo "Verfügbare Tabellen: " . implode(', ', $tables) . "<br><br>";

    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) as cnt FROM `$table`")->fetch()['cnt'];
        echo "Tabelle '$table': $count Einträge<br>";
    }

} catch (PDOException $e) {
    echo "<span style='color:red'>✗ Fehler: " . $e->getMessage() . "</span><br>";
    echo "<pre>Trace:\n" . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    echo "<span style='color:red'>✗ Allgemeiner Fehler: " . $e->getMessage() . "</span><br>";
}
?>