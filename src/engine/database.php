<?php
/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

require_once ('engine/pgsql.php');

function db_getPosts() {
	return sqlQuery('SELECT * FROM post LEFT JOIN attach ON post.post_attach = attach.attach_id INNER JOIN attach_type ON attach_type.attach_type_tag = attach.attach_type_tag')->fetchAll();
}

function db_savePost($post_external_id, $post_text, $post_attach) {
	return sqlQuery('INSERT INTO post (post_external_id, post_text, post_attach) VALUES (?, ?, ?)', $post_external_id, $post_text, $post_attach);
}

function db_updatePost($post_external_id, $post_text) {
	return sqlQuery('UPDATE post SET post_text = ? WHERE post_external_id = ?', $post_text, $post_external_id);
}

function db_attachExists($attach_filename) {
	$res = sqlQuery('SELECT count(*) as cnt FROM attach WHERE attach_filename = ?', $attach_filename)->fetch();
	return ($res['cnt'] > 0) ? true : false;
}

function db_getAttaches() {
	return sqlQuery('SELECT * FROM attach')->fetchAll();
}

function db_saveAttach($attach_filename, $attach_type_tag) {
	sqlQuery('INSERT INTO attach (attach_filename, attach_type_tag) VALUES (?, ?)', $attach_filename, $attach_type_tag);
	return getLastInsertId('seq_attach_id');
}

