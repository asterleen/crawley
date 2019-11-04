<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

function post_processRequest() {
	global $route;

	$channel = (int)$_GET['channel'];
	$offset = 0;
	$recordsCount = 0;

	if ($channel === 0 && (!CONTENT_SHOW_ALL || !empty($_GET['post']))) {
		json_respond(4, 'No channel ID specified');
	}

	$recordsRaw = Array();

	if (empty($_GET['post'])) { // processing as an array of posts
		$amount = (int)$_GET['amount'];
		$offset = (int)$_GET['offset'];

		if ($amount < 0 || $offset < 0)
			json_respond(3, 'Negative offset and amount are not supported');

		if ($amount > CONTENT_MAX_AMOUNT)
			json_respond (1, 'Too large records amount requested');

		if ($amount === 0)
			$amount = CONTENT_DEFAULT_AMOUNT;

		$recordsCount = db_getPostCount($channel);

		if ($offset >= $recordsCount)
			json_respond (2, 'No records');

		$recordsRaw = db_getPosts($amount, $offset, $channel);
	} else { // processing as a single post request
		$postId = (int)$_GET['post'];

		if ($postId <= 0)
			json_respond(5, 'Bad post ID');

		$recordsRaw = db_getPostById($channel, $postId);

		if (empty($recordsRaw))
			json_respond(2, 'No records');

		$recordsCount = 1;
	}

	$records = Array();

	foreach ($recordsRaw as $post) {
		$newRecord = Array(
			'id' => $post['post_chat_id'] . '_' . $post['post_message_id'],
			'timestamp' => $post['post_ts'],
			'text' => $post['post_text']
		);

		if (!empty($post['attach_id'])) {
			$newRecord['attachment'] = Array (
				'id' => $post['attach_id'],
				'type' => $post['attach_type_tag'],
				'meta' => $post['attach_metadata'],
				'url' => CONTENT_URL_PREFIX . '/' . $post['attach_type_tag'] . '/' . $post['attach_filename']
			);
		}

		$records[] = $newRecord;
	}

	json_respond(0, Array(
		'position' => $offset,
		'total' => $recordsCount,
		'posts' => $records
	));
}

function post_processRss() {

	$channel = (int)$_GET['channel'];

	if ($channel === 0 && !CONTENT_SHOW_ALL) {
		header ('HTTP/1.1 400 Bad Request');
		json_respond(4, 'No channel ID specified');
	}

	$recordsRaw = db_getPosts(CONTENT_DEFAULT_AMOUNT, 0, $channel);
	$records = Array();

	foreach ($recordsRaw as $post) {
		$newRecord = Array(
			'title' => htmlspecialchars(truncateText($post['post_text'], RSS_MAX_TITLE_LENGTH)),
			'link' => sprintf(RSS_POST_LINK_TEMPLATE, $post['post_chat_id'] . '_' . $post['post_message_id']),
			'description' => htmlspecialchars($post['post_text'])
		);

		if (!empty($post['attach_id'])) {
			$localFilename = TELEGRAM_CONTENT_SAVE_PATH . '/' . $post['attach_type_tag'] . '/' . $post['attach_filename'];

			$newRecord['attachment'] = Array (
				'type' => mime_content_type($localFilename),
				'length' => filesize($localFilename),
				'url' => CONTENT_URL_PREFIX . '/' . $post['attach_type_tag'] . '/' . $post['attach_filename']
			);
		}

		$records[] = $newRecord;
	}

	$content = Array();
	$content['title'] = RSS_TITLE;
	$content['link'] = RSS_URL;
	$content['description'] = RSS_DESCRIPTION;
	$content['posts'] = $records;

	header ('Content-Type: application/rss+xml');
	require_once 'engine/template/rss.xml';
}