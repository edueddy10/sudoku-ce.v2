<?php
// File: game.php
/**
 * game.php
 * Hauptspielseite für Sudoku
 */

session_start();

// Prüfe ob user eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Hole Benutzername aus Session (mit Fallback)
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Gast';
$csrf_token = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku - Spielseite</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container"
     data-csrf-token="<?php echo htmlspecialchars($csrf_token); ?>"
     data-user-id="<?php echo htmlspecialchars($_SESSION['user_id']); ?>"
     data-username="<?php echo htmlspecialchars($username); ?>">

    <header>
        <h1>🎮 Sudoku</h1>
        <div class="game-info">
            <div class="info-item">
                <span>👤</span>
                <span id="player-name"><?php echo htmlspecialchars($username); ?></span>
            </div>
            <div class="info-item">
                <span>⏱️</span>
                <span id="timer">00:00</span>
            </div>
            <div class="info-item">
                <span>❤️</span>
                <span id="lives-count">3</span>
            </div>
            <div class="info-item">
                <span>⚠️</span>
                <span id="errors-count">0</span>
            </div>
            <div class="info-item">
                <button id="logout-btn" class="btn-small">Logout</button>
            </div>
        </div>
    </header>

    <main>
        <!-- GAME CONTROLS -->
        <div id="game-controls">
            <h2>Spiel starten</h2>
            <div class="difficulty-buttons">
                <button class="btn" id="new-game-easy">🟢 Leicht</button>
                <button class="btn" id="new-game-medium">🟡 Mittel</button>
                <button class="btn" id="new-game-hard">🔴 Schwer</button>
            </div>

            <div class="stats-section" style="margin-top: 20px;">
                <h3>Meine Statistiken</h3>
                <div id="user-stats">
                    <p>Lade Statistiken...</p>
                </div>
            </div>
        </div>

        <!-- GAME AREA -->
        <div class="game-area">
            <div id="sudoku-board" style="display: none;"></div>

            <div id="numpad-container" style="display: none;">
                <div class="numpad-title">Zahleneingabe</div>
                <div class="numpad">
                    <?php for($i = 1; $i <= 9; $i++): ?>
                        <button class="numpad-btn" data-number="<?php echo $i; ?>"><?php echo $i; ?></button>
                    <?php endfor; ?>
                    <button class="numpad-btn delete">🗑️</button>
                    <button class="numpad-btn clear" style="grid-column: 2/3;">C</button>
                </div>
                <div class="numpad-hint">Klicke auf ein Feld, dann auf eine Zahl</div>
            </div>
        </div>

        <!-- LEADERBOARD -->
        <div id="leaderboard-container" style="display: none; margin-top: 30px;">
            <h2>🏆 Bestenliste</h2>
            <div id="leaderboard-content">
                <p>Lade Bestenliste...</p>
            </div>
        </div>

        <!-- MESSAGE -->
        <div id="message" class="message hidden"></div>
    </main>
</div>

<!-- KEIN inline script mehr -->
<script src="js/game.js"></script>
</body>
</html>