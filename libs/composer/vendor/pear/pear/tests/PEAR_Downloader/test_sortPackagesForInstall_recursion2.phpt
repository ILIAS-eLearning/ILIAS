--TEST--
PEAR_Downloader->sortPackagesForInstall() recursion (deep)
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
require_once 'PEAR/PackageFile/v2.php';
$pf1 = new PEAR_PackageFile_v2_rw;
$pf1->setPackage('uberparent');
$pf1->setChannel('pear.php.net');
$pf1->addPackageDepWithChannel('required', 'indirect', 'pear.php.net');

$pf2 = new PEAR_PackageFile_v1;
$pf2->setPackage('indirect');
$pf2->addPackageDep('sneaky', '1.0', 'has');

$pf3 = new PEAR_PackageFile_v1;
$pf3->setPackage('sneaky');
$pf3->addPackageDep('uberparent', '1.0', 'has');

$dl = newDownloader(array());
require_once 'PEAR/Downloader/Package.php';
$p1 = new PEAR_Downloader_Package($dl);
$p1->setPackageFile($pf1);
$p2 = new PEAR_Downloader_Package($dl);
$p2->setPackageFile($pf2);
$p3 = new PEAR_Downloader_Package($dl);
$p3->setPackageFile($pf3);
$params = array(&$p1, &$p2, &$p3);
$dl->sortPackagesForInstall($params);
$phpunit->assertEquals('uberparent', $params[0]->getPackage(), 'uberparent');
$phpunit->assertEquals('sneaky', $params[1]->getPackage(), 'sneaky');
$phpunit->assertEquals('indirect', $params[2]->getPackage(), 'indirect');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
