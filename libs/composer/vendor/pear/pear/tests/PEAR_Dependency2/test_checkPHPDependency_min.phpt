--TEST--
PEAR_Dependency2->checkPHPDependency() min test failure
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

$dep->setPHPversion('4.3.9');

$result = $dep->validatePhpDependency(
    array(
        'min' => '5.0.0',
        'max' => '6.0.0',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP (version >= 5.0.0, version <= 6.0.0), installed version is 4.3.9')
), 'min');
$phpunit->assertIsa('PEAR_Error', $result, 'min');

/****************************** nodeps *************************************/
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$dep->setPHPversion('4.3.9');

$result = $dep->validatePhpDependency(
    array(
        'min' => '5.0.0',
        'max' => '6.0.0',
    ));
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PHP (version >= 5.0.0, version <= 6.0.0), installed version is 4.3.9',
), $result, 'min nodeps');

/****************************** force *************************************/
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$dep->setPHPversion('4.3.9');

$result = $dep->validatePhpDependency(
    array(
        'min' => '5.0.0',
        'max' => '6.0.0',
    ));
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PHP (version >= 5.0.0, version <= 6.0.0), installed version is 4.3.9',
), $result, 'min force');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
