<?php
require_once 'PEAR/PackageFile/Parser/v2.php';
require_once 'PEAR/PackageFile/v2.php';
require_once 'PEAR/Config.php';
set_include_path(getcwd().PATH_SEPARATOR.get_include_path()); // allows chdir

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'download_test_classes.php.inc';


$statedir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testinstallertemp';
cleanall($statedir);
$temp_path = $statedir;
if (!is_dir($temp_path)) {
    mkdir($temp_path);
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'php')) {
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
$parser = new PEAR_PackageFile_Parser_v2;
$parser->setConfig($config);
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'phpt_test.php.inc';
$phpunit = new PEAR_PHPTest(true);
$fakelog = new fake_log;
$parser->setLogger($fakelog);
test_PEAR_Dependency2::singleton($config);
/**
 * @param array
 * @return test_PEAR_Downloader_Package
 */
function newFakeDownloaderPackage($options)
{
    global $config, $fakelog;
    $downloader = new test_PEAR_Downloader($fakelog, $options, $config);
    $p = new test_PEAR_Downloader_Package($downloader);
    return $p;
}
?>
