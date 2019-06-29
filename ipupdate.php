<?php
header('Content-type: text/plain');

require('../config.php');

define('IPv4', 1);
define('IPv6', 2);
define('IP_UNKNOWN', 3);

function append_hmac($msg, $password) {
	$mac = hash_hmac('sha256', $msg, $password);
	return $msg . '&h=' . $mac;
}

function validate_hmac($msg, $msg_mac, $password) {
	$new_mac = hash_hmac('sha256', $msg, $password);
	return $new_mac == $msg_mac;
}

function validate($remote_ip, $ip, $name, $timestamp, $mac) {
	global $SECRET;

	if ($remote_ip != $ip) {
		echo "Error: IP address mismatch, $remote_ip != $ip";
		return false;
	}

	$params = sprintf('type=update&ip=%s&name=%s&ts=%d', $ip, $name, $timestamp);

	if (!validate_hmac($params, $mac, $SECRET)) {
		echo 'Error: MAC mismatch';
		return false;
	}
	return true;
}

function ip_version($ip) {

	# An IPv4 always exactly has 3 dots - A.B.C.D
	if (substr_count($ip, '.') == 3)
		return IPv4;

	# An IPv6 has at least 2 colons (::), altought less than 3 shouldn't happen (A:B::C)
	elseif (strpos($ip, ':') >= 3)
		return IPv6;

	else
		return IP_UNKNOWN;
}

function updatedb($ip, $name, $timestamp) {
	global $DB_FILE;

	switch (ip_version($ip)) {
		case IPv4:
			$table = 'log_ipv4';
			break;
		case IPv6:
			$table = 'log_ipv6';
			break;
		default:
			echo 'Error: Unknown IP address format';
			break;
	}

	$db = new SQLite3($DB_FILE);
	$result = $db->exec('CREATE TABLE IF NOT EXISTS '.$table.'
	                     (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	                      ip VARCHAR NOT NULL,
	                      name VARCHAR NOT NULL,
	                      first_seen UNSIGNED INT NOT NULL,
	                      timestamp UNSIGNED INT NOT NULL);');


	$statement = $db->prepare('SELECT max(id) as id, ip, timestamp FROM '.$table.' WHERE name=:name;');
	$statement->bindValue(':name', $name);
	$results = $statement->execute();
	$row = $results->fetchArray();

	if ($ip != $row['ip']) {
		$statement = $db->prepare('INSERT INTO '.$table.'(ip,name,first_seen,timestamp) VALUES (:ip,:name,:timestamp,:timestamp);');
		$statement->bindValue(':ip', $ip);
		$statement->bindValue(':name', $name);
		$statement->bindValue(':timestamp', $timestamp);
		$statement->execute();
		echo 'OK: New IP updated';

	} elseif ($timestamp > $row['timestamp']) {
		$statement = $db->prepare('UPDATE '.$table.' SET timestamp=:timestamp WHERE id=:id');
		$statement->bindValue(':timestamp', $timestamp);
		$statement->bindValue(':id', $row['id']);
		$result = $statement->execute();
		echo 'OK: Timestamp updated';

	} else {
		echo 'Error: Old timestamp';
	}
}

$type      = $_POST['type'];
$timestamp = $_POST['ts'];
$remote_ip = $_SERVER['REMOTE_ADDR'];

if ($type == 'ip' ) {
	$params = sprintf('ip=%s&ts=%d', $remote_ip, $timestamp);

	echo append_hmac($params, $SECRET);

} elseif ($type == 'update') {
	$ip  = $_POST['ip'];
	$name = $_POST['name'];
	$mac = $_POST['h'];

	if (validate($remote_ip, $ip, $name, $timestamp, $mac))
		updatedb($ip, $name, $timestamp);
}

?>
