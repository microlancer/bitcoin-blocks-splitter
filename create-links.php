<?php

$start = 0;
$end = 1500;

chdir("/home/thorie/.bitcoin/blocks");

for ($i=$start; $i<=$end; $i++) {
	$types = ['blk', 'rev'];
	foreach ($types as $type) {
		$file = sprintf("$type%05d", $i);
		echo "$file\n";
		symlink("/data4/blocks/$file.dat", "$file.dat");
	}
}
