--TEST--
PEAR_Dependency2->checkExtensionDependency() max failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$dep = new test_PEAR_Dependency2($config, array(), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setExtensions(array('foo' => '1.0'));

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '0.9',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo" (version >= 0.1, version <= 0.9), installed version is 1.0')
), 'max');
$phpunit->assertIsa('PEAR_Error', $result, 'max');

// conflicts

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.8',
        'max' => '0.9',
        'conflicts' => true,
    ));
$phpunit->assertNoErrors('conflicts 1');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.8',
        'max' => '1.0',
        'conflicts' => true,
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine conflicts with PHP extension "foo" (version >= 0.8, version <= 1.0), installed version is 1.0')
), 'min');

// optional
$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '0.9',
    ), false);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo" (version >= 0.1, version <= 0.9), installed version is 1.0')
), 'max optional');
$phpunit->assertIsa('PEAR_Error', $result, 'max optional');

/****************************** nodeps *************************************/
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$dep->setExtensions(array('foo' => '1.0'));

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '0.9',
    ));
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PHP extension "foo" (version >= 0.1, version <= 0.9), installed version is 1.0',
), $result, 'max nodeps');

// optional
$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '0.9',
    ), false);
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PHP extension "foo" (version >= 0.1, version <= 0.9), installed version is 1.0',
), $result, 'max nodeps optional');

/****************************** force *************************************/
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$dep->setExtensions(array('foo' => '1.0'));

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '0.9',
    ));
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PHP extension "foo" (version >= 0.1, version <= 0.9), installed version is 1.0',
), $result, 'max force');

// optional
$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '0.9',
    ), false);
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PHP extension "foo" (version >= 0.1, version <= 0.9), installed version is 1.0',
), $result, 'max force optional');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
