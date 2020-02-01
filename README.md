# bitcoin-blocks-splitter

Split the bitcoin blocks directory folder among multiple drives by using file-level symlinks.

When your disk starts to get full, use this script to move the existing files in
the `~/.bitcoin/blocks/` folder to a new drive/partition, and it will create the
necessary symlinks to point the files to the new location.

Usage:

1. Stop bitcoind
1. Edit the file link-blocks.php and update the `$sourceDir` and `$destDir`.
1. Run:
```
php link-blocks.php
```
1. Start bitcoind
