<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

require_once ('engine/pgsql.php');

function db_getPostCount() {
	$res = sqlQuery('SELECT count(*) as cnt FROM post')->fetch();
	return $res['cnt'];
}

function db_getPostById($post_chat_id, $post_message_id) {
	return sqlQuery('SELECT *, unix_timestamp(post_timestamp) as post_ts FROM post LEFT JOIN attach ON post.post_attach = attach.attach_id WHERE post_chat_id = ? AND post_message_id = ?', $post_chat_id, $post_chat_id)->fetchAll();
}

function db_getPosts($limit, $offset) {
	return sqlQuery('SELECT *, unix_timestamp(post_timestamp) as post_ts FROM post LEFT JOIN attach ON post.post_attach = attach.attach_id order by post_timestamp DESC LIMIT ? OFFSET ?', $limit, $offset)->fetchAll();
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

function db_purgePosts() {
	return sqlQuery('DELETE FROM post');
}

function db_getAttach($attach_filename) {
	$res = sqlQuery('SELECT * FROM attach WHERE attach_filename = ?', $attach_filename)->fetch();
	return (empty($res)) ? false : $res;
}

function db_getAttaches() {
	return sqlQuery('SELECT * FROM attach')->fetchAll();
}

function db_saveAttach($attach_filename, $attach_type_tag) {
	sqlQuery('INSERT INTO attach (attach_filename, attach_type_tag) VALUES (?, ?)', $attach_filename, $attach_type_tag);
	return getLastInsertId('seq_attach_id');
}

function db_getAttachTypes() {
	return sqlQuery('SELECT * FROM attach_type')->fetchAll();
}