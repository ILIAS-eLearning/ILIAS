--TEST--
PEAR_Downloader->sortPackagesForInstall()
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
$pf1->setChannel('pear.poop.net');
$pf1->addPackageDepWithChannel('required', 'indirect', 'pear.php.net');

$pf2 = new PEAR_PackageFile_v1;
$pf2->setPackage('indirect');
$pf2->addPackageDep('child1', '1.0', 'has');

$pf3 = new PEAR_PackageFile_v2_rw;
$pf3->setPackage('child1');
$pf3->setChannel('pear.php.net');
$pf3->addPackageDepWithChannel('required', 'nodeps', 'pear.php.net');
$pf3->addPackageDepWithChannel('required', 'child2', 'pear.php.net');

$pf4 = new PEAR_PackageFile_v1;
$pf4->setPackage('child2');
$pf4->addPackageDep('nodeps', '', 'has');

$pf5 = new PEAR_PackageFile_v1;
$pf5->setPackage('nodeps');

$dl = newDownloader(array());
require_once 'PEAR/Downloader/Package.php';
$p1 = new PEAR_Downloader_Package($dl);
$p1->setPackageFile($pf1);
$p2 = new PEAR_Downloader_Package($dl);
$p2->setPackageFile($pf2);
$p3 = new PEAR_Downloader_Package($dl);
$p3->setPackageFile($pf3);
$p4 = new PEAR_Downloader_Package($dl);
$p4->setPackageFile($pf4);
$p5 = new PEAR_Downloader_Package($dl);
$p5->setPackageFile($pf5);
$params = array(&$p1, &$p2, &$p3, &$p4, &$p5);
$dl->sortPackagesForInstall($params);
$phpunit->assertEquals('nodeps', $params[0]->getPackage(), 'nodeps');
$phpunit->assertEquals('child2', $params[1]->getPackage(), 'child2');
$phpunit->assertEquals('child1', $params[2]->getPackage(), 'child1');
$phpunit->assertEquals('indirect', $params[3]->getPackage(), 'indirect');
$phpunit->assertEquals('uberparent', $params[4]->getPackage(), 'uberparent');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
