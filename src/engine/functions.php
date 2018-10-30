<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

function mknonce($len = 64)
{
	$SNChars = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
	$SNCCount = strlen($SNChars);
	$s = '';
	while (strlen($s) < $len)
	{
		$s .= $SNChars[random_int(0, $SNCCount-1)];
	}
	return $s;
}

function displayStub() 
{
	header ("HTTP/1.1 404 Not Found");
	die ("<h1>Crawley isn't intended to be called directly from your browser</h1>");
}

