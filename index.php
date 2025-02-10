<?php

define( "NONCE_LIMIT", 60 * 15 );

require_once 'class-database.php';
require_once 'class-ui.php';
require_once 'class-game.php';

$db = new Database();
$ui = new UI($db);

$ui->showHeader();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save_play') {
        $result = $db->savePlay($_POST);
        if ($result) {
            $ui->setStatus('success', 'Pelikerta tallennettu!');
        } else {
            $ui->setStatus('warning', 'Pelikerran tallentaminen ei onnistunut.');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['edit_play']) && is_numeric($_GET['edit_play'])) {
        $ui->showPlayForm($_GET['edit_play']);
    }
    if (isset($_GET['delete_play']) && is_numeric($_GET['delete_play']) && noncePasses($_GET['nonce'])) {
        $play = $db->getPlay((int) $_GET['delete_play']);
        $game = $db->getGame($play['game']);
        $ui->setStatus('confirm', "Vahvista pelikerran poistaminen: {$play['date']} pelille {$game['name']}.");
        $ui->setStatus('action', "delete_play");
        $ui->setStatus('id', $_GET['delete_play']);
    }
    if (isset($_GET['confirm']) && $_GET['confirm'] === 'delete_play' && noncePasses($_GET['nonce'])) {
        $result = $db->deletePlay((int) $_GET['id']);
        if ($result) {
            $ui->setStatus('success', 'Pelikerta poistettu!');
        } else {
            $ui->setStatus('warning', 'Pelikerran poistaminen ei onnistunut.');
        }
    }
}

$ui->render();

$ui->showFooter();

//$ui->playForm();

function noncePasses($nonce) {
	if (time() - $nonce <= NONCE_LIMIT) {
		return true;
	}
	return false;
}
