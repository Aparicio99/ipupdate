<?php
header('Content-type: text/plain');

$DB_FILE   = 'ipupdate.sqlite';
$PASSWORD  = 'some random password';
$HASH_ALGO = 'sha256';

function validate($remote_ip, $ip, $timestamp, $mac) {
	global $HASH_ALGO, $PASSWORD;

	if ($remote_ip != $ip) {
		echo 'Error: IP address mismatch';
		return false;
	}

	$params = sprintf('type=update&ip=%s&ts=%d', $ip, $timestamp);
	$mac_msg = hash_hmac($HASH_ALGO, $params, $PASSWORD);

	if ($mac != $mac_msg) {
		echo 'Error: MAC mismatch';
		return false;
	}
	return true;
}

function updatedb($ip, $timestamp) {
	global $DB_FILE;

	$db = new SQLite3($DB_FILE);
	$result = $db->exec('CREATE TABLE IF NOT EXISTS log
		(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		ip VARCHAR NOT NULL,
		timestamp UNSIGNED INT NOT NULL);');

	$results = $db->query('SELECT max(id) as id, ip, timestamp FROM log;');
	$row = $results->fetchArray();

	if ($ip != $row['ip']) {
		$statement = $db->prepare('INSERT INTO log(ip,timestamp) VALUES (:ip,:timestamp);');
		$statement->bindValue(':ip', $ip);
		$statement->bindValue(':timestamp', $timestamp);
		$statement->execute();
		echo 'OK: New IP updated';

	} elseif ($timestamp > $row['timestamp']) {
		$statement = $db->prepare('UPDATE log SET timestamp=:timestamp WHERE id=:id');
		$statement->bindValue(':timestamp', $timestamp);
		$statement->bindValue(':id', $row['id']);
		$result = $statement->execute();
		echo 'OK: Timestamp updated';

	} else {
		echo 'Error: Old timestamp';
	}
}

$type      = $_POST['type'];
$remote_ip = $_SERVER['REMOTE_ADDR'];

if ($type == 'ip' ) {
	echo $remote_ip;

} elseif ($type == 'update') {
	$ip        = $_POST['ip'];
	$timestamp = $_POST['ts'];
	$mac       = $_POST['h'];

	if (validate($remote_ip, $ip, $timestamp, $mac))
		updatedb($ip, $timestamp);
}

?>
