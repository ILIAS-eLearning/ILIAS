--TEST--
Bug #18362: A whitespace TEMP_DIR path breaks install/upgrade functionality
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$path = $config->get('temp_dir') . DIRECTORY_SEPARATOR . 'spaced tmp dir';
mkdir($path);
$config->set('temp_dir', $path);

$c1 = dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packages'. DIRECTORY_SEPARATOR . 'Foobar-1.4.0a1.tgz';
$dp = new test_PEAR_Downloader($fakelog, array(), $config);
$phpunit->assertNoErrors('after create');
$result = $dp->download(array($c1));

$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');
$installer->setOptions($dp->getOptions());
$phpunit->assertEquals($fakelog->getLog(), array(), 'Problem creating spaced tmp directory');

$ret = $installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
