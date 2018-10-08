--TEST--
PEAR_Dependency2->checkOSDependency() miscellaneous OS
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

$dep->setOS('foo');
$result = $dep->validateOsDependency(array('name' => 'foo', 'conflicts' => 'yes'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot install pear/mine on foo operating system'),
), 'foo');
$phpunit->assertIsa('PEAR_Error', $result, 'foo');

$dep->setOS('bar');
$result = $dep->validateOsDependency(array('name' => 'foo'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot install pear/mine on bar operating system, can only install on foo'),
), 'bar');
$phpunit->assertIsa('PEAR_Error', $result, 'bar');

// nodeps
$dep = new test_PEAR_Dependency2($config, array('nodeps' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setOS('foo');
$result = $dep->validateOsDependency(array('name' => 'foo', 'conflicts' => 'yes'));
$phpunit->assertNoErrors('nodeps conflict');
$phpunit->assertEquals(array('warning: Cannot install pear/mine on foo operating system'), $result, 'nodeps conflict');

$dep->setOS('bar');
$result = $dep->validateOsDependency(array('name' => 'foo'));
$phpunit->assertNoErrors('nodeps');
$phpunit->assertEquals(array('warning: Cannot install pear/mine on bar operating system, can only install on foo'), $result, 'nodeps');

// force
$dep = new test_PEAR_Dependency2($config, array('force' => true), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setOS('foo');
$result = $dep->validateOsDependency(array('name' => 'foo', 'conflicts' => 'yes'));
$phpunit->assertNoErrors('force conflict');
$phpunit->assertEquals(array('warning: Cannot install pear/mine on foo operating system'), $result, 'force conflict');

$dep->setOS('bar');
$result = $dep->validateOsDependency(array('name' => 'foo'));
$phpunit->assertNoErrors('force');
$phpunit->assertEquals(array('warning: Cannot install pear/mine on bar operating system, can only install on foo'), $result, 'force');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
