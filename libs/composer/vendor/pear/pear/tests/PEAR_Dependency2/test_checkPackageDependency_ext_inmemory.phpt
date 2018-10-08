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
$dep = &test_PEAR_Dependency2::singleton($config, array(), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.1',
        'max' => '1.9',
        'providesextension' => 'foo',
    ), true, array());
$phpunit->assertNoErrors('required');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'required log');
$phpunit->assertTrue($result, 'required');

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'providesextension' => 'foo',
    ), false, array());
$phpunit->assertNoErrors('simple');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'simple log');
$phpunit->assertTrue($result, 'simple');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
