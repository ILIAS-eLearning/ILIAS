--TEST--
PEAR_Installer - Bug #13691: directories not removed on upgrade
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$p1 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bug13691' . DIRECTORY_SEPARATOR . 'admin1.xml';
$p2 = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bug13691' . DIRECTORY_SEPARATOR . 'admin2.xml';

$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setPEARVersion('1.7.1');
$_test_dep->setExtensions(array('pcre' => '1.0'));


$dp = new test_PEAR_Downloader($fakelog, array(), $config);
$phpunit->assertNoErrors('after create');

$result = $dp->download(array($p1));
$dlpackages = $dp->getDownloadedPackages();

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');
$ret = $installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$fakelog->getLog();

$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . 'admin1', 'admin1');

$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setPEARVersion('1.7.1');
$_test_dep->setExtensions(array('pcre' => '1.0'));

$dp = new test_PEAR_Downloader($fakelog, array('upgrade' => true), $config);
$phpunit->assertNoErrors('after create 2');

$result = $dp->download(array($p2));
$dlpackages = $dp->getDownloadedPackages();

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages 2');
$ret = $installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install 2');

$phpunit->assertFileNotExists($php_dir . DIRECTORY_SEPARATOR . 'admin1', 'admin1 2');
$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . 'admin2', 'admin2');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
