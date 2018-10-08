--TEST--
PEAR_Dependency2->checkPearinstallerDependency() exclude failure
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

$dep->setPEARversion('1.2.0');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '1.0',
        'exclude' => '1.2.0',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine is not compatible with PEAR Installer version 1.2.0')
), 'exclude 1');
$phpunit->assertIsa('PEAR_Error', $result, 'exclude 1');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '1.0',
        'exclude' => array('1.2.0', '1.2.1'),
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine is not compatible with PEAR Installer version 1.2.0')
), 'exclude 1');
$phpunit->assertIsa('PEAR_Error', $result, 'exclude 1');
// nodeps
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 1');

$dep->setPEARversion('1.2.0');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '1.0',
        'exclude' => '1.2.0',
    ));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PEAR Installer version 1.2.0'), $result, 'nodeps');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '1.0',
        'exclude' => array('1.2.0', '1.2.1'),
    ));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PEAR Installer version 1.2.0'), $result, 'nodeps');


// force
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 1');

$dep->setPEARversion('1.2.0');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '1.0',
        'exclude' => '1.2.0',
    ));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PEAR Installer version 1.2.0'), $result, 'force');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '1.0',
        'exclude' => array('1.2.0', '1.2.1'),
    ));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: pear/mine is not compatible with PEAR Installer version 1.2.0'), $result, 'force');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
