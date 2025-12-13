<?php
require_once '../includes/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h3>1. Prüfe Tabellen:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($tables);

    echo "<h3>2. Prüfe user Tabelle:</h3>";
    $stmt = $pdo->query("SELECT * FROM user LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($users);

    echo "<h3>3. Prüfe score Tabelle:</h3>";
    $stmt = $pdo->query("SELECT * FROM score LIMIT 10");
    $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($scores);

    echo "<h3>4. Prüfe session Tabelle:</h3>";
    $stmt = $pdo->query("SELECT * FROM session LIMIT 10");
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($sessions);

    echo "<h3>5. Teste Leaderboard Query:</h3>";
    $stmt = $pdo->prepare("
        SELECT u.user_name as username, 
               MAX(s.points) as max_score, 
               COUNT(*) as games_played
        FROM score s
        JOIN session ss ON s.session_id = ss.session_id
        JOIN user u ON ss.user_id = u.user_id
        GROUP BY u.user_id
        ORDER BY max_score DESC
        LIMIT 5
    ");
    $stmt->execute();
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($leaderboard);

} catch (PDOException $e) {
    echo "Fehler: " . $e->getMessage();
}
?>