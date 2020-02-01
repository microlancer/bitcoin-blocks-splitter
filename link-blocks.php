<?php

// Bitcoin blocks splitter script
// ==============================
//
// When your disk gets full, you may wish to move your existing blocks to another drive but
// keep writing to the same folder for any new blocks. An example situation would be if your
// original drive has 500 GB and it's full, and the second drive has 500 GB and it's empty.
// You can't simply symlink the whole blocks folder, because all the blocks won't fit in
// either drive. But, you can split it up.
//
// Once you've reached near capacity, shut down bitcoin core and run this script. The
// existing blocks files will get moved to the empty drive destination. Once the old files
// are moved, you can start bitcoin core again, and it will continue where it left off
// with the space on the original drive freed up.

// This script will move all non-symlink files in the source folder to the target folder,
// then create the symlink to the new destination accordingly.

$sourceDir = '/home/thorie/.bitcoin/blocks';
$destDir   = '/mnt3/blocks';

if (!is_dir($sourceDir) || !is_writable($sourceDir)) {
	throw new \Exception("Unable to read/write to the sourceDir: $sourceDir");
}

if (!is_dir($destDir) || !is_writable($destDir)) {
	throw new \Exception("Unable to read/write to the destDir: $destDir");
}

function getChecksum($file) {
	// Uncomment the following line if you want to skip checksums.
	//return '0';
	return hash_file('crc32b', $file);
}

echo "Searching for existing files and preparing checksums: ";

$transfers = [];
foreach(glob($sourceDir.'/*.*') as $file) {
	if (is_dir($file)) {
		echo "Skipping dir: $file\n";
		continue;
	}
	if (is_link($file)) {
		echo "Skipping symlink: $file\n";
		continue;
	}
	if (!is_file($file)) {
		echo "Skipping non-file: $file\n";
		continue;
	}
	$destFile = $destDir . "/" . basename($file);

	if (file_exists($destFile)) {
		throw new \Exception("File $destFile already exists; won't overwrite it to be on the safe side. Script failed.");
	}
	$transfers[] = ['name' => $file, 'src' => $file, 'dest' => $destFile, 'checksum' => getChecksum($file)];
	echo ".";
}
echo "\n";


echo "Copying files in $sourceDir to $destDir: ";

foreach($transfers as $transfer) {
	if (false) echo "Copying {$transfer['src']} to {$transfer['dest']}\n";

	copy($transfer['src'], $transfer['dest']);

	echo ".";
	usleep(500);
}
echo "\n";

echo "Found " . count($transfers) . " total files.\n";

if (count($transfers) == 0) {
	echo "Nothing to transfer.\n";
	exit;
}

echo "Verifying checksum of transferred files: ";

foreach($transfers as $transfer) {
	if (false) echo "Checking integrity of {$transfer['dest']}\n";

	if (getChecksum($transfer['dest']) != $transfer['checksum']) {
		throw new \Exception("Checksum failed of the copied file: $file");
	}

	echo ".";
	usleep(500);
}
echo "\n";

echo "Will begin deleting original files in 5 seconds (CTRL-C to cancel): ";

for ($i=0; $i<5; $i++) {
	sleep(1);
	echo ".";
}
echo "\n";

echo "Deleting original files: ";

foreach($transfers as $transfer) {
	if (false) echo "Removing {$transfer['src']}\n";

	unlink($transfer['src']);
	echo ".";
	usleep(500);
}
echo "\n";

echo "Creating symlink from original location to destination: ";

chdir($sourceDir);

foreach($transfers as $transfer) {
	if (false) echo "Creating symlink from {$transfer['dest']} to {$transfer['src']}\n";

	symlink($transfer['dest'], $transfer['name']);
	echo ".";
	usleep(500);
}

echo "\n";

echo "Converted " . count($transfers) . " total files to symlinks to the new destination.\n";
echo "Script complete. You can now start bitcoin core again.\n";


