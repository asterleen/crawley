<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

function post_processRequest() {
	global $route;

	$amount = (int)$_GET['amount'];
	$offset = (int)$_GET['offset'];
	$channel = (int)$_GET['channel'];

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