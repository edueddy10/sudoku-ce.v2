CREATE TABLE IF NOT EXISTS user (
                                    user_id INT AUTO_INCREMENT PRIMARY KEY,
                                    user_name VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

CREATE TABLE IF NOT EXISTS game (
                                    game_id INT AUTO_INCREMENT PRIMARY KEY,
                                    title VARCHAR(100) DEFAULT 'Sudoku',
    difficulty ENUM('easy', 'medium', 'hard') NOT NULL
    );

CREATE TABLE IF NOT EXISTS session (
                                       session_id VARCHAR(32) PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    difficulty ENUM('easy', 'medium', 'hard') NOT NULL,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (game_id) REFERENCES game(game_id)
    );

CREATE TABLE IF NOT EXISTS score (
                                     score_id INT AUTO_INCREMENT PRIMARY KEY,
                                     session_id VARCHAR(32) NOT NULL,
    points INT NOT NULL,
    duration INT NOT NULL,
    errors INT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES session(session_id)
    );

-- Einfügen von Standard-Spielen
INSERT INTO game (title, difficulty) VALUES
                                         ('Sudoku Easy', 'easy'),
                                         ('Sudoku Medium', 'medium'),
                                         ('Sudoku Hard', 'hard');