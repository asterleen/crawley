<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

$config = Array();

function config_load() {
	global $config;

	$content = file_get_contents('engine/runtime/config.json');

	if (!empty($content)) {
		$config = json_decode($content, true);

		if (empty($config))
			$config = Array();
	}
}

function config_save() {
	global $config;

	file_put_contents('engine/runtime/config.json', json_encode($config));
}

function config_getVal($key, $defaultValue = 0) {
	global $config;

	return empty($config[$key]) ? $defaultValue : $config[$key];
}

function config_setVal($key, $value) {
	global $config;

	$config[$key] = $value;
	config_save();
}

config_load();