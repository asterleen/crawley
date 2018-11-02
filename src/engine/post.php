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

	if ($amount < 0 || $offset < 0)
		json_respond(3, 'Negative offset and amount are not supported');

	if ($amount > CONTENT_MAX_AMOUNT)
		json_respond (1, 'Too large records amount requested');

	if ($amount === 0)
		$amount = CONTENT_DEFAULT_AMOUNT;

	$recordsCount = db_getPostCount();

	if ($offset >= $recordsCount)
		json_respond (2, 'No more records');

	$recordsRaw = db_getPosts($amount, $offset);
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