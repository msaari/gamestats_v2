<?php

class UI {
    private $db;
    private $status;
    private $playFormID;

	public function __construct($db) {
        $this->db = $db;
        $this->playFormID = 0;
    }

    public function setStatus($status, $message) {
		$this->status[$status] = $message;
	}

    public function showPlayForm(int $play) {
        $this->playFormID = $play;
    }

    public function playForm(int $play = 0) {
        if ($play) {
            $play = $this->db->getPlay($play);
            $game = $this->db->getGame($play['game']);
            $date = $play['date'];
            $game = $game['name'];
            $plays = $play['plays'];
            $wins = $play['wins'];
            $players = $play['players'];
        } else {
            $players = 2;
            $date = date("Y-m-d");
            $wins = 0;
            $plays = 1;
            $game = '';
        }

        if (isset($_POST['date'])) {
            $date = $_POST['date'];
        }
        if (isset($_POST['players'])) {
            $players = $_POST['players'];
        }

        ?>
<form method="post" class="o-container o-container--small c-card u-high playform">
    <div class="c-card__body">
        <div class="o-form-element">
            <label for="date" class="c-label">Pvm:
            <input type="date" name="date" value="<?php echo $date; ?>" class="c-field c-field--label">
            </label>
        </div>

        <div class="o-form-element">
            <label for="game" class="c-label">Peli:
            <input type="text" name="game" list="games" value="<?php echo $game; ?>" class="c-field c-field--label">
            </label>
            </div>

        <div class="o-grid">
            <div class="o-form-element o-grid-cell">
                <label for="plays" class="c-label">Pelikerrat:
                <input type="number" name="plays"  value="<?php echo $plays; ?>" class="c-field c-field--label">
                </label>
            </div>

            <div class="o-form-element o-grid-cell">
                <label for="wins">Voitot:
                <input type="number" name="wins" value="<?php echo $wins; ?>" class="c-field c-field--label">
                </label>
            </div>

            <div class="o-form-element o-grid-cell">
                <label for="players">Pelaajat:
                <input type="number" name="players" value="<?php echo $players; ?>" class="c-field c-field--label">
                </label>
            </div>
        </div>
    </div>
    
    <input type="hidden" name="action" value="save_play">
    <?php
        if ($play) {
            ?>
    <input type="hidden" name="id" value="<?php echo $play['id']; ?>" />
            <?php
        }
    ?>
    <footer class="c-card__footer">
        <button type="submit" class="c-button c-button--brand c-button--block">
            Tallenna
        </button>
    </footer>
</form>

<br />
<hr />

        <?php
    }

    public function showHeader() {
		?>
<!DOCTYPE html>
<html>
<head>
    <title>Gamestats</title>
    <link rel="stylesheet" href="https://unpkg.com/@blaze/css@x.x.x/dist/blaze/blaze.css">

<?php if (isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'plays') : ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

    <style>    	
        .playform .o-grid {
            gap: 10px;
        }

        .wide_cell-4, .c-table__row--heading .wide_cell-4 {
            flex-grow: 4;
        }

        .wide_cell-2, .c-table__row--heading .wide_cell-2 {
            flex-grow: 2;
        }

        .c-button.c-button--brand {
            --button-background: #2c3e50;
        }

        .pale {
            color: #bbbbbb;
        }
        
        .game_expansion {
            color: #006400;
            font-size: small;
            font-variant-caps: all-small-caps;
            margin-right: 0.5em;
        }

        .rating {
            display: inline-block;
            width: 30px;
            height: 25px;
            text-align: center;
            color: #000000;
        }

        .rating-10 {
            background-color: #00cc00;
        }

        .rating-9 {
            background-color: #33cc99;
        }

        .rating-8 {
            background-color: #66ff99;
        }

        .rating-7 {
            background-color: #99ffff;
        }

        .rating-6 {
            background-color: #9999ff;
        }

        .rating-5 {
            background-color: #cc99ff;
        }

        .rating-4 {
            background-color: #ff66cc;
        }

        .rating-3 {
            background-color: #ff6699;
        }

        .rating-2 {
            background-color: #ff3366;
        }

        .rating-1 {
            background-color: #ff0000;
        }

        .c-table__row--heading .c-table__cell.u-small {
            font-size: var(--text-font-size-small);
        }

        input[type="date"] {
            font-family: Helvetica, sans-serif
        }

        .spacer {
            height: 1em;
        }

</style>
</head>
<body>
    <div class="o-container o-container--large u-text">
        <div role="tablist" class="c-tabs">
            <div class="c-tabs__nav">
                <div class="c-tabs__headings">
                    <a href="/?tab=plays" role="tab" class="c-tab-heading <?php isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'plays' && printf('c-tab-heading--active') ?>">Pelikerrat</a>
                    <a href="/?tab=games" role="tab" class="c-tab-heading <?php isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'games' && printf('c-tab-heading--active') ?>">Pelit</a>
                </div>
            </div>
        </div>

        <?php
	}

    public function showFooter() {
        ?>
        <datalist id="games">
        <?php
            $games = $this->db->getGames();
            $gameList = array();
            foreach ($games as $game) {
                $gameList[] = $game['name'];
            }
            sort($gameList);
            foreach ($gameList as $game) {
                echo "<option value=\"$game\">";
            }
            ?>
        </datalist>
        
    </div>
</body>
</html>
        <?php
    }

    private function displayStatus() {
		if ( isset($this->status['warning']) ) :
			?>
		<div role="alert" class="c-alert c-alert--warning"><strong>Varoitus:</strong> <?php echo $this->status['warning']; ?></div>
			<?php
		endif;
		if ( isset($this->status['success']) ) :
			?>
		<div role="alert" class="c-alert c-alert--success"><strong>Onnistui:</strong> <?php echo $this->status['success']; ?></div>
			<?php
		endif;
		if ( isset($this->status['confirm']) ) :
			?>
		<div role="alert" class="c-alert c-alert--info"><strong><?php echo $this->status['confirm']; ?></strong>
			<a class="c-button c-button--brand" href="?confirm=<?php echo $this->status['action']; ?>&id=<?php echo $this->status['id']; ?>&nonce=<?php echo time(); ?>">Kyllä!</a>
		</div>
			<?php
		endif;
	}

    public function render() {
        $this->displayStatus();
        if ($this->playFormID) {
            $this->playForm($this->playFormID);
        }
        if (isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'plays') {
            $this->showPlays();
        }
        if (isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'games') {
            $this->showGames();
        }
    }

    private function showPlays() {
        $game = '';
        if (isset($_REQUEST['game']) && !empty($_REQUEST['game'])) {
            $gameObject = $this->db->getGameByName($_REQUEST['game']);
            $game = $gameObject['name'];
        }
        $limit = 0;

        list('from' => $from, 'to' => $to) = $this->getDateRange();
        if (!isset($_REQUEST['from']) && !isset($_REQUEST['to']) && !isset($_REQUEST['game'])) {
            $limit = 120;
        }

        $plays = $this->db->getPlays(['limit' => $limit, 'game' => $game, 'from' => $from, 'to' => $to]);
        ?>

<div class="spacer"></div>

        <?php
        $this->dateRangeButtons($from, $to, 'plays');
        ?>

<div class="spacer"></div>

<form method="get">
    <div class="c-input-group">
    <div class="o-field">
        <input type="text" name="game" list="games" class="c-field" value="<?php echo $game; ?>">
    </div>
    <button type="submit" class="c-button c-button--brand">Näytä</button>
    </div>
    <input type="hidden" name="tab" value="plays">
</form>

<div class="spacer"></div>

<table class="c-table c-table--striped">
    <thead class="c-table__head">
        <tr class="c-table__row c-table__row--heading">
            <th class="c-table__cell">Pvm</th>
            <th class="c-table__cell wide_cell-4">Peli</th>
            <th class="c-table__cell">Pelikerrat</th>
            <th class="c-table__cell">Voitot</th>
            <th class="c-table__cell">Pelaajat</th>
            <th class="c-table__cell wide_cell-2">Työkalut</th>
        </tr>
    </thead>
    <tbody class="c-table__body">
        <?php

        $totals = ['plays' => 0, 'wins' => 0, 'players' => 0];
        $chartMonths = array();
        $chartYears = array();

        foreach ($plays as $play) {
            $date = date('j.n.Y', strtotime($play['date']));
            
            $monthDate = date('Y-m', strtotime($play['date']));
            if (!isset($chartMonths[$monthDate])) {
                $chartMonths[$monthDate] = 0;
            }
            $chartMonths[$monthDate] += $play['plays'];
            
            $yearDate = date('Y', strtotime($play['date']));
            if (!isset($chartYears[$yearDate])) {
                $chartYears[$yearDate] = 0;
            }
            $chartYears[$yearDate] += $play['plays'];

            $totals['plays'] += $play['plays'];
            $totals['wins'] += $play['wins'];
            $totals['players'] += $play['players'] * $play['plays'];
            ?>
        <tr class="c-table__row">
            <td class="c-table__cell"><?php echo $date; ?></td>
            <td class="c-table__cell wide_cell-4"><?php echo $play['name']; ?></td>
            <td class="c-table__cell"><?php echo $play['plays']; ?></td>
            <td class="c-table__cell"><?php echo $play['wins']; ?></td>
            <td class="c-table__cell"><?php echo $play['players']; ?></td>
            <td class="c-table__cell wide_cell-2">
                <span class="c-input-group">
                    <a href="?edit_play=<?php echo $play['id']; ?>" class="c-button c-button--brand u-xsmall">muuta</a>
                    <a href="?delete_play=<?php echo $play['id']; ?>&amp;nonce=<?php echo time(); ?>" class="c-button u-xsmall c-button--error">poista</a>
                </span>
            </td>
        </tr>
            <?php
        }

        ?>
        <tr class="c-table__row">
            <td class="c-table__cell"></td>
            <td class="c-table__cell wide_cell-4">Yhteensä</td>
            <td class="c-table__cell"><?php echo $totals['plays']; ?></td>
            <td class="c-table__cell"><?php echo $totals['wins']; ?></td>
            <td class="c-table__cell"><?php echo round($totals['players'] / $totals['plays'], 2); ?></td>
            <td class="c-table__cell wide_cell-2"></td>
        </tr>
    </tbody>
</table>

<?php
$firstMonth = min(array_keys($chartMonths));
$lastMonth = max(array_keys($chartMonths));
list($lowestYear, $lowestMonth) = explode('-', $firstMonth);
list($highestYear, $highestMonth) = explode('-', $lastMonth);
for ($i = $lowestYear; $i <= $highestYear; $i++) {
    for ($j = 1; $j <= 12; $j++) {
        if ($i === (int) $lowestYear && $j < (int) $lowestMonth) {
            continue;
        }
        if ($i === (int) $highestYear && $j > (int) $highestMonth) {
            continue;
        }
        $month = $j;
        if (strlen($month) < 2) {
            $month = "0$j";
        }
        if (!isset($chartMonths["$i-$month"])) {
            $chartMonths["$i-$month"] = 0;
        }
    }
    if (!isset($chartYears[$i])) {
        $chartYears[$i] = 0;
    }
}

ksort($chartMonths);
ksort($chartYears);
?>

<div>
    <canvas id="playChart"></canvas>
</div>

<div>
    <canvas id="yearChart"></canvas>
</div>

<script>
      const ctx = document.getElementById('playChart');
      const ctx_y = document.getElementById('yearChart');

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [
        <?php
        foreach (array_keys($chartMonths) as $month) {
            echo "'$month', ";
        }
        ?>
    ],
    datasets: [{
      label: 'Pelikerrat',
      data: [
        <?php
        echo implode(', ', $chartMonths);
        ?>
      ],
      borderWidth: 1
    }]
  },
  options: {
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

<?php if (count($chartYears) > 1) : ?>
new Chart(ctx_y, {
  type: 'bar',
  data: {
    labels: [
        <?php
        foreach (array_keys($chartYears) as $year) {
            echo "'$year', ";
        }
        ?>
    ],
    datasets: [{
      label: 'Pelikerrat',
      data: [
        <?php
        echo implode(', ', $chartYears);
        ?>
      ],
      borderWidth: 1
    }]
  },
  options: {
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});
<?php endif; ?>
</script>

        <?php
    }

    private function showGames() {
        $gamesData = $this->db->getGames();

        $games = array();
        foreach ($gamesData as $game) {
            $games[ $game['id'] ] = new Game($game);
        }

        $plays = $this->db->getAggregatePlays();
        foreach ($plays as $gameID => $play) {
            $game = $games[ $gameID ];
            $game->addTotalPlays($play['plays']);
            if ($game->parent > 0) {
                $parent = $games[ $game->parent ];
                $parent->addTotalPlays($play['plays']);

                if ($parent->parent > 0) {
                    $grandparent = $games[ $parent->parent ];
                    $grandparent->addTotalPlays($play['plays']);
                }
            }
        }

        list('from' => $from, 'to' => $to) = $this->getDateRange();

        $plays = $this->db->getPlays(['from' => $from, 'to' => $to]);
        foreach ($plays as $play) {
            $game = $games[ $play['game'] ];
            $game->addPlays($play['plays']);
            $game->addWins($play['wins']);
            if ($game->parent > 0) {
                $parent = $games[ $game->parent ];
                $parent->addPlays($play['plays']);
                $parent->addWins($play['wins']);

                if ($parent->parent > 0) {
                    $grandparent = $games[ $parent->parent ];
                    $grandparent->addPlays($play['plays']);
                    $grandparent->addWins($play['wins']);
                }
            }
            $game->countHappinessHotness();
        }

        $orderby = 'name';
        if (isset($_REQUEST['orderby'])) {
            $orderby = $_REQUEST['orderby'];
        }

        if ($orderby === 'name') {
            uasort($games, function($a, $b) { return strcasecmp($a->name, $b->name); });
        }
        if ($orderby === 'plays') {
            uasort($games, function($a, $b) { return $b->plays - $a->plays; });
        }
        if ($orderby === 'wins') {
            uasort($games, function($a, $b) { return $b->wins - $a->wins; });
        }
        if ($orderby === 'rating') {
            uasort($games, function($a, $b) { return $b->rating - $a->rating; });
        }
        if ($orderby === 'happiness') {
            uasort($games, function($a, $b) { return $b->happiness <=> $a->happiness; });
        }
        if ($orderby === 'hotness') {
            uasort($games, function($a, $b) { return $b->hotness <=> $a->hotness; });
        }
        if ($orderby === 'year') {
            uasort($games, function($a, $b) { return $a->year - $b->year; });
        }

        $i = 1;
        ?>

<div class="spacer"></div>

        <?php
        $this->dateRangeButtons($from, $to, 'games');
        ?>

<div class="spacer"></div>

<table class="c-table c-table--striped">
    <thead class="c-table__head">
        <tr class="c-table__row c-table__row--heading">
            <th class="u-small c-table__cell">#</th>
            <th class="u-small c-table__cell wide_cell-4"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'name'])?>">Peli</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'plays'])?>">Pelit</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'wins'])?>">Voitot</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'rating'])?>">Reittaus</a></th>
            <th class="u-small c-table__cell">MM</th>
            <th class="u-small c-table__cell">YM</th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'happiness'])?>">HHM</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'hotness'])?>">Hotness</a></th>
            <th class="u-small c-table__cell">SP</th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'year'])?>">Vuosi</a></th>
            <th class="u-small c-table__cell wide_cell-2">Työkalut</th>
        </tr>
    </thead>
    <tbody class="c-table__body">
        <?php

        $totals = ['plays' => 0, 'hours' => 0, 'wins' => 0, 'rating' => 0];
        foreach ($games as $game) {
            if ($game->plays < 1) {
                continue;
            }
            if (!$game->parent) {
                $totals['plays'] += $game->plays;
                $totals['wins'] += $game->wins;
                $totals['hours'] += $game->plays * $game->playtime;
                $totals['rating'] += $game->rating * $game->plays;
            }
            ?>
        <tr class="c-table__row">
            <td class="c-table__cell pale"><?php echo $i++; ?></td>
            <td class="c-table__cell wide_cell-4"><?php echo $game->getName(); ?></td>
            <td class="c-table__cell"><?php echo $game->plays ?> <span class="u-small pale">&nbsp;/&nbsp;<?php echo $game->totalplays; ?></span></td>
            <td class="c-table__cell"><?php echo $game->wins; ?></td>
            <td class="c-table__cell"><?php echo $game->getRating(); ?></td>
            <td class="c-table__cell"><?php echo "0"; ?></td>
            <td class="c-table__cell"><?php echo "0"; ?></td>
            <td class="c-table__cell"><?php echo $game->happiness; ?></td>
            <td class="c-table__cell"><?php echo $game->hotness; ?></td>
            <td class="c-table__cell"><?php echo "0"; ?></td>
            <td class="c-table__cell"><?php echo $game->year; ?></td>
            <td class="c-table__cell wide_cell-2">
                <span class="c-input-group">
                    <a href="?edit_game=<?php echo $game->id; ?>" class="c-button c-button--brand u-xsmall">muuta</a>
                    <a href="?delete_game=<?php echo $game->id; ?>&amp;nonce=<?php echo time(); ?>" class="c-button u-xsmall c-button--error">poista</a>
                </span>
            </td>
        </tr>
            <?php
        }

        ?>
        <tr class="c-table__row">
            <td class="c-table__cell"></td>
            <td class="c-table__cell wide_cell-4">Yhteiskesto <?php echo round($totals['hours'] / 60, 0); ?> tuntia.</td>
            <td class="c-table__cell"><?php echo $totals['plays']; ?></td>
            <td class="c-table__cell"><?php echo $totals['wins']; ?></td>
            <td class="c-table__cell"><?php echo round($totals['rating'] / $totals['plays'], 2); ?></td>
            <td class="c-table__cell"></td>
            <td class="c-table__cell"></td>
            <td class="c-table__cell"></td>
            <td class="c-table__cell"></td>
            <td class="c-table__cell"></td>
            <td class="c-table__cell"></td>
            <td class="c-table__cell"></td>
            <td class="c-table__cell"></td>
        </tr>
        <?php
        ?>
    </tbody>
</table>
        <?php
    }

    private function getURLString($args) {
        $pickupArgs = ['tab', '12months', 'everything', 'lastyear', 'thisyear', 'from', 'to'];
        foreach ($pickupArgs as $arg) {
            if (isset($_REQUEST[ $arg ])) {
                $args[ $arg ] = $_REQUEST[ $arg ];
            }
        }
        return http_build_query($args);
    }

    private function getDateRange() {
        $from = isset($_REQUEST['from']) ? $_REQUEST['from'] : date('Y-m-d', strtotime('first day of january this year'));
        $to = isset($_REQUEST['to']) ? $_REQUEST['to'] : date('Y-m-d');

        if (isset($_REQUEST['everything'])) {
            $from = '1970-01-01';
            $to = date("Y-m-d");
        }

        if (isset($_REQUEST['12months'])) {
            $from = date("Y-m-d", strtotime("12 months ago"));
            $to = date("Y-m-d");
        }

        if (isset($_REQUEST['lastyear'])) {
            $from = date("Y-m-d", strtotime("first day of january last year"));
            $to = date("Y-m-d", strtotime("last day of december last year"));
        }

        if (isset($_REQUEST['thisyear'])) {
            $from = date("Y-m-d", strtotime("first day of january this year"));
            $to = date("Y-m-d", strtotime("last day of december this year"));
        }
        return ['from' => $from, 'to' => $to];
    }

    private function dateRangeButtons($from, $to, $tab) {
        ?>
<form method="get">
    <label for="from">Tästä:
        <input type="date" name="from" value="<?php echo $from; ?>">
    </label>

    <label for="to">Tähän:
        <input type="date" name="to" value="<?php echo $to; ?>">
    </label>

    <?php if (isset($_REQUEST['orderby'])) : ?>
        <input type="hidden" name="orderby" value="<?php echo $_REQUEST['orderby']; ?>">
    <?php endif; ?>
    <input type="hidden" name="tab" value="<?php echo $tab; ?>">
    <button type="submit" class="c-button c-button--brand">Päivitä</button>
</form>
<form method="get">
    <?php if (isset($_REQUEST['orderby'])) : ?>
        <input type="hidden" name="orderby" value="<?php echo $_REQUEST['orderby']; ?>">
    <?php endif; ?>
    <input type="hidden" name="tab" value="<?php echo $tab; ?>">
    <button type="submit" name="everything" value="1" class="c-button c-button--<?php echo isset($_REQUEST['everything']) ? 'info' : 'brand'; ?>">Kaikki</button>
    <button type="submit" name="12months" value="1" class="c-button c-button--<?php echo isset($_REQUEST['12months']) ? 'info' : 'brand'; ?>">12 kk</button>
    <button type="submit" name="lastyear" value="1" class="c-button c-button--<?php echo isset($_REQUEST['lastyear']) ? 'info' : 'brand'; ?>">Viime vuosi</button>
    <button type="submit" name="thisyear" value="1" class="c-button c-button--<?php echo isset($_REQUEST['thisyear']) ? 'info' : 'brand'; ?>">Tämä vuosi</button>
</form>
    <?php
    }
}