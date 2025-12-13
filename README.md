# KT CE, Sudoku Web-Spiel

## Projektbeschreibung

Im Rahmen der LV KT Communication Engineering wurde dieses webbasierte Sudoku-Spiel entwickelt.
Die Anwendung trennt klar die Präsentationsschicht (Frontend mit HTML5, CSS3, JavaScript), die Logikschicht (PHP-Backend mit Spiellogik und Session-Management) und die Datenhaltungsschicht (MySQL-Datenbank).

## Features

- Voll funktionsfähiges Sudoku-Spiel mit 3 Schwierigkeitsgraden
- Benutzerverwaltung ohne Passwort (Namen-basiert)
- Echtzeit-Leaderboard mit Top 20 Spielern
- Persönliche Statistiken für jeden Spieler
- Sicherheitsfeatures (CSRF-Schutz, SQL-Injection Prevention)
- Timer & LEben-System mit Punkteberechnung
- Docker-Container für einfache Installation

## Vorraussetzungen
- Docker & Docker Compose
- Git
- Web-Browser

### Installation
```
git clone https://github.com/edueddy10/sudoku-ce.v2
cd sudoku-game
docker compose up -d --build
```
- Öffne deinen Browser:

Sudoku-Spiel:
````
http://localhost:8080
````
Datenbank:
````
http://localhost:8082
````

## Container-Übersicht

| Service            | Port | Beschreibung                  |
|--------------------|------|-------------------------------|
| Web (Apache + PHP) | 8080 | Web-Anwendung & API           |
| MySQL              | 8081 | Datenbank (extern erreichbar) |
| phpMyAdmin         | 8082 | Web-basiertes DB-Management   |

## Spiel im Detail

### Schwierigkeitsgrade:
- Einfach: 30 vorgegebene Zahlen
- Mittel: 40 vorgegebene Zahlen
- Schwer: 50 vorgegebene Zahlen

### Punkteberechnung:
#### Punkte = (1000 + Zeitbonus - Fehler × 5) × Schwierigkeitsmultiplikator
- 3 Leben pro Spiel
- Tastaturunterstützung
- Jede Eingabe wird sofort validiert

## Konfiguration
#### .env
````dotenv
DB_HOST=db
DB_NAME=sudoku_game
DB_USER=sudoku_user
DB_PASS=sudoku_password
DB_ROOT_PASSWORD=root_password
````

#### Ports anpassen
````yaml
ports:
  - "8080:80"     # Web
  - "8081:3306"   # MySQL
  - "8082:80"     # phpMyAdmin
````

#### DB-Schema
```markdown
user          session         score
-----         -------         -----
user_id       session_id      score_id
user_name     user_id         session_id
created_at    game_id         points
              difficulty      duration
              start_time      errors
              end_time        timestamp
```
