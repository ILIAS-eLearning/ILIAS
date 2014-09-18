<?php
#require_once '../src/LibRIS.php';
$testdir = 'tests';
set_include_path(get_include_path() . PATH_SEPARATOR . './src/');
spl_autoload_register(function ($klass) {
	$parts = explode('\\', $klass);
	if ($parts[0] == 'LibRIS') {
		print "Called for $klass" . PHP_EOL;
		include implode(DIRECTORY_SEPARATOR, $parts) . '.php';
	}
});
use \LibRIS\RISReader;

$ris = new RISReader();
$ris->parseFile($testdir . '/derik-test.ris');
$ris->printRecords();
$records = $ris->getRecords();
$rw = new \LibRIS\RISWriter();
print $rw->writeRecords($records);
// Regression against Banyuls.ris
$ris = new RISReader();
$ris->parseFile($testdir . '/Banyuls.ris');
$ris->printRecords();
$records = $ris->getRecords();
$rw = new \LibRIS\RISWriter();
print $rw->writeRecords($records);
