<?php
set_include_path(getcwd().PATH_SEPARATOR.get_include_path()); // allows chdir

$server = 'pear.Chiara';
//$server = 'test.pear.php.net';
$temp_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testinstallertemp';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'download_test_classes.php.inc';


cleanall($temp_path);
$statedir = $temp_path;
if (!is_dir($temp_path)) {
    mkdir($temp_path);
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'php')) {
    mkdir($php_dir = $temp_path . DIRECTORY_SEPARATOR . 'php');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'data')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'data');
}
if (!is_dir($cfg_dir = $temp_path . DIRECTORY_SEPARATOR . 'cfg')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'cfg');
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
if (!is_dir($tmp_dir = $temp_path . DIRECTORY_SEPARATOR . 'tmp')) {
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
    'cfg_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cfg',
    'ext_dir' => $temp_path . DIRECTORY_SEPARATOR . 'ext',
    'data_dir' => $temp_path . DIRECTORY_SEPARATOR . 'data',
    'www_dir' => $temp_path . DIRECTORY_SEPARATOR . 'www',
    'doc_dir' => $temp_path . DIRECTORY_SEPARATOR . 'doc',
    'test_dir' => $temp_path . DIRECTORY_SEPARATOR . 'test',
    'download_dir' => $temp_path . DIRECTORY_SEPARATOR . 'tmp',
    'temp_dir' => $temp_path . DIRECTORY_SEPARATOR . 'tmp',
    'bin_dir' => $temp_path . DIRECTORY_SEPARATOR . 'bin',));
touch($temp_path . DIRECTORY_SEPARATOR . 'pear.conf');
$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'pear.conf', 'w');
fwrite($fp, $config);
fclose($fp);
touch($temp_path . DIRECTORY_SEPARATOR . 'pear.ini');
$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'pear.ini', 'w');
fwrite($fp, $config);
fclose($fp);

putenv('PHP_PEAR_SYSCONF_DIR='.$temp_path);
$home = getenv('HOME');
if (!empty($home)) {
    // for PEAR_Config initialization
    putenv('HOME="'.$temp_path);
}
require_once "PEAR/Installer.php";
require_once dirname(dirname(__FILE__)) . '/download_test_classes.php.inc';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'phpt_test.php.inc';
$phpunit = new PEAR_PHPTest(true);
$fakelog = new fake_log;
$config = &test_PEAR_Config::singleton($temp_path . '/pear.ini');
$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->resetFunctions('rest');
$reg->updateChannel($chan);
test_PEAR_Dependency2::singleton($config);
function newDownloader($options)
{
    global $config;
    $downloader = new test_PEAR_Downloader($fakelog, $options, $config);
    return $downloader;
}

$installer = new test_PEAR_Installer($fakelog);
?>
