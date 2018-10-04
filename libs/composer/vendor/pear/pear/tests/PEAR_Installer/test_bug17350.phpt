--TEST--
PEAR_Installer - Bug #17350: "pear install --force" doesn't uninstall files from previous pkg versions
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bug17350' . DIRECTORY_SEPARATOR;
$p1  = $dir . 'Test_Package99-1.0.0' . DIRECTORY_SEPARATOR . 'package.xml';
$p2  = $dir . 'Test_Package99-1.1.0' . DIRECTORY_SEPARATOR . 'package.xml';

$_test_dep->setPHPVersion('5.3.0');
$_test_dep->setPEARVersion('1.9.0');
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

$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . 'test1.php', 'test1.php exists');
$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . 'test2.php', 'test2.php exists');

$_test_dep->setPHPVersion('5.3.0');
$_test_dep->setPEARVersion('1.9.0');
$_test_dep->setExtensions(array('pcre' => '1.0'));

$dp = new test_PEAR_Downloader($fakelog, array('force' => true), $config);
$phpunit->assertNoErrors('after create 2');

$result = $dp->download(array($p2));
$dlpackages = $dp->getDownloadedPackages();

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages 2');
$ret = $installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install 2');

$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . 'test1.php', 'test1.php exists');
$phpunit->assertFileNotExists($php_dir . DIRECTORY_SEPARATOR . 'test2.php', 'test2.php does not exists');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
