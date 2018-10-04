--TEST--
PEAR_Dependency2->checkPHPDependency() exclude failure
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
        'min' => '4.0.0',
        'max' => '5.0.0',
        'exclude' => '4.3.9'
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine is not compatible with PHP version 4.3.9')
), 'exclude 1');
$phpunit->assertIsa('PEAR_Error', $result, 'exclude 1');

$result = $dep->validatePhpDependency(
    array(
        'min' => '4.0.0',
        'max' => '5.0.0',
        'exclude' => array('4.3.9','4.3.10')
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine is not compatible with PHP version 4.3.9')
), 'exclude 2');
$phpunit->assertIsa('PEAR_Error', $result, 'exclude 2');

// nodeps
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 1');

$dep->setPHPversion('4.3.9');

$result = $dep->validatePhpDependency(
    array(
        'min' => '4.0.0',
        'max' => '5.0.0',
        'exclude' => '4.3.9'
    ));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP version 4.3.9'), $result, 'nodeps');

$result = $dep->validatePhpDependency(
    array(
        'min' => '4.0.0',
        'max' => '5.0.0',
        'exclude' => array('4.3.9','4.3.10')
    ));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP version 4.3.9'), $result, 'nodeps');


// force
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 1');

$dep->setPHPversion('4.3.9');

$result = $dep->validatePhpDependency(
    array(
        'min' => '4.0.0',
        'max' => '5.0.0',
        'exclude' => '4.3.9'
    ));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP version 4.3.9'), $result, 'nodeps');

$result = $dep->validatePhpDependency(
    array(
        'min' => '4.0.0',
        'max' => '5.0.0',
        'exclude' => array('4.3.9','4.3.10')
    ));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP version 4.3.9'), $result, 'nodeps');echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
