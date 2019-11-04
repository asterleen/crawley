<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

require_once ('engine/pgsql.php');

function db_getPostCount($post_chat_id = 0) {
	$res = [];

	if ((int)$post_chat_id === 0) {
		$res = sqlQuery('SELECT count(*) as cnt FROM post')->fetch();
	} else {
		$res = sqlQuery('SELECT count(*) as cnt FROM post WHERE post_chat_id = ?', $post_chat_id)->fetch();
	}
	
	return $res['cnt'];
}

function db_getPostById($post_chat_id, $post_message_id) {
	return sqlQuery('SELECT *, unix_timestamp(post_timestamp) as post_ts FROM post LEFT JOIN attach ON post.post_attach = attach.attach_id WHERE post_chat_id = ? AND post_message_id = ?', $post_chat_id, $post_message_id)->fetchAll();
}

function db_getPosts($limit, $offset, $post_chat_id = 0) {

	if ((int)$post_chat_id === 0) {
		return sqlQuery('SELECT *, unix_timestamp(post_timestamp) as post_ts FROM post LEFT JOIN attach ON post.post_attach = attach.attach_id order by post_timestamp DESC LIMIT ? OFFSET ?', $limit, $offset)->fetchAll();
	} else {
		return sqlQuery('SELECT *, unix_timestamp(post_timestamp) as post_ts FROM post LEFT JOIN attach ON post.post_attach = attach.attach_id WHERE post_chat_id = ? order by post_timestamp DESC LIMIT ? OFFSET ?', $post_chat_id, $limit, $offset)->fetchAll();
	}

	
}

function db_savePost($post_chat_id, $post_message_id, $post_text, $post_attach) {
	return sqlQuery('INSERT INTO post (post_chat_id, post_message_id, post_text, post_attach) VALUES (?, ?, ?, ?)', $post_chat_id, $post_message_id, $post_text, $post_attach);
}

function db_updatePost($post_chat_id, $post_message_id, $post_text) {
	return sqlQuery('UPDATE post SET post_text = ? WHERE post_chat_id = ? AND post_message_id = ?', $post_text, $post_chat_id, $post_message_id);
}

function db_deletePost($post_chat_id, $post_message_id) {
	return sqlQuery('DELETE FROM post WHERE post_chat_id = ? AND post_message_id = ?', $post_chat_id, $post_message_id);
}

function db_purgePosts($post_chat_id) {
	if ((int)$post_chat_id !== 0) {
		return sqlQuery('DELETE FROM post WHERE post_chat_id = ?');
	} else {
		return false;
	}
}

function db_getAttach($attach_filename) {
	$res = sqlQuery('SELECT * FROM attach WHERE attach_filename = ?', $attach_filename)->fetch();
	return (empty($res)) ? false : $res;
}

function db_getAttaches() {
	return sqlQuery('SELECT * FROM attach')->fetchAll();
}

function db_saveAttach($attach_filename, $attach_type_tag, $attach_metadata = null) {
	sqlQuery('INSERT INTO attach (attach_filename, attach_type_tag, attach_metadata) VALUES (?, ?, ?)', $attach_filename, $attach_type_tag, $attach_metadata);
	return getLastInsertId('seq_attach_id');
}

function db_getAttachTypes() {
	return sqlQuery('SELECT * FROM attach_type')->fetchAll();
}