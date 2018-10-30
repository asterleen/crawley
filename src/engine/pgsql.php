<?php

/*
       Crawley the Telegram Beholder
    by Asterleen ~ https://asterleen.com

    https://github.com/asterleen/crawley
*/

$DB = null;

function getLastInsertId($name = '')
{
	global $DB;
	return $DB->lastInsertId($name);
}

function sqlQuery()
{
	global $DB;
	global $lang;

	if(is_null($DB)) {
		try {
			$DB = new PDO('pgsql:host=' . DB_HOST . ';dbname=' . DB_NAME,
						   DB_USER, DB_PASSWORD);

			$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $error) {
				error_log($error->getMessage());
				header ('HTTP/1.1 500 OH SHEET');
				die('Database feels bad a little');
		}
	}

	$args = func_get_args();
	if(empty($args))
		return;

	try {
		$request = $DB->prepare($args[0]);
		$request->setFetchMode(PDO::FETCH_ASSOC);

		if(sizeof($args) > 1) {
			$args = array_splice($args, 1);
			$request->execute($args);
		} else
			$request->execute();
	} catch(PDOException $error) {
			error_log($error->getMessage());
			header ('HTTP/1.1 500 OH SHEET');
			die('Database could not process the query');
	}
	
	return $request;
}?>