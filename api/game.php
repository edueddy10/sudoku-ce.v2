<?php
// File: api/game.php
ob_start();
session_start();
require_once __DIR__ . '/../includes/DatabaseManager.php';
require_once __DIR__ . '/../includes/Sudoku.php';
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'data' => []];

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if (empty($action)) {
        throw new Exception('Keine Aktion angegeben');
    }

    $db = new DatabaseManager();

    // Öffentliche Aktionen
    $publicActions = ['get-leaderboard'];

    // Login-Check für nicht-öffentliche Aktionen
    if (!in_array($action, $publicActions)) {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Nicht eingeloggt');
        }
        $userId = $_SESSION['user_id'];
    }

    switch ($action) {
        case 'get-leaderboard':
            $limit = (int)($_POST['limit'] ?? 20);
            $leaderboard = $db->getLeaderboard($limit);
            $response['success'] = true;
            $response['data'] = $leaderboard;
            break;

        case 'start-session':
            $difficulty = $_POST['difficulty'] ?? 'medium';

            // Spiel und Session in DB anlegen
            $gameId = $db->getGameIdByDifficulty($difficulty);
            $sessionId = $db->createSession($userId, $gameId, $difficulty);

            // Sudoku generieren
            $sudoku = new Sudoku($sessionId, $userId);
            $puzzleData = $sudoku->generatePuzzle($difficulty);

            // In Session speichern
            $_SESSION['current_game'] = [
                'session_id' => $sessionId,
                'puzzle' => $puzzleData['puzzle'],
                'solution' => $puzzleData['solution'],
                'difficulty' => $difficulty,
                'lives' => 3,
                'errors' => 0,
                'start_time' => time()
            ];

            $response['success'] = true;
            $response['data'] = [
                'puzzle' => $puzzleData['puzzle'],
                'session_id' => $sessionId,
                'difficulty' => $difficulty,
                'lives' => 3
            ];
            break;

        case 'validate-move':
            if (!isset($userId)) throw new Exception('Nicht eingeloggt');

            $row = (int)$_POST['row'];
            $col = (int)$_POST['col'];
            $value = (int)$_POST['value'];

            // Prüfung ob Session Daten existieren
            if (!isset($_SESSION['current_game'])) {
                throw new Exception('Kein aktives Spiel gefunden.');
            }

            $gameData = &$_SESSION['current_game']; // Referenz nutzen für einfacheres Update
            $sessionId = $gameData['session_id'];

            // Instanz ohne Generierung, nur für Helper-Methoden
            $sudoku = new Sudoku($sessionId, $userId);

            // Lösung prüfen
            $correct = ($gameData['solution'][$row][$col] == $value);

            if ($correct) {
                // Wert ins Puzzle eintragen
                $gameData['puzzle'][$row][$col] = $value;

                // KORREKTUR: Nutze die Klasse zur Überprüfung des GANZEN Brettes
                // Wir übergeben das aktuelle Grid und die Lösung
                $isComplete = $sudoku->isComplete($gameData['puzzle'], $gameData['solution']);

                if ($isComplete) {
                    // Spiel gewonnen
                    $duration = time() - $gameData['start_time'];
                    $score = $sudoku->calculateScore($duration, $gameData['errors'], $gameData['difficulty']);

                    $db->saveScore($sessionId, $score, $duration, $gameData['errors']);
                    $db->endSession($sessionId);

                    unset($_SESSION['current_game']);

                    $response['data']['completed'] = true;
                    $response['data']['score'] = $score;
                } else {
                    $response['data']['completed'] = false;
                }
            } else {
                // Fehlerbehandlung
                $gameData['errors']++;
                $gameData['lives']--;

                $response['data']['game_over'] = ($gameData['lives'] <= 0);
                $response['data']['lives'] = $gameData['lives'];

                if ($gameData['lives'] <= 0) {
                    $db->endSession($sessionId);
                    unset($_SESSION['current_game']);
                }
            }

            $response['success'] = true;
            $response['data']['correct'] = $correct;
            break;

        case 'get-user-scores':
            if (!isset($userId)) throw new Exception('Nicht eingeloggt');
            $limit = (int)($_POST['limit'] ?? 10);
            $scores = $db->getUserScores($userId, $limit);
            $response['success'] = true;
            $response['data'] = $scores;
            break;

        default:
            throw new Exception('Ungültige Aktion');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

ob_end_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>