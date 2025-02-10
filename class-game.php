<?php

class Game {
    public $id;
    public $name;
    public $totalplays;
    public $plays;
    public $wins;
    public $rating;
    public $bgg;
    public $parent;
    public $year;
    public $playtime;
    public $happiness;
    public $hotness;

    public function __construct($args) {
        $this->id = $args['id'];
        $this->name = $args['name'];
        $this->rating = $args['rating'];
        $this->bgg = $args['bgg'];
        $this->parent = $args['parent'];
        $this->year = $args['year'];
        $this->playtime = $args['playtime'];
        $this->totalplays = 0;
        $this->happiness = 0;
        $this->hotness = 0;
        $this->plays = 0;
        $this->wins = 0;
    }

    public function addPlays(int $plays) {
        $this->plays += $plays;
    }

    public function addTotalPlays(int $plays) {
        $this->totalplays += $plays;
    }

    public function addWins(int $wins) {
        $this->wins += $wins;
    }

    public function getName() {
        $name = $this->name;
        if ($this->parent > 0) {
            $name = '<span class="game_expansion">[Exp]</span> ' . $name;
        }
        return $name;
    }

    public function getRating() {
        return "<span class='rating rating-{$this->rating}'>{$this->rating}</span>";
    }

    public function countHappinessHotness() {
        $ratio = $this->plays && $this->totalplays ? 1 + $this->plays / $this->totalplays : 0;
        $happiness = ($this->rating - 4.5) * ($this->playtime * $this->plays);
        $hotness = $ratio * $ratio * sqrt($this->plays) * $happiness;
        
        $this->happiness = round(log10($happiness), 2);
        $this->hotness = round(log10($hotness), 2);

        if (is_nan($this->happiness)) {
            $this->happiness = 0;
        }

        if (is_nan($this->hotness)) {
            $this->hotness = 0;
        }
    }
}