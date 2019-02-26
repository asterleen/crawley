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

function config_getChats() {
	return config_getVal('chats', Array());
}

function config_saveChats($chats) {
	config_setVal('chats', $chats);
}

function config_getChatById($chatId) {
	$chats = config_getChats();
	return $chats[$chatId];
}

function config_removeChatById($chatId) {
	$chats = config_getChats();
	unset ($chats[$chatId]);
	config_saveChats($chats);
}

function config_addChat($chatId, $chat) {
	$chats = config_getChats();
	$chats[$chatId] = $chat;
	config_saveChats($chats);
}

config_load();