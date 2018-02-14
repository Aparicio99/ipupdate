<?php
header('Content-type: text/plain');

require('../config.php');

function print_table_last_ip($table) {
	global $DB_FILE;
	global $TIMEZONE;

	$db = new SQLite3($DB_FILE);
	$results = $db->query('SELECT max(id) as id, ip, timestamp FROM '.$table.';');
	$row = $results->fetchArray();

	$date = new DateTime();
	$date->setTimeZone(new DateTimeZone($TIMEZONE));
	$date->setTimestamp($row['timestamp']);
	$date_string = $date->format('Y-m-d H:i:s');

	echo $date_string . ' - ' . $row['ip'] . "\n";

}

print_table_last_ip('log_ipv4');
print_table_last_ip('log_ipv6');

?>
