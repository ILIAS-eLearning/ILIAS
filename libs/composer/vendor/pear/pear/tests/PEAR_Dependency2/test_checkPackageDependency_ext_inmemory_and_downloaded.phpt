--TEST--
PEAR_Dependency2->checkPackageDependency(), extension package, extension is loaded in memory
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$_test_dep->setExtensions(array('foo' => '1.2'));

require_once 'PEAR/Downloader/Package.php';
require_once 'PEAR/Downloader.php';
$down = new PEAR_Downloader($fakelog, array(), $config);
$dp = new PEAR_Downloader_Package($down);
$dp->initialize(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR .
    'extpackage.xml');
$params = array(&$dp);

$dep = &test_PEAR_Dependency2::singleton($config, array(), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 1');

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.3',
        'max' => '1.9',
        'providesextension' => 'foo',
    ), true, $params);
$phpunit->assertNoErrors('required');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'required log');
$phpunit->assertTrue($result, 'required');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
