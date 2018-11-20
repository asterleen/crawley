<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

if (!file_exists('engine/enconfig.php'))
	die ('<h1>No enconfig.php file found. Configure Crawley first!</h1>');

require_once ('engine/enconfig.php');
require_once ('engine/database.php');
require_once ('engine/functions.php');
require_once ('engine/tempconfig.php');

checkFilesystem();

$route = explode('/', $_GET['route']);

switch ($route[0]) {
	case 'callback': 
		require_once ('engine/telegram.php');
		telegram_processInput();
		break;

	case 'post': 
		require_once ('engine/post.php');
		post_processRequest();
		break;

	case 'rss.xml':
		require_once ('engine/post.php');
		post_processRss();
		break;

	default:
		displayStub();
}