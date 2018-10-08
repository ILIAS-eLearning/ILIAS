<?php
/**
 * Generate the test package.xml for PEAR's tests
 * @package PEAR_PackageFileManager
 */
/**
 * Include the package file manager
 */
require_once('PEAR/PackageFileManager.php');
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$test = new PEAR_PackageFileManager;
if (PEAR::isError($test)) {
    echo $test->getMessage();
    exit;
}
$test->setOptions(
array('baseinstalldir' => 'PEAR',
'version' => '1.4.0a10',
'packagedirectory' => dirname(__FILE__),
'state' => 'alpha',
'filelistgenerator' => 'cvs',
'roles' => array('*' => 'test'),
'notes' => 'Tests for PEAR 1.4.0a10',
'simpleoutput' => true,
'packagefile' => 'package-PEARtests.xml',
'cleardependencies' => true,
'maintainers' => array(),
'ignore' => array('generatetestPackage.xml.php.inc')));
$test->addMaintainer('cellog', 'lead', 'Greg Beaver', 'cellog@php.net');
$test->addDependency('PEAR', '1.4.0a10');
$test->addDependency('Text_Diff', false, 'has', 'pkg', true);
if (isset($_GET['make']) || (isset($_SERVER['argv']) && $_SERVER['argv'] == 'make')) {
    $e = $test->writePackageFile();
} else {
    $e = $test->debugPackageFile();
}
if (PEAR::isError($e)) {
    echo $e->getMessage();
}
if (!isset($_GET['make'])) {
    echo '<a href="' . $_SERVER['PHP_SELF'] . '?make=1">Make this file</a>';
}
?>
