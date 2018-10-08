<?php

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'pear_registry_inc.php.inc';
include_once "PEAR/Registry.php";
include_once "PEAR/ChannelFile.php";
include_once "PEAR/PackageFile/v1.php";
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'phpt_test.php.inc';
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'download_test_classes.php.inc';
set_include_path(getcwd().PATH_SEPARATOR.get_include_path()); // allows chdir

// ------------------------------------------------------------------------- //

$statedir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testinstallertemp';
cleanall();

$files1 = array(
    "pkg1-1.php" => array(
        "role" => "php",
        ),
    "pkg1-2.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg1",
        ),
    );
$files1_test = array(
    "pkg12-1.php" => array(
        "role" => "php",
        ),
    "pkg12-2.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg1",
        ),
    );
$files1_grnok = array(
    "pkg32-1.php" => array(
        "role" => "php",
        ),
    "pkg32-2.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg1",
        ),
    );
$files2 = array(
    "pkg2-1.php" => array(
        "role" => "php",
        ),
    "pkg2-2.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg2",
        ),
    );
$files3 = array(
    "pkg3-1.php" => array(
        "role" => "php",
        ),
    "pkg3-2.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg3",
        ),
    );
$files3_new = array(
    "pkg3-3.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg3",
        ),
    "pkg3-4.php" => array(
        "role" => "php",
        ),
    );
mkdir($statedir);
$phpunit = new PEAR_PHPTest(true);
$temp_path = $statedir;
if (!is_dir($temp_path)) {
    mkdir($temp_path);
}
if (!is_dir($php_dir = $temp_path . DIRECTORY_SEPARATOR . 'php')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'php');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'data')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'data');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'doc')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'doc');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'test')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'test');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'ext')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'ext');
    $ext_dir = $temp_path . DIRECTORY_SEPARATOR . 'ext';
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'script')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'script');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'tmp')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'tmp');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'bin')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'bin');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'cache')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'cache');
}
// make the fake configuration - we'll use one of these and it should work
$config = serialize(array(
    'preferred_state' => 'stable',
    'cache_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cache',
    'php_dir' => $temp_path . DIRECTORY_SEPARATOR . 'php',
    'ext_dir' => $temp_path . DIRECTORY_SEPARATOR . 'ext',
    'data_dir' => $temp_path . DIRECTORY_SEPARATOR . 'data',
    'www_dir' => $temp_path . DIRECTORY_SEPARATOR . 'www',
    'doc_dir' => $temp_path . DIRECTORY_SEPARATOR . 'doc',
    'test_dir' => $temp_path . DIRECTORY_SEPARATOR . 'test',
    'bin_dir' => $temp_path . DIRECTORY_SEPARATOR . 'bin',));
$fp = fopen($statedir . DIRECTORY_SEPARATOR . 'pear.ini', 'wb');
fwrite($fp, $config, strlen($config));
fclose($fp);
putenv('PHP_PEAR_SYSCONF_DIR='.$statedir);
$home = getenv('HOME');
if (!empty($home)) {
    // for PEAR_Config initialization
    putenv('HOME="'.$statedir);
}
$config = &PEAR_Config::singleton($statedir . DIRECTORY_SEPARATOR . 'pear.ini');
$reg = &$config->getRegistry();
$fakelog = new fake_log;
?>
