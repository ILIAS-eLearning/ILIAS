--TEST--
PEAR_Dependency2->checkPackageDependency() url-style dependency (failure)
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

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'uri' => 'http://www.example.com/foo-1.0.tgz',
    ), true, array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires package "http://www.example.com/foo-1.0.tgz"')
), 'min');
$phpunit->assertIsa('PEAR_Error', $result, 'required');

// optional
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'uri' => 'http://www.example.com/foo-1.0.tgz',
    ), false, array());
$phpunit->assertEquals(array('pear/mine can optionally use package "http://www.example.com/foo-1.0.tgz"'), $result, 'optional');

/****************************** nodeps *************************************/
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'uri' => 'http://www.example.com/foo-1.0.tgz',
    ), true, array());
$phpunit->assertNoErrors('min nodeps');
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires package "http://www.example.com/foo-1.0.tgz"',
), $result, 'nodeps required');

// optional
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'uri' => 'http://www.example.com/foo-1.0.tgz',
    ), false, array());
$phpunit->assertEquals(array('pear/mine can optionally use package "http://www.example.com/foo-1.0.tgz"'), $result, 'nodeps optional');

/****************************** force *************************************/
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$dep->setExtensions(array('foo' => '1.0'));

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'uri' => 'http://www.example.com/foo-1.0.tgz',
    ), true, array());
$phpunit->assertEquals(array('warning: pear/mine requires package "http://www.example.com/foo-1.0.tgz"'), $result, 'force required');

// optional
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'uri' => 'http://www.example.com/foo-1.0.tgz',
    ), false, array());
$phpunit->assertEquals(array('pear/mine can optionally use package "http://www.example.com/foo-1.0.tgz"'), $result, 'force optional');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
