--TEST--
PEAR_Dependency2->checkOSDependency() windows OS conflicts
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

$dep->setOS('darwin');
$result = $dep->validateOsDependency(array('name' => 'windows'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Can only install pear/mine on Windows'),
), 'windows/darwin');
$phpunit->assertIsa('PEAR_Error', $result, 'windows/darwin');

$dep->setOS('WINDOWS');
$result = $dep->validateOsDependency(array('name' => 'windows', 'conflicts' => 'yes'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot install pear/mine on Windows'),
), 'windows/windows conflicts');
$phpunit->assertIsa('PEAR_Error', $result, 'windows/windows conflicts');

// nodeps
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setOS('darwin');
$result = $dep->validateOsDependency(array('name' => 'windows'));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: Can only install pear/mine on Windows'), $result, 'nodeps');

$dep->setOS('WINDOWS');
$result = $dep->validateOsDependency(array('name' => 'windows', 'conflicts' => 'yes'));
$phpunit->assertNoErrors('nodeps conflict');
$phpunit->assertEquals(array('warning: Cannot install pear/mine on Windows'), $result, 'nodeps conflict');

// force
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setOS('darwin');
$result = $dep->validateOsDependency(array('name' => 'windows'));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: Can only install pear/mine on Windows'), $result, 'nodeps');

$dep->setOS('WINDOWS');
$result = $dep->validateOsDependency(array('name' => 'windows', 'conflicts' => 'yes'));
$phpunit->assertNoErrors('nodeps conflict');
$phpunit->assertEquals(array('warning: Cannot install pear/mine on Windows'), $result, 'nodeps conflict');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
