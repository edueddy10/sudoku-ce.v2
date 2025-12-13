// Globale Variable für CSRF Token
let csrfToken = '';

document.addEventListener('DOMContentLoaded', function() {
    loadLeaderboard();
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
});

// Handle Login
async function handleLogin(event) {
    event.preventDefault();
    const username = document.getElementById('username').value.trim();

    // Validierung
    if (!username || username.length < 2) {
        showMessage('Bitte geben Sie mindestens 2 Zeichen ein.', 'error');
        return;
    }
    if (username.length > 50) {
        showMessage('Der Name darf maximal 50 Zeichen lang sein.', 'error');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('username', username);

        // WICHTIG: Richtiger Endpoint
        const response = await fetch('api/login.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            // Token speichern
            if (data.csrftoken) {
                csrfToken = data.csrftoken;
                localStorage.setItem('csrfToken', csrfToken);
            }
            showMessage('Login erfolgreich!', 'success');
            setTimeout(() => {
                window.location.href = 'game.php';
            }, 1000);
        } else {
            showMessage('Fehler: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Login Fehler:', error);
        showMessage('Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.', 'error');
    }
}

// Lade Leaderboard auf Login-Seite
async function loadLeaderboard() {
    try {
        const formData = new FormData();
        formData.append('action', 'get-leaderboard');
        formData.append('limit', 20);

        // WICHTIG: Richtiger Endpoint (game.php für Leaderboard)
        const response = await fetch('api/game.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            const leaderboardHtml = `
                <table class="highscore-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Rang</th>
                            <th>Spieler</th>
                            <th style="width: 120px;">Beste Punktzahl</th>
                            <th style="width: 80px;">Spiele</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.data.map((player, index) => `
                            <tr>
                                <td class="rank top-${Math.min(index + 1, 3)}">${index + 1}</td>
                                <td class="player-name">${player.username}</td>
                                <td class="score">${player.max_score}</td>
                                <td>${player.games_played}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            document.getElementById('highscore-content').innerHTML = leaderboardHtml;
        } else {
            document.getElementById('highscore-content').innerHTML = '<div class="no-data">Noch keine Highscores vorhanden. Seien Sie der erste!</div>';
        }
    } catch (error) {
        console.error('Fehler beim Laden der Bestenliste:', error);
        document.getElementById('highscore-content').innerHTML = '<div class="no-data">Fehler beim Laden der Bestenliste.</div>';
    }
}

// Zeige Nachricht an
function showMessage(text, type) {
    let messageDiv = document.getElementById('message');
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.id = 'message';
        messageDiv.className = 'message';
        document.querySelector('.login-section').insertBefore(messageDiv, document.getElementById('loginForm'));
    }
    messageDiv.textContent = text;
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 4000);
}