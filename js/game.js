// Variablen
let timerInterval;
let startTime;
let currentPuzzle;
let originalPuzzle;
let selectedCell = null;
let errorCount = 0;
let currentSessionId = null;
let currentDifficulty = null;

// CSRF Token und User-Daten
let csrfToken = '';
let userId = '';
let username = '';

// FUNKTIONEN DEFINIEREN (müssen VOR den Event-Listenern stehen!)

// Starte neues Spiel

// Debug-Funktion: Prüfe alle wichtigen Elemente
function checkElements() {
    const elements = [
        'game-controls',
        'sudoku-board',
        'numpad-container',
        'leaderboard-container',
        'leaderboard-content',
        'player-name',
        'timer',
        'lives-count',
        'errors-count'
    ];

    console.log('--- Element Check ---');
    elements.forEach(id => {
        const el = document.getElementById(id);
        console.log(`#${id}:`, el ? '✅ gefunden' : '❌ NICHT gefunden');
        if (el) {
            console.log(`  Display: ${window.getComputedStyle(el).display}`);
            console.log(`  Visibility: ${window.getComputedStyle(el).visibility}`);
        }
    });
    console.log('--- Ende Check ---');
}

// Rufe bei Bedarf auf:
// checkElements();
async function startNewGame(difficulty) {
    console.log('Starte neues Spiel mit Schwierigkeit:', difficulty);
    try {
        const formData = new FormData();
        formData.append('action', 'start-session');
        formData.append('difficulty', difficulty);
        formData.append('csrf_token', csrfToken);

        const response = await fetch('api/game.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            currentPuzzle = JSON.parse(JSON.stringify(data.data.puzzle));
            originalPuzzle = JSON.parse(JSON.stringify(data.data.puzzle));
            currentSessionId = data.data.session_id;
            currentDifficulty = data.data.difficulty;

            renderBoard(data.data.puzzle);
            updateLives(data.data.lives);
            updateErrors(0);
            errorCount = 0;
            selectedCell = null;

            document.getElementById('game-controls').style.display = 'none';
            document.getElementById('sudoku-board').style.display = 'grid';
            document.getElementById('numpad-container').style.display = 'block';
            document.getElementById('leaderboard-container').style.display = 'none';

            startTimer();
            showMessage('Viel Spaß beim Spielen!', 'info');
        } else {
            showMessage('Fehler: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Fehler:', error);
        showMessage('Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.', 'error');
    }
}

// Zeichne das Board
function renderBoard(puzzle) {
    const board = document.getElementById('sudoku-board');
    board.innerHTML = '';

    for (let row = 0; row < 9; row++) {
        for (let col = 0; col < 9; col++) {
            const cell = document.createElement('div');
            cell.className = 'sudoku-cell';
            cell.dataset.row = row;
            cell.dataset.col = col;

            if (puzzle[row][col] !== 0) {
                cell.textContent = puzzle[row][col];
                cell.classList.add('given');
            } else {
                cell.addEventListener('click', selectCell);
            }

            board.appendChild(cell);
        }
    }
}

// Zelle auswählen
function selectCell(e) {
    document.querySelectorAll('.sudoku-cell').forEach(cell => {
        cell.classList.remove('selected', 'highlight-row', 'highlight-col', 'highlight-box');
    });

    selectedCell = e.target;
    selectedCell.classList.add('selected');

    const selectedRow = parseInt(selectedCell.dataset.row);
    const selectedCol = parseInt(selectedCell.dataset.col);
    const selectedBoxRow = Math.floor(selectedRow / 3);
    const selectedBoxCol = Math.floor(selectedCol / 3);

    document.querySelectorAll('.sudoku-cell').forEach(cell => {
        const row = parseInt(cell.dataset.row);
        const col = parseInt(cell.dataset.col);

        if (row === selectedRow && col !== selectedCol) {
            cell.classList.add('highlight-row');
        }
        if (col === selectedCol && row !== selectedRow) {
            cell.classList.add('highlight-col');
        }

        const boxRow = Math.floor(row / 3);
        const boxCol = Math.floor(col / 3);
        if (boxRow === selectedBoxRow && boxCol === selectedBoxCol && !(row === selectedRow && col === selectedCol)) {
            cell.classList.add('highlight-box');
        }
    });
}

// Zahleneingabe
let isProcessing = false; // Neue Variable oben definieren

async function enterNumber(number) {
    if (isProcessing) return; // Verhindert Doppelklicks
    if (!selectedCell) {
        showMessage('Bitte wähle zuerst ein Feld aus!', 'info');
        return;
    }
    if (selectedCell.classList.contains('given')) {
        showMessage('Dieses Feld ist vorgegeben!', 'error');
        return;
    }

    // Sofortige UI-Reaktion (Optimistic UI) - optional, hier aber sicherheitshalber warten
    isProcessing = true;
    const row = parseInt(selectedCell.dataset.row);
    const col = parseInt(selectedCell.dataset.col);

    try {
        const formData = new FormData();
        formData.append('action', 'validate-move');
        formData.append('session_id', currentSessionId);
        formData.append('row', row);
        formData.append('col', col);
        formData.append('value', number);
        formData.append('csrf_token', csrfToken);

        const response = await fetch('api/game.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            if (data.data.correct) {
                currentPuzzle[row][col] = parseInt(number);
                selectedCell.textContent = number;
                selectedCell.classList.remove('error');
                selectedCell.classList.add('correct');
                // Entferne Listener, damit man richtige Felder nicht mehr ändern kann
                selectedCell.removeEventListener('click', selectCell);
                // Auswahl entfernen
                selectedCell.classList.remove('selected');
                selectedCell = null;

                if (data.data.completed) {
                    clearInterval(timerInterval);
                    showMessage(`Gewonnen! Score: ${data.data.score}`, 'success');
                    setTimeout(() => {
                        alert(`Glückwunsch! Du hast gewonnen!\nPunkte: ${data.data.score}`);
                        resetGame();
                        loadUserStats();
                        loadLeaderboard();
                    }, 500);
                }
            } else {
                errorCount++;
                updateErrors(errorCount);
                selectedCell.classList.add('error');
                updateLives(data.data.lives);

                // Kurzes visuelles Feedback
                setTimeout(() => {
                    if(selectedCell) selectedCell.classList.remove('error');
                }, 800);

                if (data.data.game_over) {
                    clearInterval(timerInterval);
                    showMessage('Game Over!', 'error');
                    setTimeout(() => {
                        alert('Game Over! Keine Leben mehr.');
                        resetGame();
                        loadUserStats();
                    }, 500);
                }
            }
        } else {
            showMessage('Fehler: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Fehler:', error);
    } finally {
        isProcessing = false; // Sperre aufheben
    }
}

// Zahl löschen
function deleteNumber() {
    if (!selectedCell || selectedCell.classList.contains('given')) return;

    const row = parseInt(selectedCell.dataset.row);
    const col = parseInt(selectedCell.dataset.col);

    if (originalPuzzle[row][col] === 0) {
        currentPuzzle[row][col] = 0;
        selectedCell.textContent = '';
        selectedCell.classList.remove('correct', 'error');
    }
}

// Zelle leeren
function clearCell() {
    deleteNumber();
}

// Timer starten
function startTimer() {
    if (timerInterval) clearInterval(timerInterval);

    startTime = Date.now();
    timerInterval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        document.getElementById('timer').textContent = formatTime(elapsed);
    }, 1000);
}

// Zeit formatieren
function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

// Update Lives
function updateLives(lives) {
    document.getElementById('lives-count').textContent = lives;
}

// Update Errors
function updateErrors(errors) {
    document.getElementById('errors-count').textContent = errors;
}

// Nachricht anzeigen
function showMessage(text, type) {
    const msg = document.getElementById('message');
    msg.textContent = text;
    msg.className = `message ${type}`;
    msg.classList.remove('hidden');
    setTimeout(() => {
        msg.classList.add('hidden');
    }, 4000);
}

// Spiel zurücksetzen
// Spiel zurücksetzen
function resetGame() {
    console.log('DEBUG: resetGame aufgerufen');

    // 1. Zeige die Game Controls
    document.getElementById('game-controls').style.display = 'block';

    // 2. Verstecke Spielfeld und Numpad
    document.getElementById('sudoku-board').style.display = 'none';
    document.getElementById('numpad-container').style.display = 'none';

    // 3. Zeige Leaderboard Container
    const leaderboardContainer = document.getElementById('leaderboard-container');
    if (leaderboardContainer) {
        leaderboardContainer.style.display = 'block';
        console.log('DEBUG: Leaderboard Container sichtbar gemacht');
    } else {
        console.error('DEBUG: Leaderboard Container nicht gefunden!');
    }

    // 4. Stelle sicher, dass content Element existiert
    if (leaderboardContainer && !document.getElementById('leaderboard-content')) {
        const contentDiv = document.createElement('div');
        contentDiv.id = 'leaderboard-content';
        contentDiv.innerHTML = '<p>Lade Bestenliste...</p>';
        leaderboardContainer.appendChild(contentDiv);
        console.log('DEBUG: Leaderboard Content Element erstellt');
    }

    // 5. Reset Variablen
    selectedCell = null;
    errorCount = 0;
    clearInterval(timerInterval);
    document.getElementById('timer').textContent = '00:00';
    updateLives(3);
    updateErrors(0);
}

// Lade Benutzer-Statistiken
async function loadUserStats() {
    try {
        const formData = new FormData();
        formData.append('action', 'get-user-scores');
        formData.append('limit', 5);
        formData.append('csrf_token', csrfToken);

        const response = await fetch('api/game.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            const statsHtml = data.data.map((score, index) => `
                <div class="score-item">
                    <strong>${index + 1}</strong>
                    ${score.points} Punkte <small>${formatTime(score.duration)}, ${score.errors} Fehler, ${score.difficulty}</small>
                </div>
            `).join('');
            document.getElementById('user-stats').innerHTML = statsHtml;
        } else {
            document.getElementById('user-stats').innerHTML = '<p>Noch keine Spiele gespielt. Starte dein erstes Spiel!</p>';
        }
    } catch (error) {
        console.error('Fehler beim Laden der Statistiken:', error);
    }
}

// Lade Leaderboard
// Lade Leaderboard
async function loadLeaderboard() {
    console.log('DEBUG: loadLeaderboard aufgerufen');

    // Warte kurz, damit das DOM aktualisiert wird
    await new Promise(resolve => setTimeout(resolve, 50));

    // Suche das Element - mehrere Möglichkeiten
    let leaderboardElement = document.getElementById('leaderboard-content');

    // Wenn nicht gefunden, versuche es erneut oder erstelle es
    if (!leaderboardElement) {
        console.warn('Element #leaderboard-content nicht gefunden, versuche es erneut...');

        // Warte etwas länger und suche erneut
        await new Promise(resolve => setTimeout(resolve, 100));
        leaderboardElement = document.getElementById('leaderboard-content');

        // Wenn immer noch nicht gefunden, prüfe ob Container existiert
        if (!leaderboardElement) {
            const container = document.getElementById('leaderboard-container');
            if (container) {
                console.warn('Container gefunden, aber content nicht. Erstelle Element...');

                // Erstelle das content Element neu
                const contentDiv = document.createElement('div');
                contentDiv.id = 'leaderboard-content';
                container.appendChild(contentDiv);
                leaderboardElement = contentDiv;
            } else {
                console.error('Weder Container noch Content gefunden. Leaderboard kann nicht geladen werden.');
                return;
            }
        }
    }

    try {
        const formData = new FormData();
        formData.append('action', 'get-leaderboard');
        formData.append('limit', 20);
        formData.append('csrf_token', csrfToken);

        console.log('DEBUG: Sende Request...');
        const response = await fetch('api/game.php', {
            method: 'POST',
            body: formData
        });

        console.log('DEBUG: Response Status:', response.status);
        const data = await response.json();
        console.log('DEBUG: Response Data:', data);

        if (data.success && data.data && data.data.length > 0) {
            const leaderboardHtml = `
                <table class="highscore-table">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Spieler</th>
                            <th>Beste Punktzahl</th>
                            <th>Spiele</th>
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

            console.log('DEBUG: Setze HTML in', leaderboardElement);
            leaderboardElement.innerHTML = leaderboardHtml;
        } else {
            console.warn('DEBUG: Keine Leaderboard-Daten:', data);
            leaderboardElement.innerHTML = '<div class="no-data">Noch keine Spiele gespielt.</div>';
        }
    } catch (error) {
        console.error('Fehler beim Laden der Bestenliste:', error);
        if (leaderboardElement) {
            leaderboardElement.innerHTML = '<div class="no-data">Fehler beim Laden der Bestenliste.</div>';
        }
    }
}

// Logout
async function logout() {
    console.log('Logout aufgerufen');
    try {
        const formData = new FormData();
        formData.append('action', 'logout');
        formData.append('csrf_token', csrfToken);

        await fetch('api/login.php', {
            method: 'POST',
            body: formData
        });

        localStorage.removeItem('csrfToken');
        sessionStorage.clear();
        window.location.href = 'login.php';
    } catch (error) {
        console.error('Logout fehlgeschlagen:', error);
        window.location.href = 'login.php';
    }
}

// Keyboard Support
document.addEventListener('keydown', (e) => {
    if (!selectedCell || selectedCell.classList.contains('given')) return;

    if ((e.key >= '1' && e.key <= '9')) {
        enterNumber(e.key);
    } else if (e.key === 'Backspace' || e.key === 'Delete') {
        deleteNumber();
        e.preventDefault();
    }
});

// Event Listener - MUSS AM ENDE STEHEN, NACH ALLEN FUNKTIONS-DEFINITIONEN!
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM geladen, initialisiere Spiel...');

    // Hole Daten aus data-attributes
    const container = document.querySelector('.container');
    if (!container) {
        console.error('Container nicht gefunden!');
        return;
    }

    csrfToken = container.getAttribute('data-csrf-token') || localStorage.getItem('csrfToken') || '';
    userId = container.getAttribute('data-user-id') || '';
    username = container.getAttribute('data-username') || '';

    console.log('User ID:', userId, 'Username:', username);

    // Token in localStorage speichern
    if (csrfToken) {
        localStorage.setItem('csrfToken', csrfToken);
    }

    // Prüfe ob Session vorhanden ist
    if (!userId || userId === '') {
        console.warn('Keine User ID gefunden, leite zu login weiter...');
        window.location.href = 'login.php';
        return;
    }

    // Überprüfe ob Elemente existieren
    const easyBtn = document.getElementById('new-game-easy');
    const mediumBtn = document.getElementById('new-game-medium');
    const hardBtn = document.getElementById('new-game-hard');

    if (!easyBtn || !mediumBtn || !hardBtn) {
        console.error('Schwierigkeits-Buttons nicht gefunden!');
        return;
    }

    console.log('Buttons gefunden, füge Event-Listener hinzu...');

    // Event-Listener für Schwierigkeits-Buttons
    easyBtn.addEventListener('click', () => startNewGame('easy'));
    mediumBtn.addEventListener('click', () => startNewGame('medium'));
    hardBtn.addEventListener('click', () => startNewGame('hard'));

    // Logout Button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }

    // Numpad-Buttons
    document.querySelectorAll('.numpad-btn[data-number]').forEach(btn => {
        btn.addEventListener('click', () => enterNumber(btn.dataset.number));
    });

    const deleteBtn = document.querySelector('.numpad-btn.delete');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', deleteNumber);
    }

    const clearBtn = document.querySelector('.numpad-btn.clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearCell);
    }

    // Lade Statistiken und Leaderboard
    loadUserStats();
    loadLeaderboard();

    console.log('Spiel initialisiert!');
});