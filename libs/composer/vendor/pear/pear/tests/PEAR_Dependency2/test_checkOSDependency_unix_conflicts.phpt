--TEST--
PEAR_Dependency2->checkOSDependency() unix OS conflicts
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

$dep->setOS('linux');
$result = $dep->validateOsDependency(array('name' => 'unix', 'conflicts' => 'yes'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot install pear/mine on any Unix system'),
),'linux');
$phpunit->assertIsa('PEAR_Error', $result, 'linux');

$dep->setOS('freebsd');
$result = $dep->validateOsDependency(array('name' => 'unix', 'conflicts' => 'yes'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot install pear/mine on any Unix system'),
),'freebsd');
$phpunit->assertIsa('PEAR_Error', $result, 'freebsd');

$dep->setOS('darwin');
$result = $dep->validateOsDependency(array('name' => 'unix', 'conflicts' => 'yes'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot install pear/mine on any Unix system'),
),'darwin');
$phpunit->assertIsa('PEAR_Error', $result, 'darwin');

$dep->setOS('sunos');
$result = $dep->validateOsDependency(array('name' => 'unix', 'conflicts' => 'yes'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot install pear/mine on any Unix system'),
),'linux');
$phpunit->assertIsa('PEAR_Error', $result, 'linux');

$dep->setOS('irix');
$result = $dep->validateOsDependency(array('name' => 'unix', 'conflicts' => 'yes'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot install pear/mine on any Unix system'),
),'irix');
$phpunit->assertIsa('PEAR_Error', $result, 'irix');

$dep->setOS('hpux');
$result = $dep->validateOsDependency(array('name' => 'unix', 'conflicts' => 'yes'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot install pear/mine on any Unix system'),
),'hpux');
$phpunit->assertIsa('PEAR_Error', $result, 'hpux');

$dep->setOS('aix');
$result = $dep->validateOsDependency(array('name' => 'unix', 'conflicts' => 'yes'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot install pear/mine on any Unix system'),
),'aix');
$phpunit->assertIsa('PEAR_Error', $result, 'aix');

$dep->setOS('WINDOWS');
$result = $dep->validateOsDependency(array('name' => 'unix'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Can only install pear/mine on a Unix system'),
),'windows');
$phpunit->assertIsa('PEAR_Error', $result, 'windows');

// nodeps
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setOS('WINDOWS');
$result = $dep->validateOsDependency(array('name' => 'unix'));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: Can only install pear/mine on a Unix system'), $result, 'nodeps');

// force
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setOS('WINDOWS');
$result = $dep->validateOsDependency(array('name' => 'unix'));
$phpunit->assertNoErrors('force');
$phpunit->assertEquals(array('warning: Can only install pear/mine on a Unix system'), $result, 'force');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
