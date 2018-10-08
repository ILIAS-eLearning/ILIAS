--TEST--
PEAR_Dependency2->checkExtensionDependency() exclude failure
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
        'max' => '2.0',
        'exclude' => '1.0'
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine is not compatible with PHP extension "foo" version 1.0')
), 'exclude 1');
$phpunit->assertIsa('PEAR_Error', $result, 'exclude 1');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '2.0',
        'exclude' => array('0.9', '1.0')
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine is not compatible with PHP extension "foo" version 1.0')
), 'exclude 1');
$phpunit->assertIsa('PEAR_Error', $result, 'exclude 1');
// conflicts

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'exclude' => '1.0',
        'conflicts' => true,
    ));
$phpunit->assertNoErrors('conflicts 1');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'exclude' => '1.9',
        'conflicts' => true,
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine conflicts with PHP extension "foo" (excluded versions: 1.9), installed version is 1.0')
), 'min');

// optional
$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '2.0',
        'exclude' => '1.0'
    ), false);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine is not compatible with PHP extension "foo" version 1.0')
), 'exclude 1');
$phpunit->assertIsa('PEAR_Error', $result, 'exclude 1');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '2.0',
        'exclude' => array('0.9', '1.0')
    ), false);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine is not compatible with PHP extension "foo" version 1.0')
), 'exclude 1');
$phpunit->assertIsa('PEAR_Error', $result, 'exclude 1');

/************************************* nodeps ******************************************/
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setExtensions(array('foo' => '1.0'));

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '2.0',
        'exclude' => '1.0'
    ));
$phpunit->assertNoErrors('nodeps exclude 1');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP extension "foo" version 1.0'), $result, 'exclude 1 nodeps');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '2.0',
        'exclude' => array('0.9', '1.0')
    ));
$phpunit->assertNoErrors('nodeps exclude 2');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP extension "foo" version 1.0'), $result, 'exclude 2 nodeps');

// optional
$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '2.0',
        'exclude' => '1.0'
    ), false);
$phpunit->assertNoErrors('nodeps exclude 1 optional');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP extension "foo" version 1.0'), $result, 'exclude 1 nodeps optional');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '2.0',
        'exclude' => array('0.9', '1.0')
    ), false);
$phpunit->assertNoErrors('nodeps exclude 2 optional');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP extension "foo" version 1.0'), $result, 'exclude 2 nodeps optional');

/************************************* force ******************************************/
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setExtensions(array('foo' => '1.0'));

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '2.0',
        'exclude' => '1.0'
    ));
$phpunit->assertNoErrors('nodeps exclude 1');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP extension "foo" version 1.0'), $result, 'exclude 1 nodeps');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '2.0',
        'exclude' => array('0.9', '1.0')
    ));
$phpunit->assertNoErrors('nodeps exclude 2');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP extension "foo" version 1.0'), $result, 'exclude 2 nodeps');

// optional
$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '2.0',
        'exclude' => '1.0'
    ), false);
$phpunit->assertNoErrors('nodeps exclude 1 optional');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP extension "foo" version 1.0'), $result, 'exclude 1 nodeps optional');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '0.1',
        'max' => '2.0',
        'exclude' => array('0.9', '1.0')
    ), false);
$phpunit->assertNoErrors('nodeps exclude 2 optional');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PHP extension "foo" version 1.0'), $result, 'exclude 2 nodeps optional');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
