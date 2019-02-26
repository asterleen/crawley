<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

function curl_request ($method, $type, $data = Array()) {
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

function telegram_getChatInfo ($chat_id) {
	$chatinfo_raw = curl_request('getChat', 'get', Array('chat_id' => $chat_id));

	if (!$chatinfo_raw)
		return false;

	$chatinfo = json_decode($chatinfo_raw, true);
	if (!$chatinfo) {
		return false;
	}

	return Array(
		'id' => $chatinfo['id'],
		'title' => $chatinfo['title'],
		'username' => $chatinfo['username'],
		'type' => $chatinfo['type']
	);

}

function telegram_getFile ($file_id, $attachType) {

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

// NB: if $dontExit is set to false, Crawley will exit the script after sending the message
function telegram_sendMessage ($text, $chat, $additionalParams = null, $dontExit = false)
{
	$message = Array (
		'text' => $text,	
		'parse_mode' => 'markdown',
		'chat_id' => $chat);

	if (!empty($additionalParams))
			$message = array_merge($message, $additionalParams);

	if (TELEGRAM_USE_DIRECT_RESPONSE && !$dontExit) // see enconfig.php
	{
		$message = array_merge($message, Array('method' => 'sendMessage'));

		header('Content-Type: application/json');
		die(json_encode($message));
	}
	else {
		$answer = curl_request('sendMessage', 'post', $message);

		if (!$dontExit) {
			die('OK');
		}
	}
}



function telegram_processCommand($commandline, $chat, $user, $messageId)
{
	$commandline = mb_substr($commandline, 1, NULL, 'UTF-8');

	if (strpos($commandline, '@') > -1)
		$commandline = mb_substr($commandline, 0, mb_strpos($commandline, '@', 0, 'UTF-8'), 'UTF-8');

	if (strlen($commandline) == 0) {
		telegram_sendMessage('Empty command? Really? Why?', $chat);
		return; // not a command
	}

	$commands = explode(' ', $commandline);

	if ($chat > 0 && $commands[0] == 'whoami') {
		telegram_sendMessage(sprintf('Your id is `%d`.', $user), $chat);
		return;
	}

	if ($chat > 0 && $user !== TELEGRAM_UBER_ADMIN_UID) {
		error_log('Command attempt from a non-admin user ['.$user.'], declined.');
		return;
	}

	if ($chat > 0 && $commands[0] == 'start') {

		$startMessage = <<<CRAWLEY
Hi! This is Crawley, the Telegram Beholder.

To use me, follow these steps:
1. Send me a /getkey command and copy the key I'll give back.
2. Add me to your channel and give me admin rights
3. In that channel, write the `/addchat <your_key>` command
4. I'll remember the chat and remove your message. You're done!

Read more at https://github.com/asterleen/crawley
CRAWLEY;

		telegram_sendMessage($startMessage, $chat);

		return;
	}

	// THh following commands are allowed in channels
	switch ($commands[0]) {
		case 'setchat':
			if ($commands[1] === config_getVal('setchannel_tmp_key')) {
				if ($chat > 0)
					telegram_sendMessage('Not a channel/group chat, rejected.', $chat);
				else {
					config_setVal('channel_id', $chat);
					curl_request('deleteMessage', 'get', Array('chat_id' => $chat, 'message_id' => $messageId));
					config_setVal('setchannel_tmp_key', 0);
				}
			} else {
				telegram_sendMessage('Bad temporary key. Send me a `/getkey` command in private chat.', $chat);
			}
			break;

		case 'addchat':
			$chatInfo = telegram_getChatInfo($chat);
			if (!$chatInfo) {
				error_log('Error while getting chat info!');
				telegram_sendMessage('Could not get chat info!', $chat);
			} else {

			}
			break;
	}

	// The next commands are to be executed only in private messages and only by admin.
	if ($chat <= 0)
		return;

	switch ($commands[0]) {
		case 'getkey': 
			$setchatNonce = mknonce(8);
			config_setVal ('setchannel_tmp_key', $setchatNonce);

			telegram_sendMessage("Your chat setting temporary key is `".$setchatNonce."`.\n" .
							"Send the command `/setchat ".$setchatNonce."` to the channel you want to connect with Crawley and I will follow it and save its content.", $chat);
			break;

		case 'purge':
			if (empty($commands[1]) || empty(config_getVal('purge_tmp_key'))) {
				$purgeNonce = mknonce(8);

				config_setVal ('purge_tmp_key', $purgeNonce);

				telegram_sendMessage("Are you sure to remove all the posts from Crawley's database?\n" .
							"**WARNING: you will not be able to restore these records!**\n" .
							"Only posts will be purged, attaches won't be due to caching reasons.\n" .
							"To proceed send me this: `/purge ".$purgeNonce.'`', $chat);
			} else {
				if ($commands[1] === config_getVal('purge_tmp_key')) {
					db_purgePosts();
					config_setVal('purge_tmp_key', 0);
					telegram_sendMessage('Posts table is completely clean.', $chat);
				}
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
	$chat = (int)$message['chat']['id'];
	$post = (int)$message['message_id'];

	$containsAttach = (array_key_exists('photo', $message) ||
					   array_key_exists('voice', $message) ||
					   array_key_exists('audio', $message));

	$text = ($containsAttach ? $message['caption'] : $message['text']);

	if ($text[0] === '/') {
		telegram_processCommand($text, $chat, $user, $post);
	} elseif ($isFromChannel && $chat === config_getVal('channel_id')) { // won't save direct messages
		if ($isEdit) {
			if ($text === '-') { // artifical removal, Telegram does not send delete event
				db_deletePost($chat, $post);
				curl_request('deleteMessage', 'get', Array('chat_id' => $chat, 'message_id' => $post));
			} else
				db_updatePost($chat, $post, $text);
		} else {

			$attachId = null;
			if ($containsAttach) {
				$attachId = telegram_processAttach($message);

				if ($attachId === null) {
					// Remember: this function die()'s implicitly
					telegram_sendMessage('[Crawley] Could not get the attachment! Fix it as soon as possible and re-send your post then remove this message.', $chat);
				}
			}

			db_savePost($chat, $post, $text, $attachId);
		}

		die ('OK');
	}
}

function telegram_processInput() {
	global $route;

	if ($route[1] !== TELEGRAM_CALLBACK_KEY)
		die('Bad Telegram API Key!');

	$event = json_decode(file_get_contents('php://input'), true);

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

