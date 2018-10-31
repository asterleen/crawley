<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

function curl_request ($method, $type, $data = Array())
{
	$curl = curl_init();

	if($curl)
	{
		curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
		curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot'.TELEGRAM_BOT_TOKEN.'/'.$method.($type == 'get' ? '?'.http_build_query($data) : ''));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);
		curl_setopt($curl, CURLOPT_USERAGENT, 'CrawleyBot; +https://github.com/asterleen/crawley');
		
		if ($type == 'post')
		{
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		
		$out = curl_exec($curl);
		curl_close($curl);
		return (empty ($out)) ? false : $out;
	} else
		return false;
}

function telegram_getFile ($file_id, $attachType)
{

	$filedata_raw = curl_request('getFile', 'get', Array('file_id' => $file_id));
	$filedata = json_decode($filedata_raw, true);
	if (!$filedata)
		return false;

	if (!$filedata['ok'])
		return false;

	$filepath = $filedata['result']['file_path'];
	$filename = md5($file_id).'.'.pathinfo($filepath, PATHINFO_EXTENSION);
	$filelocation = TELEGRAM_CONTENT_SAVE_PATH.'/'.$attachType.'/'.$filename;

	if (!file_exists($filelocation)) {
		$fcontent = file_get_contents('https://api.telegram.org/file/bot'.TELEGRAM_BOT_TOKEN.'/'.$filepath);

		if (!$fcontent)
			return false;

		if (file_put_contents($filelocation, $fcontent) === false)
			return false;
	}

	return $filename;
}

function telegram_sendMessage ($text, $chat, $additionalParams = null, $forceHTTP = false)
{
	$message = Array (
		'text' => $text,	
		'parse_mode' => 'markdown',
		'chat_id' => $chat);

	if (!empty($additionalParams))
			$message = array_merge($message, $additionalParams);

	if (TELEGRAM_USE_DIRECT_RESPONSE && !$forceHTTP) // see enconfig.php
	{
		$message = array_merge($message, Array('method' => 'sendMessage'));

		header('Content-Type: application/json');
		die(json_encode($message));
	}
	else
		$answer = curl_request('sendMessage', 'post', $message);
}



function telegram_processCommand($commandline, $chat, $user, $messageId)
{
	if ($user !== TELEGRAM_UBER_ADMIN_UID) {
		error_log('Command attempt from a non-admin user ['.$user.'], declined.');
		return;
	}

	$commandline = mb_substr($commandline, 1, NULL, 'UTF-8');

	if (strpos($commandline, '@') > -1)
		$commandline = mb_substr($commandline, 0, mb_strpos($commandline, '@', 0, 'UTF-8'), 'UTF-8');

	if (strlen($commandline) == 0) {
		telegram_sendMessage('Empty command? Really? Why?', $chat);
		return; // not a command
	}

	$commands = explode(' ', $commandline);

	switch ($commands[0]) {
		case 'thischannel':
			if ($chat > 0)
				telegram_sendMessage('Not a channel/group chat, rejected.', $chat);
			else {
				config_setVal('channel_id', $chat);
				curl_request('deleteMessage', 'get', Array('chat_id' => $chat, 'message_id' => $messageId));
			}
			break;

		default:
			telegram_sendMessage('Bad command.', $chat);
	}

}

// This will return attachment ID from the database after processing
// or NULL if processing/downloading failed.
// We use NULL and not FALSE because it makes it easier to insert
// this value into the database.
function telegram_processAttach($message) {
	$attachType = '';
	$downloadableObject = Array();

	if (!empty($message['photo'])) {
		$attachType = 'photo';

		$largestPhotoSize = 0;

		foreach ($message['photo'] as $photo) { // find the largest photo
			if ($photo['file_size'] > $largestPhotoSize) {
				$largestPhotoSize = $photo['file_size'];
				$downloadableObject = $photo;
			}
		}

		if ($largestPhotoSize === 0) {
			error_log ('No photo found, bad object or something else went wrong');
			return null; 
		}

	} else if (!empty($message['voice'])) {
		$attachType = 'voice';
		$downloadableObject = $message['voice'];

	} else if (!empty($message['audio'])) {
		$attachType = 'audio';
		$downloadableObject = $message['audio'];
	} else {
		error_log ('Unknown attachment came here, will not download');
		return null;
	}

	$filename = telegram_getFile($downloadableObject['file_id'], $attachType);
	if (empty($filename)) {
		error_log ('Could not download contents of the attachment');
		return null;
	}

	$attachObject = db_getAttach($filename);
	$attachId = 0;

	if ($attachObject === false) {
		$attachId = db_saveAttach($filename, $attachType);
	} else {
		error_log('Attach #'.$attachObject['attach_id'].' ['.$attachObject['attach_filename'].'] is being reused');
		$attachId = $attachObject['attach_id'];
	}
	
	return $attachId;
}

function telegram_processMessage($message, $isEdit, $isFromChannel) {
	$user = (int)$message['from']['id'];
	$chat = $message['chat']['id'];
	$externalId = $chat.'_'.$message['message_id'];

	$containsAttach = (array_key_exists('photo', $message) ||
					   array_key_exists('voice', $message) ||
					   array_key_exists('audio', $message));

	$text = ($containsAttach ? $message['caption'] : $message['text']);

	if ($text[0] === '/') {
		telegram_processCommand($text, $chat, $user, $message['message_id']);
	} elseif ($isFromChannel && $chat === config_getVal('channel_id')) { // won't save direct messages
		if ($isEdit) {
			if ($text === '-') { // artifical removal, Telegram does not send delete event
				db_deletePost($externalId);
				curl_request('deleteMessage', 'get', Array('chat_id' => $chat, 'message_id' => $message['message_id']));
			} else
				db_updatePost($externalId, $text);
		} else {
			$attachId = ($containsAttach) ? telegram_processAttach($message) : null;
			db_savePost($externalId, $text, $attachId);
		}

		die ('OK');
	}
}

function telegram_processInput() {
	global $route;

	if ($route[1] !== TELEGRAM_CALLBACK_KEY)
		die('Bad Telegram API Key!');

	$event = json_decode(file_get_contents('php://input'), true);

	error_log(print_r($event, true));

	if (empty($event))
		die('Bad event data.');

	if (!empty($event['message'])) {
		telegram_processMessage($event['message'], false, false);

	} elseif (!empty($event['edited_message'])) {
		telegram_processMessage($event['edited_message'], true, false);

	} elseif (!empty($event['channel_post'])) {
		telegram_processMessage($event['channel_post'], false, true);

	} elseif (!empty($event['edited_channel_post'])) {
		telegram_processMessage($event['edited_channel_post'], true, true);
	}
}

