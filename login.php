<?php
// File: login.php
/**
 * login.php
 * Landing/Login Page für Sudoku
 */

session_start();

// Wenn bereits eingeloggt, gehe zu Spielseite
if (isset($_SESSION['user_id'])) {
    header('Location: game.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku - Landing Page</title>
    <link rel="stylesheet" href="css/login_style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>🎮 Sudoku</h1>
        <p>Willkommen! Tauchen Sie ein in das klassische Rätselspiel.</p>
    </header>

    <main>
        <!-- LOGIN SECTION -->
        <div class="login-section">
            <h2>Spieler-Login</h2>

            <div id="message" class="message hidden"></div>

            <form id="loginForm">
                <div class="input-group">
                    <input
                        type="text"
                        id="username"
                        placeholder="Geben Sie Ihren Namen ein..."
                        required
                        autocomplete="off"
                        minlength="2"
                        maxlength="50"
                    >
                    <button type="submit" id="login-btn">Spielen</button>
                </div>
                <div style="text-align: center; margin-top: 10px; color: #666; font-size: 0.9em;">
                    Kein Passwort erforderlich. Einfach Namen eingeben und loslegen!
                </div>
            </form>
        </div>

        <!-- HIGHSCORE SECTION -->
        <div class="highscore-section">
            <h2>🏆 Top 20 Highscores</h2>
            <div id="highscore-content">
                <div class="no-data">
                    Lade Bestenliste...
                </div>
            </div>
        </div>
    </main>
</div>

<script src="js/login.js"></script>
</body>
</html>