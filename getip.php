<?php
header('Content-type: text/plain');

$DB_FILE = 'ipupdate.sqlite';

$db = new SQLite3($DB_FILE);
$results = $db->query('SELECT max(id) as id, ip, timestamp FROM log;');
$row = $results->fetchArray();

echo $row['ip'] . "\n";

$date = new DateTime();
#$date->setTimeZone(new DateTimeZone('Europe/Lisbon'));
$date->setTimestamp($row['timestamp']);
echo $date->format('Y-m-d H:i:s') . "\n";

?>
