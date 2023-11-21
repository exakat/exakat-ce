<?php

$sqlite3 = new Sqlite3('data/analyzers.sqlite');

$res = $sqlite3->query('select * from analyzers');

$missing = 0;
$total = 0;
while($row = $res->fetchArray()) {
	++$total;
	if (!file_exists('library/Exakat/Analyzer/'.$row['folder'].'/'.$row['name'].'.php')) {
		++$missing;
		print_r($row);
	}
}

print $missing." missing\n";
print $total." total\n";

?>