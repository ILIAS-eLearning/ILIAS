<?php
require_once 'PEAR/Common.php';
set_include_path(getcwd().PATH_SEPARATOR.get_include_path()); // allows chdir
$statedir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testinstallertemp';

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'download_test_classes.php.inc';


cleanall($statedir);
if (!is_dir($statedir)) {
    mkdir($statedir);
}
if (!is_dir($statedir . DIRECTORY_SEPARATOR . 'php')) {
    mkdir($statedir . DIRECTORY_SEPARATOR . 'php');
}
if (!is_dir($statedir . DIRECTORY_SEPARATOR . 'data')) {
    mkdir($statedir . DIRECTORY_SEPARATOR . 'data');
}
if (!is_dir($statedir . DIRECTORY_SEPARATOR . 'doc')) {
    mkdir($statedir . DIRECTORY_SEPARATOR . 'doc');
}
if (!is_dir($statedir . DIRECTORY_SEPARATOR . 'test')) {
    mkdir($statedir . DIRECTORY_SEPARATOR . 'test');
}
if (!is_dir($statedir . DIRECTORY_SEPARATOR . 'ext')) {
    mkdir($statedir . DIRECTORY_SEPARATOR . 'ext');
}
if (!is_dir($statedir . DIRECTORY_SEPARATOR . 'script')) {
    mkdir($statedir . DIRECTORY_SEPARATOR . 'script');
}
if (!is_dir($statedir . DIRECTORY_SEPARATOR . 'tmp')) {
    mkdir($statedir . DIRECTORY_SEPARATOR . 'tmp');
}
if (!is_dir($statedir . DIRECTORY_SEPARATOR . 'bin')) {
    mkdir($statedir . DIRECTORY_SEPARATOR . 'bin');
}
if (!is_dir($statedir . DIRECTORY_SEPARATOR . 'cache')) {
    mkdir($statedir . DIRECTORY_SEPARATOR . 'cache');
}
// make the fake configuration - we'll use one of these and it should work
$config = serialize(array('master_server' => 'pear.php.net',
    'preferred_state' => 'stable',
    'cache_dir' => $statedir . DIRECTORY_SEPARATOR . 'cache',
    'php_dir' => $statedir . DIRECTORY_SEPARATOR . 'php',
    'ext_dir' => $statedir . DIRECTORY_SEPARATOR . 'ext',
    'data_dir' => $statedir . DIRECTORY_SEPARATOR . 'data',
    'www_dir' => $statedir . DIRECTORY_SEPARATOR . 'www',
    'doc_dir' => $statedir . DIRECTORY_SEPARATOR . 'doc',
    'test_dir' => $statedir . DIRECTORY_SEPARATOR . 'test',
    'bin_dir' => $statedir . DIRECTORY_SEPARATOR . 'bin',));
touch($statedir . DIRECTORY_SEPARATOR . 'pear.conf');
$fp = fopen($statedir . DIRECTORY_SEPARATOR . 'pear.conf', 'w');
fwrite($fp, $config);
fclose($fp);
touch($statedir . DIRECTORY_SEPARATOR . 'pear.ini');
$fp = fopen($statedir . DIRECTORY_SEPARATOR . 'pear.ini', 'w');
fwrite($fp, $config);
fclose($fp);

putenv('PHP_PEAR_SYSCONF_DIR='.$statedir);
$home = getenv('HOME');
if (!empty($home)) {
    // for PEAR_Config initialization
    putenv('HOME="'.$statedir);
}
$config = &PEAR_Config::singleton($statedir . DIRECTORY_SEPARATOR . 'pear.ini');
require_once dirname(dirname(__FILE__)) . '/download_test_classes.php.inc';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'phpt_test.php.inc';
$phpunit = new PEAR_PHPTest(true);
$fakelog = new fake_log;

$common = new PEAR_Common;
?>
