<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

function mknonce($len = 64) {
	$SNChars = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
	$SNCCount = strlen($SNChars);
	$s = '';
	while (strlen($s) < $len)
	{
		$s .= $SNChars[random_int(0, $SNCCount-1)];
	}
	return $s;
}

function displayStub() {
	header ("HTTP/1.1 404 Not Found");
	die ("<h1>Crawley isn't intended to be called directly from your browser</h1>");
}

function json_respond ($status, $payload = Array()) {
	if (CORS_ALLOW_EXTERNAL)
		header ('Access-Control-Allow-Origin: *');

	header('Content-Type: application/json');
	die(json_encode(Array('status' => (int)$status, 'payload' => $payload)));
}

function checkFilesystem() {
	$attachTypes = db_getAttachTypes();

	foreach ($attachTypes as $atype) {
		$dir = TELEGRAM_CONTENT_SAVE_PATH . '/' . $atype['attach_type_tag'];
		if (!is_dir($dir)) {
			if (!mkdir($dir)) {
				error_log('Could not create directory ' . $dir);
				header('HTTP/1.1 500 Internal Server Error');
				die('System error, see log');
			}
		}
	}
}