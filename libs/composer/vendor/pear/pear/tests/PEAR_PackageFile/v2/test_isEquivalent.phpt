--TEST--
PEAR_PackageFile_Parser_v2->isEquivalent()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
require_once 'PEAR/PackageFile/v1.php';
$pf1 = new test_PEAR_PackageFile_v1;
$pf2 = new PEAR_PackageFile_v2_rw;
$pf2->setConfig($config);
$pf2->setPackageType('bundle');
$phpunit->assertFalse($pf2->isEquivalent($pf1), 'bundle');
$pf2->setPackageType('php');
$ret = $pf2->isEquivalent($pf1);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'Missing Package Name'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No summary found'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'Missing description'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'Missing license'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No release version found'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No release state found'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No release date found'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No release notes found'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No maintainers found, at least one must be defined'),
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'No files in <filelist> section of package.xml'),
        ), 'error message');
$phpunit->assertFalse($ret, 'invalid pf1');
$pf1->setPackage('foo');
$pf2->setPackage('bar');
$pf1->setDate('2004-12-25');
$pf1->setSummary('fpp');
$pf1->setDescription('fpp');
$pf1->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$pf1->addFile('', 'file.php', array('role' => 'php'));
$pf1->setVersion('1.0.0');
$pf1->setState('stable');
$pf1->setNotes('foo');
$pf1->setLicense('PHP License');
$pf2->setDate('2004-12-26');
$pf2->setReleaseVersion('1.0.1');
$pf2->setAPIVersion('1.0.0');
$pf2->setReleaseStability('beta');
$pf2->setAPIStability('stable');
$pf2->addMaintainer('lead', 'cello', 'Greg Beaver', 'cellog@php.net');
$ret = $pf2->isEquivalent($pf1);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' =>
        'package.xml 1.0 package "foo" does not match "bar"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' =>
        'package.xml 1.0 summary "fpp" does not match ""'),
    array('package' => 'PEAR_PackageFile_v2', 'message' =>
        'package.xml 1.0 description "fpp" does not match ""'),
    array('package' => 'PEAR_PackageFile_v2', 'message' =>
        'package.xml 1.0 release notes "foo" do not match ""'),
    array('package' => 'PEAR_PackageFile_v2', 'message' =>
        'package.xml 1.0 has unmatched extra maintainers "cellog"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' =>
        'package.xml 2.0 has unmatched extra maintainers "cello"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' =>
        'package.xml 1.0 version "1.0.0" does not match "1.0.1"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' =>
        'package.xml 1.0 state "stable" does not match "beta"'),
    array('package' => 'PEAR_PackageFile_v2', 'message' =>
        'package.xml 1.0 file "file.php" is not present in <contents>'),
), 'invalid matches');
$phpunit->assertFalse($ret, 'invalid pf1');
$pf2->deleteMaintainer('cello');
$pf2->setPackage('foo');
$pf2->setReleaseVersion('1.0.0');
$pf2->setReleaseStability('stable');
$pf2->clearContents();
$pf2->setSummary('fpp');
$pf2->setDescription('fpp');
$pf2->setNotes('foo');
$pf2->setChannel('pear.php.net');
$pf2->addFile('', 'file.php', array('role' => 'php'));
$pf2->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$pf2->setLicense('PHP');
$pf2->clearDeps();
$pf2->setPhpDep('4.3');
$pf2->setPearinstallerDep('1.4.1');
$ret = $pf2->isEquivalent($pf1);
$phpunit->assertNoErrors('valid');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
