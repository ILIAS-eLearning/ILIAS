--TEST--
PEAR_Dependency2->checkExtensionDependency() min failure
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
        'min' => '1.1',
        'max' => '1.9',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo" (version >= 1.1, version <= 1.9), installed version is 1.0')
), 'min');
$phpunit->assertIsa('PEAR_Error', $result, 'min');

// conflicts

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '1.1',
        'max' => '1.9',
        'conflicts' => true,
    ));
$phpunit->assertNoErrors('conflicts 1');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '1.0',
        'max' => '1.9',
        'conflicts' => true,
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine conflicts with PHP extension "foo" (version >= 1.0, version <= 1.9), installed version is 1.0')
), 'min');

// optional
$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '1.1',
        'max' => '1.9',
    ), false);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo" (version >= 1.1, version <= 1.9), installed version is 1.0')
), 'min optional');
$phpunit->assertIsa('PEAR_Error', $result, 'min optional');

/****************************** nodeps *************************************/
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$dep->setExtensions(array('foo' => '1.0'));

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '1.1',
        'max' => '1.9',
    ));
$phpunit->assertNoErrors('min nodeps');
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PHP extension "foo" (version >= 1.1, version <= 1.9), installed version is 1.0',
), $result, 'min nodeps');

// optional
$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '1.1',
        'max' => '1.9',
    ), false);
$phpunit->assertNoErrors('min force');
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PHP extension "foo" (version >= 1.1, version <= 1.9), installed version is 1.0',
), $result, 'min nodeps optional');

/****************************** force *************************************/
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$dep->setExtensions(array('foo' => '1.0'));

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '1.1',
        'max' => '1.9',
    ));
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PHP extension "foo" (version >= 1.1, version <= 1.9), installed version is 1.0',
), $result, 'min force');

// optional
$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '1.1',
        'max' => '1.9',
    ), false);
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PHP extension "foo" (version >= 1.1, version <= 1.9), installed version is 1.0',
), $result, 'min force optional');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
