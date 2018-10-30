<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

require_once ('engine/enconfig.php');
require_once ('engine/database.php');
require_once ('engine/functions.php');
require_once ('engine/tempconfig.php');

$route = explode('/', $_GET['route']);

switch ($route[0]) {
	case 'callback': 
		require_once ('engine/telegram.php');
		telegram_processInput();
		break;

	case 'post': 
		require_once ('engine/post.php');
		break;

	default:
		displayStub();
}