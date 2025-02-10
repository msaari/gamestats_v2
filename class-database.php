<?php

class Database {
    private $db;
    
    public function __construct($dbname = 'database.sqlite') {
        $this->db = new SQLite3($dbname);
        $this->initialize();
    }
    
    private function initialize() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            year INTEGER,
            bgg INTEGER,
            rating INTEGER,
            playtime INTEGER,
            parent INTEGER
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_name TEXT NOT NULL,
            password TEXT NOT  NULL,
            email TEXT NOT NULL
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS plays (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            game INTEGER NOT NULL,
            date TEXT NOT NULL,
            plays INTEGER NOT NULL DEFAULT 1,
            wins INTEGER NOT NULL DEFAULT 0,
            players INTEGER NOT NULL DEFAULT 2
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS entities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            type TEXT NOT NULL
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS game_entities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            game_id INTEGER NOT NULL,
            entity_id INTEGER NOT NULL,
            FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
            FOREIGN KEY (entity_id) REFERENCES entities(id) ON DELETE CASCADE
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS game_taxonomy (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            game_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL,
            FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS sessions (
            sid TEXT PRIMARY KEY,
            user_id INTEGER NOT NULL,
            open_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
        )");
    }
    
    public function insertUser($user_name, $email, $password) {
        $user = $this->getUser($user_name);
        if ($user) {
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (user_name, password, email) VALUES (:user_name, :password, :email)");
        $stmt->bindValue(':user_name', $user_name, SQLITE3_TEXT);
        $stmt->bindValue(':password', $password, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        return $stmt->execute();
    }

    public function getUser($user_name) {
        $query = 'SELECT * FROM users WHERE user_name = :user_name';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_name', $user_name, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row;
    }

    public function checkPassword($user_name, $password) {
        $user = $this->getUser($user_name);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['password']);
    }

    public function getUsers() {
        $query = "SELECT * FROM users";
        $result = $this->db->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            var_dump($row);
        }
        return true;
    }

    public function getSessionUser($sid) {
        $stmt = $this->db->prepare("SELECT * FROM sessions WHERE sid = :sid");
        $stmt->bindValue(':sid', $sid, SQLITE3_TEXT);
        $result = $stmt->execute();
        if (!$result) {
            return false;
        }
        $session = $result->fetchArray(SQLITE3_ASSOC);
        if (isset($session['user_id'])) {
            // Control session length based on $session['open_time'] if necessary.
            return $session['user_id'];
        }
        return false;
    }

    public function setSessionUser($sid, $user_id) {
        $stmt = $this->db->prepare("INSERT INTO sessions (sid, user_id) VALUES (:sid, :user_id)");
        $stmt->bindValue(':sid', $sid, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    public function deleteSession($sid) {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE sid = :sid");
        $stmt->bindValue(':sid', $sid, SQLITE3_TEXT);
        return $stmt->execute();
    }

    public function getGameByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM games WHERE name = :name");
        $stmt->bindValue(':name', $name);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row;
    }

    public function insertGame(string $name, int $year, int $bgg, int $rating, int $playtime, string $parent, array $designers, array $publishers, string $tags) {
        if ($parent) {
            $parentGame = $this->getGameByName($parent);
            if ($parentGame) {
                $parent = $parentGame['id'];
            }
        } else {
            $parent = 0;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO games (name, year, bgg, rating, playtime, parent)
            VALUES (:name, :year, :bgg, :rating, :playtime, :parent)"
        );
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
        $stmt->bindValue(':bgg', $bgg, SQLITE3_INTEGER);
        $stmt->bindValue(':rating', $rating, SQLITE3_INTEGER);
        $stmt->bindValue(':playtime', $playtime, SQLITE3_INTEGER);
        $stmt->bindValue(':parent', $parent, SQLITE3_INTEGER);
        $success = $stmt->execute();
        if (!$success) {
            return false;
        }
        $game_id = $this->db->lastInsertRowID();

        $entityIDs = array();
        foreach ($designers as $designer) {
            $dbDesigner = $this->getEntityByName($designer);
            if (!$dbDesigner) {
                $entityIDs[] = $this->insertEntity($designer, 'designer');
            } else {
                $entityIDs[] = $dbDesigner['id'];
            }
        }
        foreach ($publishers as $publisher) {
            $dbPublisher = $this->getEntityByName($publisher);
            if (!$dbPublisher) {
                $entityIDs[] = $this->insertEntity($publisher, 'publisher');                echo "Inserted $designer to DB<br />";
            } else {
                $entityIDs[] = $dbPublisher['id'];
            }
        }
        foreach ($entityIDs as $entity_id) {
            $stmt = $this->db->prepare(
                "INSERT INTO game_entities (game_id, entity_id)
                VALUES (:game_id, :entity_id)"
            );
            $stmt->bindValue(':game_id', $game_id, SQLITE3_INTEGER);
            $stmt->bindValue(':entity_id', $entity_id, SQLITE3_INTEGER);
            $stmt->execute();
        }
        return $game_id;
    }

    public function savePlay($args) {
        $game = $this->getGameByName($args['game']);
        if (!$game) {
            $game_id = $this->insertGame($args['game'], 0, 0, 0, 0, 0, array(), array(), '');
        } else {
            $game_id = $game['id'];
        }

        if (isset($args['id'])) {
            return $this->updatePlay(
                (int) $args['id'],
                $game_id,
                $args['date'],
                (int) $args['plays'],
                (int) $args['wins'],
                (int) $args['players']
            );
        } else {
            return $this->insertPlay(
                $game_id,
                $args['date'],
                (int) $args['plays'],
                (int) $args['wins'],
                (int) $args['players']
            );
        }
    }

    public function insertPlay(int $game, string $date, int $plays, int $wins, int $players) {
        $stmt = $this->db->prepare(
            "INSERT INTO plays (game, date, plays, wins, players)
            VALUES (:game, :date, :plays, :wins, :players)"
        );
        $stmt->bindValue(':game', $game, SQLITE3_INTEGER);
        $stmt->bindValue(':date', $date, SQLITE3_TEXT);
        $stmt->bindValue(':plays', $plays, SQLITE3_INTEGER);
        $stmt->bindValue(':wins', $wins, SQLITE3_INTEGER);
        $stmt->bindValue(':players', $players, SQLITE3_INTEGER);

        return $stmt->execute();
    }

    public function updatePlay(int $id, int $game, string $date, int $plays, int $wins, int $players) {
        $stmt = $this->db->prepare(
            "UPDATE plays SET game = :game, date = :date, plays = :plays,
            wins = :wins, players = :players
            WHERE id = :id"
        );
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':game', $game, SQLITE3_INTEGER);
        $stmt->bindValue(':date', $date, SQLITE3_TEXT);
        $stmt->bindValue(':plays', $plays, SQLITE3_INTEGER);
        $stmt->bindValue(':wins', $wins, SQLITE3_INTEGER);
        $stmt->bindValue(':players', $players, SQLITE3_INTEGER);

        return $stmt->execute();
    }

    public function getEntityByName($name) {
    	$query = 'SELECT * FROM entities WHERE name = :name';
    	$stmt = $this->db->prepare($query);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row;
    }

    public function getEntityByID($id) {
    	$query = 'SELECT * FROM entities WHERE id = :id';
    	$stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row;
    }

    public function insertEntity($name, $type) {
        $stmt = $this->db->prepare("INSERT INTO entities (name, type) VALUES (:name, :type)");
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $success = $stmt->execute();
        if ($success) {
            return $this->db->lastInsertRowID();
        }
        return false;
    }

    public function getGames() {
        $stmt = $this->db->prepare('SELECT * FROM games');
        $result = $stmt->execute();
        $games = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $games[] = $row;
        }
        return $games;
    }

    public function getPlays(array $args) {
        $query = 'SELECT
            plays.id, plays.date, plays.plays, plays.wins, plays.players, plays.game, games.name
            FROM plays, games
            WHERE plays.game = games.id';
        $bind = array();
        if (isset($args['game']) && !empty($args['game'])) {
            if (!is_numeric($args['game'])) {
                $gameObj = $this->getGameByName($args['game']);
                $game = $gameObj['id'];
            }
            $query .= ' AND plays.game = :game';
            $bind[':game'] = $game;
        }
        if (isset($args['from']) && isset($args['to'])) {
            $query .= ' AND plays.date >= :from AND plays.date <= :to';
            $bind[':from'] = $args['from'];
            $bind[':to'] = $args['to'];
        }
        $query .= " ORDER BY date DESC";
        if (isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0) {
            $query .= ' LIMIT :limit';
            $bind[':limit'] = $args['limit'];
        }
        if (count($bind) > 0) {
            $stmt = $this->db->prepare($query);
            foreach ($bind as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $result = $stmt->execute();
        } else {
            $result = $this->db->query($query);
        }
        $plays = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $plays[] = $row;
        }
        return $plays;
    }

    public function getAggregatePlays() {
        $query = "SELECT game, SUM(plays) AS playSum, SUM(wins) AS winSum FROM plays
            GROUP BY game";
        $result = $this->db->query($query);
        $aggregatePlays = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $aggregatePlays[$row['game']] = ['plays' => $row['playSum'], 'wins' => $row['winSum']];
        }
        return $aggregatePlays;
    }

    public function getPlay(int $id) {
        $stmt = $this->db->prepare('SELECT * FROM plays WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function getGame(int $id) {
        $stmt = $this->db->prepare('SELECT * FROM games WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function deletePlay(int $id) {
        $stmt = $this->db->prepare('DELETE FROM plays WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute(); 
    }
}
