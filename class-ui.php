<?php

class UI {
    private $db;
    private $status;
    private $playFormID;
    private $gameFormID;

	public function __construct($db) {
        $this->db = $db;
        $this->playFormID = 0;
    }

    public function render() {
        $this->displayStatus();
        if ($this->playFormID) {
            $this->playForm($this->playFormID);
        }
        if ($this->gameFormID) {
            $this->gameForm($this->gameFormID);
        }
        if (isset($_REQUEST['tab'])) {
            switch ( $_REQUEST['tab'] ) {
                case 'plays':
                    $this->showPlays();
                    break;
                case 'games':
                    $this->showGames();
                    break;
                case 'addplay':
                    $this->playForm();
                    break;
                case 'top100':
                    $this->showTop100();
                    break;
                case 'firstplays':
                    $this->showFirstPlays();
                    break;
                case 'years':
                    $this->showYears();
                    break;
            }
        }   
    }

    public function setStatus($status, $message) {
		$this->status[$status] = $message;
	}

    public function showPlayForm(int $play) {
        $this->playFormID = $play;
    }

    public function showGameForm(int $game) {
        $this->gameFormID = $game;
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

<div class="spacer"></div>

        <?php
    }

    public function gameForm(int $game = 0) {
        if ($game) {
            $game = $this->db->getGame($game);
            $name = $game['name'];
            $year = $game['year'];
            $bgg = $game['bgg'];
            $playtime = $game['playtime'];
            $parent = $game['parent'];
            $rating = $game['rating'];
            $parentObj = $this->db->getGame($parent);
            $parentName = '';
            if ($parentObj) {
               $parentName = $parentObj['name'];
            }
            $entities = $this->db->getEntities($game['id']);
            $designers = array();
            $publishers = array();
            foreach ($entities as $entity) {
                if ($entity['type'] === 'publisher') {
                    $publishers[] = $entity['name'];
                }
                if ($entity['type'] === 'designer') {
                    $designers[] = $entity['name'];
                }
            }
            $designers = implode(', ', $designers);
            $publishers = implode(', ', $publishers);
            $tagsArray = $this->db->getTags($game['id']);
            $tags = array();
            foreach ($tagsArray as $tag) {
                $tags[] = $tag['name'];
            }
            $tags = implode(', ', $tags);
        } else {
            $name = "";
            $year = 2025;
            $playtime = 0;
            $bgg = 0;
            $parentName = "";
            $rating = 0;
            $designers = '';
            $publishers = '';
            $tags = '';
        }
        ?>
<form method="post" class="o-container o-container--small c-card u-high playform">
    <div class="c-card__body">
        <div class="o-form-element">
            <label for="name" class="c-label">Nimi:
            <input type="name" name="name" value="<?php echo $name; ?>" class="c-field c-field--label" data-1p-ignore>
            </label>
        </div>

        <div class="o-form-element">
            <label for="designers" class="c-label">Suunnittelijat:
            <input type="designers" name="designers" value="<?php echo $designers; ?>" class="c-field c-field--label" data-1p-ignore>
            </label>
        </div>

        <div class="o-form-element">
            <label for="publishers" class="c-label">Julkaisijat:
            <input type="publishers" name="publishers" value="<?php echo $publishers; ?>" class="c-field c-field--label" data-1p-ignore>
            </label>
        </div>

        <div class="o-grid">
            <div class="o-form-element o-grid-cell">
                <label for="year" class="c-label">Vuosi:
                <input type="number" name="year"  value="<?php echo $year; ?>" class="c-field c-field--label">
                </label>
            </div>

            <div class="o-form-element o-grid-cell">
                <label for="playtime">Pituus:
                <input type="number" name="playtime" value="<?php echo $playtime; ?>" class="c-field c-field--label">
                </label>
            </div>
        </div>

        <div class="o-grid">
            <div class="o-form-element o-grid-cell">
                <label for="bgg">BGG ID:
                <input type="number" name="bgg" value="<?php echo $bgg; ?>" class="c-field c-field--label">
                </label>
            </div>

            <div class="o-form-element o-grid-cell">
                <label for="rating">Reittaus:
                <input type="number" name="rating" value="<?php echo $rating; ?>" class="c-field c-field--label">
                </label>
            </div>
        </div>

        <div class="o-form-element">
            <label for="parent" class="c-label">Emopeli:
            <input type="text" name="parent" list="games" value="<?php echo $parentName; ?>" class="c-field c-field--label">
            </label>
        </div>

        <div class="o-form-element">
            <label for="tags" class="c-label">Avainsanat:
            <input type="text" name="tags" value="<?php echo $tags; ?>" class="c-field c-field--label">
            </label>
        </div>
    </div>
    
    <input type="hidden" name="action" value="save_game">
    <?php
        if ($game) {
            ?>
    <input type="hidden" name="id" value="<?php echo $game['id']; ?>" />
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
    <script type="module" src="https://unpkg.com/@blaze/atoms@x.x.x/dist/blaze-atoms/blaze-atoms.esm.js"></script>
    <script nomodule="" src="https://unpkg.com/@blaze/atoms@x.x.x/dist/blaze-atoms/blaze-atoms.js"></script>
    <meta name=viewport content="width=device-width, minimum-scale=1, initial-scale=1, user-scalable=yes">

<?php if (isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'plays') : ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

<style>
<?php require_once 'style.css'; ?>
</style>

</head>
<body>
    <div class="o-container o-container--large u-text">
        <div role="tablist" class="c-tabs">
            <div class="c-tabs__nav">
                <div class="c-tabs__headings">
                    <a href="/?tab=addplay" role="tab" class="c-tab-heading <?php isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'addplay' && printf('c-tab-heading--active') ?>">Lisää pelikerta</a>
                    <a href="/?tab=plays" role="tab" class="c-tab-heading <?php isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'plays' && printf('c-tab-heading--active') ?>">Pelikerrat</a>
                    <a href="/?tab=games" role="tab" class="c-tab-heading <?php isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'games' && printf('c-tab-heading--active') ?>">Pelit</a>
                    <a href="/?tab=top100" role="tab" class="c-tab-heading <?php isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'top100' && printf('c-tab-heading--active') ?>">Top 100</a>
                    <a href="/?tab=firstplays" role="tab" class="c-tab-heading <?php isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'firstplays' && printf('c-tab-heading--active') ?>">Debyytit</a>
                    <a href="/?tab=years" role="tab" class="c-tab-heading <?php isset($_REQUEST['tab']) && $_REQUEST['tab'] === 'years' && printf('c-tab-heading--active') ?>">Vuodet</a>
                </div>
            </div>
        </div>

        <div class="spacer"></div>

        <?php
	}

    public function showFooter() {
        ?>
        <datalist id="games">
        <?php
            $games = $this->db->getGames([]);
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
            $url = $this->getURLString([
                'confirm' => $this->status['action'],
                'id' => $this->status['id'],
                'nonce' => time(),
            ]);
			?>
		<div role="alert" class="c-alert c-alert--info"><strong><?php echo $this->status['confirm']; ?></strong>
			<a class="c-button c-button--brand" href="?<?php echo $url; ?>">Kyllä!</a>
		</div>
			<?php
		endif;
	}

    private function showPlays() {
        $game = '';
        if (isset($_REQUEST['game']) && !empty($_REQUEST['game'])) {
            $gameObject = $this->db->getGameByName($_REQUEST['game']);
            $game = $gameObject['name'];
        }
        $limit = 0;

        list('from' => $from, 'to' => $to) = $this->getDateRange();
        if (!isset($_REQUEST['from']) && !isset($_REQUEST['to']) && !isset($_REQUEST['game'])
            && !isset($_REQUEST['lastyear']) && !isset($_REQUEST['thisyear'])
            && !isset($_REQUEST['12months']) && !isset($_REQUEST['everything'])) {
            $limit = 120;
        }

        $plays = $this->db->getPlays(['limit' => $limit, 'game' => $game, 'from' => $from, 'to' => $to]);
        ?>

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
        $gamesData = $this->db->getGames([]);

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
            $game->addPlays($play);
            $game->addWins($play['wins']);
            if ($game->parent > 0) {
                $parent = $games[ $game->parent ];
                $parent->addPlays($play);
                $parent->addWins($play['wins']);

                if ($parent->parent > 0) {
                    $grandparent = $games[ $parent->parent ];
                    $grandparent->addPlays($play);
                    $grandparent->addWins($play['wins']);
                }
            }
        }

        foreach ($games as $game) {
            $game->countHappinessHotness();
            $game->countStayingPower('01-01-2001', $to);
        }

        $orderby = 'name';
        if (isset($_REQUEST['orderby'])) {
            $orderby = $_REQUEST['orderby'];
        }

        if ($orderby === 'name') {
            uasort($games, function($a, $b) { return strcasecmp($this->handleArticles($a->name), $this->handleArticles($b->name)); });
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
        if ($orderby === 'mm') {
            uasort($games, function($a, $b) { return $b->monthMetric - $a->monthMetric; });
        }
        if ($orderby === 'ym') {
            uasort($games, function($a, $b) {
                $aYears = date("Y") - $a->firstYear + 1;
                $bYears = date("Y") - $b->firstYear + 1;
                $aScore = $a->yearMetric * 2 - $aYears + $a->yearMetric;
                $bScore = $b->yearMetric * 2 - $bYears + $b->yearMetric;
                return $bScore - $aScore;
            });
        }
        if ($orderby === 'happiness') {
            uasort($games, function($a, $b) { return $b->happiness <=> $a->happiness; });
        }
        if ($orderby === 'hotness') {
            uasort($games, function($a, $b) { return $b->hotness <=> $a->hotness; });
        }
        if ($orderby === 'sp') {
            uasort($games, function($a, $b) { return $b->stayingPower <=> $a->stayingPower; });
        }
        if ($orderby === 'year') {
            uasort($games, function($a, $b) { return $a->year - $b->year; });
        }

        $i = 1;
        ?>

        <?php
        $this->dateRangeButtons($from, $to, 'games');
        ?>

<div class="spacer"></div>

        <form method="get">
        <?php if (isset($_REQUEST['orderby'])) : ?>
            <input type="hidden" name="orderby" value="<?php echo $_REQUEST['orderby']; ?>">
        <?php endif; ?>
            <input type="hidden" name="tab" value="<?php echo $_REQUEST['tab']; ?>">
            <button type="submit" name="incomplete" value="1" class="c-button c-button--brand">Keskeneräiset</button>
            <button type="submit" name="no_exp" value="1" class="c-button c-button--brand">Ei lisäosia</button>
        </form>

<div class="spacer"></div>

<table class="c-table c-table--striped">
    <thead class="c-table__head">
        <tr class="c-table__row c-table__row--heading">
            <th class="u-small c-table__cell">#</th>
            <th class="u-small c-table__cell wide_cell-4"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'name'])?>">Peli</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'plays'])?>">Pelit</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'wins'])?>">Voitot</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'rating'])?>">Reittaus</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'mm'])?>">MM</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'ym'])?>">YM</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'happiness'])?>">HHM</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'hotness'])?>">Hotness</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'sp'])?>">SP</a></th>
            <th class="u-small c-table__cell"><a class="c-link" href="/?<?php echo $this->getURLString(['orderby' => 'year'])?>">Vuosi</a></th>
            <th class="u-small c-table__cell wide_cell-2">Työkalut</th>
        </tr>
    </thead>
    <tbody class="c-table__body">
        <?php

        $totals = ['plays' => 0, 'hours' => 0, 'wins' => 0, 'rating' => 0];
        foreach ($games as $game) {
            if (isset($_REQUEST['no_exp']) && $game->parent) {
                continue;
            }

            if (isset($_REQUEST['incomplete']) && ( $game->rating || $game->year)) {
                continue;
            }

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
            <td class="c-table__cell"><?php echo $game->echoPlays(); ?></td>
            <td class="c-table__cell"><?php echo $game->wins; ?></td>
            <td class="c-table__cell"><?php echo $game->getRating(); ?></td>
            <td class="c-table__cell"><?php echo $game->monthMetric; ?></td>
            <td class="c-table__cell"><?php echo $game->echoYearMetric($to); ?></td>
            <td class="c-table__cell"><?php echo $game->happiness; ?></td>
            <td class="c-table__cell"><?php echo $game->hotness; ?></td>
            <td class="c-table__cell"><?php echo $game->stayingPower; ?></td>
            <td class="c-table__cell"><?php echo $game->year; ?></td>
            <td class="c-table__cell wide_cell-2">
                <span class="c-input-group">
                    <?php
                    $editUrl = $this->getURLString(['edit_game' => $game->id]);
                    $deleteUrl = $this->getURLString([
                        'delete_game' => $game->id,
                        'nonce' => time(),
                    ]);
                    ?>
                    <a href="?<?php echo $editUrl; ?>" class="c-button c-button--brand u-xsmall">muuta</a>
                    <a href="?<?php echo $deleteUrl; ?>" class="c-button u-xsmall c-button--error">poista</a>
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
            <td class="c-table__cell"><?php $tp = $totals['plays'] ? $totals['plays'] : 1; echo round($totals['rating'] / $tp, 2); ?></td>
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

    private function showTop100() {
        $cut_year = date("Y") - 2;
        $from = "$cut_year-01-01";
        $plays = $this->db->getPlays(['from' => $from, 'to' => date('Y-m-d'), 'rating' => 7]);

        $parents = $this->db->getParentNames();
        $games = [];
        foreach ($plays as $play) {
            $name = $play['name'];
            if (isset($parents[$name])) {
                $name = $parents[$name];
            }
            $games[$name] = true;
        }
        $games = array_keys($games);
        usort($games, array($this, 'articleSort'));
        echo "<textarea rows='100' cols='80'>";
        foreach ($games as $game) {
            echo "$game\n";
        }
        echo "</textarea>";
    }

    private function showFirstPlays() {
        $plays = $this->db->getPlays([]);

        $gameFirstPlays = [];
        foreach ($plays as $play) {
            if (!isset($gameFirstPlays[$play['name']])) {
                $gameFirstPlays[$play['name']] = $play['date'];
            } else {
                if ($play['date'] < $gameFirstPlays[$play['name']]) {
                    $gameFirstPlays[$play['name']] = $play['date'];
                }
            }
        }
        asort($gameFirstPlays);
        $currentYear = 0;
        $cumulative = 0;
        $yearData = '';
        $yearTotal = 0;

        ?>
        <blaze-accordion>
            <?php

        foreach ($gameFirstPlays as $game => $date) {
            $time = strtotime($date);
            $year = date('Y', $time);
            if ($currentYear != $year) {
                if ($currentYear != 0) {
                    $yearData .= <<<EOHTML
                            </tbody>
                        </table>
                    </blaze-accordion-pane>
                    EOHTML;
                    echo str_replace('####', $yearTotal, $yearData);
                }
                $yearData = <<<EOHTML
                <blaze-accordion-pane header="$year (yhteensä: ####)">
                    <table class="c-table c-table--striped">
                        <thead class="c-table__head">
                            <tr class="c-table__row c-table__row--heading">
                                <td class="c-table__cell">#</td>
                                <td class="c-table__cell wide_cell-4">Peli</td>
                                <td class="c-table__cell">Pvm</td>
                            </tr>
                        </thead>
                        <tbody class="c-table__body">
                EOHTML;
                $currentYear = $year;
                $yearTotal = 0;
            }
            $displayDate = date('j.n.Y', $time);
            $cumulative++;
            $yearTotal++;

            $yearData .= <<<EOHTML
                <tr class="c-table__row">
                    <td class="c-table__cell">$cumulative</td>
                    <td class="c-table__cell wide_cell-4">$game</td>
                    <td class="c-table__cell">$displayDate</td>
                </tr>
            EOHTML;
        }
        echo str_replace('####', $yearTotal, $yearData);

            ?>
                    </tbody>
                </table>
            </blaze-accordion-pane>
        </blaze-accordion>
        <?php
    }

    private function showYears() {
        $plays = $this->db->getPlays([]);
        $games = $this->db->getGames([]);

        $gameLengths = [];
        foreach ($games as $game) {
            $gameLengths[$game['name']] = $game['playtime'];
        }
        unset($games);

        $years = [];
        foreach ($plays as $play) {
            if (date('j.n.Y', strtotime($play['date'])) == '1.1.2001') {
                continue;
            }
            $year = date('Y', strtotime($play['date']));
            
            if (!isset($years[$year])) {
                $years[$year]['plays'] = 0;
                $years[$year]['games'] = [];
                $years[$year]['players'] = 0;
                $years[$year]['minutes'] = 0;
            }

            $years[$year]['plays'] += $play['plays'];
            $years[$year]['players'] += $play['plays'] * $play['players'];
            $years[$year]['minutes'] += $play['plays'] * $gameLengths[$play['name']];
            $years[$year]['games'][$play['name']] = true;
        }

        ksort($years);

        ?>
            <table class="c-table c-table--striped">
                <thead class="c-table__head">
                    <tr class="c-table__row c-table__row--heading">
                        <td class="c-table__cell">Vuosi</td>
                        <td class="c-table__cell">Erät</td>
                        <td class="c-table__cell">Pelit</td>
                        <td class="c-table__cell">Pelaajien ka</td>
                        <td class="c-table__cell">Tunnit</td>
                        <td class="c-table__cell">Keskipituus</td>
                    </tr>
                </thead>
                <tbody class="c-table__body">
            <?php

        foreach ($years as $year => $yearData) {
            $avgPlayers = round($yearData['players'] / $yearData['plays'], 2);
            $totalHours = round($yearData['minutes'] / 60, 0);
            $avgPlaytime = round($yearData['minutes'] / $yearData['plays'], 1);
            $games = count($yearData['games']);
            ?>
                <tr class="c-table__row">
                    <td class="c-table__cell"><?php echo $year; ?></td>
                    <td class="c-table__cell"><?php echo $yearData['plays']; ?></td>
                    <td class="c-table__cell"><?php echo $games; ?></td>
                    <td class="c-table__cell"><?php echo $avgPlayers; ?></td>
                    <td class="c-table__cell"><?php echo $totalHours; ?></td>
                    <td class="c-table__cell"><?php echo $avgPlaytime; ?></td>
                </tr>
            <?php
        }

        ?>
                </tbody>
            </table>
        <?php
    }

    private function getURLString($args) {
        $pickupArgs = ['tab', '12months', 'everything', 'lastyear', 'thisyear', 'from', 'to',
        'incomplete', 'no_exp'];
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

    private function handleArticles($str) {
        list($first,$rest) = explode(" ", $str . " " , 2);
        $validarticles = array("a", "an", "the");
        if (in_array(strtolower($first), $validarticles)) {
            return $rest . ", " . $first;
        }
        return $str;
    }

    private function articleSort($a, $b) {
        return strnatcasecmp($this->handleArticles($a), $this->handleArticles($b));
    }
}