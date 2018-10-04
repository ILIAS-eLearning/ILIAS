--TEST--
PEAR_Dependency2->checkExtensionDependency() extension not loaded failure, also _getExtraString
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

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'conflicts' => true,
    ));
$phpunit->assertNoErrors('conflicts 1');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '1.1',
        'max' => '1.9',
        'conflicts' => true,
    ));
$phpunit->assertNoErrors('conflicts 2');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'min' => '1.1',
        'max' => '1.9',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo" (version >= 1.1, version <= 1.9)')
), 'minmax');
$phpunit->assertIsa('PEAR_Error', $result, 'minmax');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'max' => '1.9',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo" (version <= 1.9)')
), 'max');
$phpunit->assertIsa('PEAR_Error', $result, 'max');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'max' => '1.9',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo" (version <= 1.9)')
), 'max');
$phpunit->assertIsa('PEAR_Error', $result, 'min');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'max' => '1.9',
        'recommended' => '1.2',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo" (recommended version 1.2)')
), 'max');
$phpunit->assertIsa('PEAR_Error', $result, 'recommended');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'exclude' => array('1.8', '1.9'),
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo" (excluded versions: 1.8, 1.9)')
), 'excluded 1');
$phpunit->assertIsa('PEAR_Error', $result, 'excluded 1');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'exclude' => '1.8',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo" (excluded versions: 1.8)')
), 'excluded 2');
$phpunit->assertIsa('PEAR_Error', $result, 'excluded 2');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'max' => '1.9',
        'exclude' => '1.8',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo" (version <= 1.9, excluded versions: 1.8)')
), 'excluded 3');
$phpunit->assertIsa('PEAR_Error', $result, 'excluded 3');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PHP extension "foo"')
), 'nothing');
$phpunit->assertIsa('PEAR_Error', $result, 'nothing');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
    ), false);
$phpunit->assertNoErrors('nothing optional');
$phpunit->assertEquals(array('pear/mine can optionally use PHP extension "foo"'), $result, 'nothing optional');

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'max' => '1.9',
        'exclude' => '1.8',
    ), false);
$phpunit->assertNoErrors('extra optional');
$phpunit->assertEquals(array('pear/mine can optionally use PHP extension "foo" (version <= 1.9, excluded versions: 1.8)'), $result, 'extra optional');

$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'max' => '1.9',
        'exclude' => '1.8',
    ));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: pear/mine requires PHP extension "foo" (version <= 1.9, excluded versions: 1.8)'), $result, 'nodeps');

$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$result = $dep->validateExtensionDependency(
    array(
        'name' => 'foo',
        'max' => '1.9',
        'exclude' => '1.8',
    ));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: pear/mine requires PHP extension "foo" (version <= 1.9, excluded versions: 1.8)'), $result, 'nodeps');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
