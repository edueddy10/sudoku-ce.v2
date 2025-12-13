<?php
// File: includes/Sudoku.php

class Sudoku {
    private $grid;
    private $solution;
    private $sessionId;
    private $userId;

    public function __construct($sessionId = null, $userId = null) {
        $this->grid = array_fill(0, 9, array_fill(0, 9, 0));
        $this->solution = array_fill(0, 9, array_fill(0, 9, 0));
        $this->sessionId = $sessionId;
        $this->userId = $userId;
    }

    public function generatePuzzle($difficulty = 'medium') {
        $this->fillGrid($this->grid);
        // Deep copy der Lösung erstellen
        $this->solution = array_map(function($arr) { return $arr; }, $this->grid);

        $emptyCells = $this->getEmptyCellsByDifficulty($difficulty);
        $this->removeNumbers($emptyCells);

        return [
            'puzzle' => $this->grid,
            'solution' => $this->solution,
            'difficulty' => $difficulty,
            'session_id' => $this->sessionId
        ];
    }

    private function getEmptyCellsByDifficulty($difficulty) {
        $levels = [
            'easy' => 30,    // Weniger leere Felder für Tests, ggf. auf 20 reduzieren
            'medium' => 40,
            'hard' => 50
        ];
        return $levels[$difficulty] ?? 40;
    }

    private function fillGrid(&$grid) {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($grid[$row][$col] == 0) {
                    $numbers = range(1, 9);
                    shuffle($numbers);

                    foreach ($numbers as $num) {
                        if ($this->isValidPlacement($grid, $row, $col, $num)) {
                            $grid[$row][$col] = $num;
                            if ($this->fillGrid($grid)) return true;
                            $grid[$row][$col] = 0;
                        }
                    }
                    return false;
                }
            }
        }
        return true;
    }

    private function isValidPlacement($grid, $row, $col, $num) {
        // Optimierte Validierung in einem Loop
        $startRow = $row - $row % 3;
        $startCol = $col - $col % 3;

        for ($x = 0; $x < 9; $x++) {
            if ($grid[$row][$x] == $num) return false; // Zeile
            if ($grid[$x][$col] == $num) return false; // Spalte

            // 3x3 Box Berechnung
            $boxRow = $startRow + intdiv($x, 3);
            $boxCol = $startCol + ($x % 3);
            if ($grid[$boxRow][$boxCol] == $num) return false;
        }
        return true;
    }

    private function removeNumbers($count) {
        $attempts = $count * 2; // Sicherheit gegen Endlos-Schleifen
        while ($count > 0 && $attempts > 0) {
            $row = rand(0, 8);
            $col = rand(0, 8);

            if ($this->grid[$row][$col] != 0) {
                $this->grid[$row][$col] = 0;
                $count--;
            }
            $attempts--;
        }
    }

    /**
     * Prüft, ob das Grid komplett und korrekt ist.
     * Akzeptiert jetzt optional grid und solution als Parameter,
     * um auch ohne gespeicherten State zu funktionieren.
     */
    public function isComplete($gridToCheck = null, $solutionToCheck = null) {
        $g = $gridToCheck ?? $this->grid;
        $s = $solutionToCheck ?? $this->solution;

        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                // Prüfen ob leer ODER ungleich der Lösung
                // Hier werden die Indizes $row und $col jetzt korrekt verwendet!
                if ($g[$row][$col] == 0 || $g[$row][$col] != $s[$row][$col]) {
                    return false;
                }
            }
        }
        return true;
    }

    public function calculateScore($duration, $errors, $difficulty) {
        $basePoints = 1000;
        $timeBonus = max(0, 900 - $duration); // 15 Min Zeitfenster für Bonus

        $difficultyMultiplier = [
            'easy' => 1,
            'medium' => 1.5,
            'hard' => 2.5
        ];

        $multiplier = $difficultyMultiplier[$difficulty] ?? 1;
        // Punktabzug drastischer gestalten, damit Fehler weh tun
        $points = ($basePoints + $timeBonus - ($errors * 50)) * $multiplier;

        return max(0, (int)$points);
    }
}
?>