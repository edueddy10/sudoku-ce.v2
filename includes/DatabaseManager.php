<?php
// File: includes/DatabaseManager.php
/**
 * DatabaseManager.php
 * Verwaltet alle Datenbankoperationen mit PDO
 */
require_once __DIR__ . '/config.php';

class DatabaseManager {
    public $pdo;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // WICHTIG für LIMIT
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Finde Benutzer nach Name
     */
    public function getUserByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE user_name = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    /**
     * Erstelle neuen Benutzer
     */
    public function createUser($username, $password = null) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO user (user_name, password_hash) VALUES (?, ?)"
        );
        $password_hash = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
        $stmt->execute([$username, $password_hash]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Erstelle neue Spielsession
     */
    public function createSession($userId, $gameId, $difficulty) {
        $sessionId = bin2hex(random_bytes(16));
        $stmt = $this->pdo->prepare(
            "INSERT INTO session (session_id, user_id, game_id, difficulty) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$sessionId, $userId, $gameId, $difficulty]);
        return $sessionId;
    }

    /**
     * Speichere Score
     */
    public function saveScore($sessionId, $points, $duration, $errors) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO score (session_id, points, duration, errors) 
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$sessionId, $points, $duration, $errors]);
    }

    /**
     * Hole alle Scores für einen Benutzer
     */
    public function getUserScores($userId, $limit = 10) {
        // Korrektur: LIMIT muss als INTEGER gebunden werden
        $stmt = $this->pdo->prepare("
            SELECT s.points, s.duration, s.errors, s.timestamp,
                   g.difficulty
            FROM score s
            JOIN session ss ON s.session_id = ss.session_id
            JOIN game g ON ss.game_id = g.game_id
            WHERE ss.user_id = ?
            ORDER BY s.points DESC, s.duration ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hole globale Leaderboard
     */
    public function getLeaderboard($limit = 20) {
        // Korrektur: LIMIT muss als INTEGER gebunden werden
        $stmt = $this->pdo->prepare("
            SELECT u.user_name as username, 
                   MAX(s.points) as max_score, 
                   COUNT(*) as games_played
            FROM score s
            JOIN session ss ON s.session_id = ss.session_id
            JOIN user u ON ss.user_id = u.user_id
            WHERE s.points > 0
            GROUP BY u.user_id, u.user_name
            ORDER BY max_score DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hole Session-Details
     */
    public function getSession($sessionId) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, u.user_name, g.title 
            FROM session s
            JOIN user u ON s.user_id = u.user_id
            JOIN game g ON s.game_id = g.game_id
            WHERE s.session_id = ?
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetch();
    }

    /**
     * Beende Session
     */
    public function endSession($sessionId) {
        $stmt = $this->pdo->prepare(
            "UPDATE session SET end_time = CURRENT_TIMESTAMP WHERE session_id = ?"
        );
        return $stmt->execute([$sessionId]);
    }

    /**
     * Hole Game ID nach Schwierigkeit
     */
    public function getGameIdByDifficulty($difficulty) {
        $stmt = $this->pdo->prepare(
            "SELECT game_id FROM game WHERE difficulty = ? LIMIT 1"
        );
        $stmt->execute([$difficulty]);
        $result = $stmt->fetch();
        return $result ? $result['game_id'] : 1;
    }
}
?>