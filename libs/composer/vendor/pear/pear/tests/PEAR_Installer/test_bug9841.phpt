--TEST--
PEAR_Installer::validateUninstall() - Bug #9841/10081 uninstall error test for complex dependencies
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$p1 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'SG.xml';
$p2 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'RR.xml';
$p3 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'QQ.xml';
$p4 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'BB.xml';
$p5 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'AA.xml';

for ($i = 1; $i < 6; $i++) {
    $packages[] = ${"p$i"};
}

$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setPEARVersion('1.5.0');
$_test_dep->setExtensions(array('pcre' => '1.0'));


$dp = new test_PEAR_Downloader($fakelog, array(), $config);
$phpunit->assertNoErrors('after create');

$result = $dp->download($packages);
$phpunit->assertEquals(5, count($result), 'return');
$dlpackages = $dp->getDownloadedPackages();
$phpunit->assertEquals(5, count($dlpackages), 'downloaded packages count');

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');
$ret = $installer->install($result[0], $dp->getOptions());
$ret = $installer->install($result[1], $dp->getOptions());
$ret = $installer->install($result[2], $dp->getOptions());
$ret = $installer->install($result[3], $dp->getOptions());
$ret = $installer->install($result[4], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$fakelog->getLog();

$reg = $config->getRegistry();
$a = &$reg->getPackage('BB');
$paramnames = array(&$a);
$installer->setUninstallPackages($paramnames);
$installer->uninstall('BB');

$phpunit->assertErrors(
    array(
        array('package' => 'PEAR_Error', 'message' => 'pear/BB cannot be uninstalled, other installed packages depend on this package'),
    ),
'after BB uninstall');
$phpunit->assertEquals(array(
  0 =>
  array (
    0 => 0,
    1 => '"pear/BB" can be optionally used by installed package pear/RR',
  ),
  1 =>
  array (
    0 => 0,
    1 => '"pear/BB" is required by installed package pear/QQ',
  ),
), $fakelog->getLog(), 'BB log');

unset($installer->___uninstall_package_cache);
$a = &$reg->getPackage('SG');
$paramnames = array(&$a);
$installer->setUninstallPackages($paramnames);
$installer->uninstall('SG');

$phpunit->assertErrors(
    array(
        array('package' => 'PEAR_Error', 'message' => 'pear/SG cannot be uninstalled, other installed packages depend on this package'),
    ),
'after SG uninstall');
$phpunit->assertEquals(array(
  0 =>
  array (
    0 => 0,
    1 => '"pear/SG" is required by installed package pear/AA',
  ),
), $fakelog->getLog(), 'SG log');

unset($installer->___uninstall_package_cache);
$a = &$reg->getPackage('RR');
$paramnames = array(&$a);
$installer->setUninstallPackages($paramnames);
$installer->uninstall('RR');

$phpunit->assertErrors(
    array(
        array('package' => 'PEAR_Error', 'message' => 'pear/RR cannot be uninstalled, other installed packages depend on this package'),
    ),
'after RR uninstall');
$phpunit->assertEquals(array(
  0 =>
  array (
    0 => 0,
    1 => '"pear/RR" is required by installed package pear/SG',
  ),
), $fakelog->getLog(), 'RR log');


unset($installer->___uninstall_package_cache);
$a = &$reg->getPackage('AA');
$paramnames = array(&$a);
$installer->setUninstallPackages($paramnames);
$installer->uninstall('AA');

$phpunit->assertErrors(
    array(
        array('package' => 'PEAR_Error', 'message' => 'pear/AA cannot be uninstalled, other installed packages depend on this package'),
    ),
'after AA uninstall');
$phpunit->assertEquals(array(
  0 =>
  array (
    0 => 0,
    1 => '"pear/AA" is required by installed package pear/RR',
  ),
), $fakelog->getLog(), 'AA log');

unset($installer->___uninstall_package_cache);
$a = &$reg->getPackage('QQ');
$b = &$reg->getPackage('SG');
$paramnames = array(&$a, &$b);
$installer->setUninstallPackages($paramnames);
$installer->uninstall('QQ');

$phpunit->assertErrors(
    array(
        array('package' => 'PEAR_Error', 'message' => 'pear/QQ cannot be uninstalled, other installed packages depend on this package'),
    ),
'after QQ uninstall (QQ SG)');
$phpunit->assertEquals(array(
  0 =>
  array (
    0 => 0,
    1 => '"pear/QQ" is required by installed package pear/SG',
  ),
), $fakelog->getLog(), 'QQ (QQ SG) log');

$installer->uninstall('SG');

$phpunit->assertErrors(
    array(
        array('package' => 'PEAR_Error', 'message' => 'pear/SG cannot be uninstalled, other installed packages depend on this package'),
    ),
'after SG (QQ SG) uninstall');
$phpunit->assertEquals(array(
  0 =>
  array (
    0 => 0,
    1 => '"pear/SG" is required by installed package pear/AA',
  ),
), $fakelog->getLog(), 'SG (QQ SG) log');


unset($installer->___uninstall_package_cache);
$a = &$reg->getPackage('SG');
$b = &$reg->getPackage('QQ');
$c = &$reg->getPackage('BB');
$d = &$reg->getPackage('RR');
$e = &$reg->getPackage('AA');
$paramnames = array(&$a, &$b, &$c, &$d, &$e);
$installer->setUninstallPackages($paramnames);
$installer->uninstall('SG');
$phpunit->assertNoErrors('last SG');
$installer->uninstall('QQ');
$phpunit->assertNoErrors('last QQ');
$installer->uninstall('BB');
$phpunit->assertNoErrors('last BB');
$installer->uninstall('RR');
$phpunit->assertNoErrors('last RR');
$installer->uninstall('AA');
$phpunit->assertNoErrors('last AA');


echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
