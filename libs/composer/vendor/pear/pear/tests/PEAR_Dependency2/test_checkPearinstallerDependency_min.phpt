--TEST--
PEAR_Dependency2->checkPearinstallerDependency() min test failure
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
        'min' => '1.3.0',
    ));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires PEAR Installer (version >= 1.3.0), installed version is 1.2.0')
), 'min');
$phpunit->assertIsa('PEAR_Error', $result, 'min');

/****************************** nodeps *************************************/
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$dep->setPEARversion('1.2.0');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '1.3.0',
    ));
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PEAR Installer (version >= 1.3.0), installed version is 1.2.0'), $result, 'min nodeps');

/****************************** force *************************************/
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);

$dep->setPEARversion('1.2.0');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '1.3.0',
    ));
$phpunit->assertEquals(array (
  0 => 'warning: pear/mine requires PEAR Installer (version >= 1.3.0), installed version is 1.2.0'), $result, 'min force');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
