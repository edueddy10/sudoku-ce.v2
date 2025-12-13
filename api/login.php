<?php
/**
 * API: Login & Logout Handling
 */

// Output Buffering - VERHINDERT vorzeitige Ausgabe
ob_start();

// Session starten
session_start();

// Header setzen
header('Content-Type: application/json; charset=utf-8');

// Imports
require_once __DIR__ . '/../includes/DatabaseManager.php';
require_once __DIR__ . '/../includes/config.php';

// Standard Antwort-Struktur
$response = [
    'success' => false,
    'message' => '',
    'csrf_token' => '',
    'data' => null
];

try {
    // Hole die Aktion aus POST oder GET
    $action = $_POST['action'] ?? $_GET['action'] ?? null;

    if (!$action) {
        throw new Exception('Keine Aktion angegeben');
    }

    // Initialisiere Datenbankverbindung
    $db = new DatabaseManager();

    switch ($action) {
        case 'login':
            // Validiere Benutzernamen
            $username = trim($_POST['username'] ?? '');

            if (strlen($username) < 2) {
                throw new Exception('Benutzername muss mindestens 2 Zeichen lang sein');
            }

            if (strlen($username) > 50) {
                throw new Exception('Benutzername darf maximal 50 Zeichen lang sein');
            }

            // Sanitize username
            $username = preg_replace('/[^a-zA-Z0-9äöüßÄÖÜ\s\-_]/', '', $username);

            // Suche Benutzer in der Datenbank
            $user = $db->getUserByUsername($username);

            if (!$user) {
                // Benutzer existiert nicht → erstelle neuen Benutzer
                $userId = $db->createUser($username);
            } else {
                // Benutzer existiert bereits
                $userId = $user['user_id'];
            }

            // Initialisiere Session
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;

            // Generiere CSRF Token und speichere ihn in der Session
            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $csrfToken;

            // Erfolgreiche Antwort MIT CSRF Token
            $response['success'] = true;
            $response['message'] = 'Login erfolgreich';
            $response['csrf_token'] = $csrfToken;
            $response['data'] = [
                'user_id' => $userId,
                'username' => $username
            ];

            break;

        case 'logout':
            // Validiere CSRF Token
            $csrfToken = $_POST['csrf_token'] ?? $_SESSION['csrf_token'] ?? null;

            if (!$csrfToken || $csrfToken !== ($_SESSION['csrf_token'] ?? null)) {
                throw new Exception('Sicherheitstoken ungültig');
            }

            // Zerstöre Session
            session_destroy();

            $response['success'] = true;
            $response['message'] = 'Logout erfolgreich';

            break;

        case 'check-session':
            // Prüfe ob Benutzer eingeloggt ist
            if (isset($_SESSION['user_id'])) {
                $response['success'] = true;
                $response['message'] = 'Session aktiv';
                $response['data'] = [
                    'user_id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'csrf_token' => $_SESSION['csrf_token'] ?? ''
                ];
            } else {
                $response['success'] = false;
                $response['message'] = 'Nicht eingeloggt';
            }
            break;

        default:
            throw new Exception('Ungültige Aktion: ' . htmlspecialchars($action));
    }

} catch (Exception $e) {
    // Fehler in Response eintragen
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    http_response_code(400);
} catch (Throwable $e) {
    $response['success'] = false;
    $response['message'] = 'Ein unerwarteter Fehler ist aufgetreten';
    http_response_code(500);
}

// Output Buffering beenden und JSON ausgeben
ob_end_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?>