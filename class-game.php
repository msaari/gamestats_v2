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
    public $playsPerYear;
    public $stayingPower;
    public $months;
    public $years;
    public $firstYear;
    public $lastYear;
    public $monthMetric;
    public $yearMetric;

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
        $this->firstYear = 9999;
        $this->lastYear = 0;
        $this->monthMetric = 0;
        $this->yearMetric = 0;
        $this->stayingPower = 0;
        $this->playsPerYear = [];
        $this->months = [];
        $this->years = [];
    }

    public function addPlays(array $play) {
        if (!isset($play['plays']) || !isset($play['date'])) {
            return;
        }
        $this->plays += $play['plays'];

        $year = date('Y', strtotime($play['date']));
        if (!isset($this->playsPerYear[$year])) {
            $this->playsPerYear[$year] = 0;
        }
        $this->playsPerYear[$year] += $play['plays'];

        $month = date('m', strtotime($play['date']));
        $metricMonth = "$year/$month";

        $this->months[$metricMonth] = true;
        $this->years[$year] = true;
        $this->monthMetric = count($this->months);
        $this->yearMetric = count($this->years);

        if ($year < $this->firstYear) {
            $this->firstYear = $year;
        }

        if ($year > $this->lastYear) {
            $this->lastYear = $year;
        }
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

    public function countStayingPower(string $from, string $to) {
        $fromYear = date('Y', strtotime($from));
        $toYear = date('Y', strtotime($to));
        $sumOfWeights = 0;
        for ($year = $fromYear; $year <= $toYear; $year++) {
            $yearWeight = pow(5/6, $toYear-$year);
            $sumOfWeights += $yearWeight;
        }

        $rawStayingPower = array_reduce(
            array_keys($this->playsPerYear),
            function($sp, $year) use ($toYear, $sumOfWeights) {
                $plays = $this->playsPerYear[$year];
                $yearWeight = pow(5/6, $toYear-$year);
                $sp['sumOfValues'] += sqrt($plays) * $yearWeight;
                $sp['weightedAverage'] = pow($sp['sumOfValues'] / $sumOfWeights, 2);
                return $sp;
            },
            ['sumOfWeights' => 0, 'sumOfValues' => 0, 'weightedAverage' => 0]
        );
        $lengthStayingPower = $rawStayingPower['weightedAverage'] * ($this->playtime / 60);
        $this->stayingPower = round($lengthStayingPower, 3);
    }

    public function echoPlays() {
        $result = $this->plays;
        if ($this->totalplays != $this->plays) {
            $result .=  "<span class='u-small pale'>&nbsp;/&nbsp; $this->totalplays</span>";
        }
        return $result;
    }

    public function echoYearMetric(string $to) {
        $toYear = date("Y", strtotime($to));
        $totalYears = $toYear - $this->firstYear + 1;
        $asterisk = $this->lastYear !== $toYear ? '*' : '';
        $result = $this->yearMetric . '/' . $totalYears . $asterisk;
        return $result;
    }
}