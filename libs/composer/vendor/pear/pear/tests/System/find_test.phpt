--TEST--
System::find() tests
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip ';
}
?>
--FILE--
<?php

require_once 'System.php';

 /*******************
         find
 ********************/
echo "Testing: find\n";
// create a temp file and then try to find it in its directory by its filename
$tmpfile = System::mktemp('pear-find-test');
$dir = dirname($tmpfile);
$filename = basename($tmpfile);

$files = System::find(
	array($dir, '-type', 'f', '-name', $filename, '-maxdepth', 1));
if(count($files) != 1) {
	echo "Test 1: ".count($files)." results found, while expected 1.\n";
	if(count($files) > 0) {
		echo "Listing results:\n";
		var_dump($files);
	}
}

// try to find the temp file by replacing the first character with a ?
$files = System::find(
	array($dir, '-type', 'f', '-name', '?'.substr($filename, 1), '-maxdepth', 1));
if(count($files) == 0) {
	echo "Test 2: 0 results found, while expected 1 or more.\n";
}

// try to find the temp file by replacing the first four characters with a *
$files = System::find(
	array($dir, '-type', 'f', '-name', '*'.substr($filename, 4), '-maxdepth', 1));
if(count($files) == 0) {
	echo "Test 3: 0 results found, while expected 1 or more.\n";
}

// try to find the temp file from within the parent of the containing directory

$parent = substr($dir, 0, strrpos($dir, DIRECTORY_SEPARATOR));
if (!$parent) {
    $parent = '/';
}

$files = System::find(
	array($parent, '-type', 'f', '-name', $filename, '-maxdepth', 2));
if(count($files) == 0) {
	echo "Test 5: 0 results found, while expected 1 or more.\n";
}

// Clean up
unlink($tmpfile);
?>
===DONE===
--EXPECT--
Testing: find
===DONE===