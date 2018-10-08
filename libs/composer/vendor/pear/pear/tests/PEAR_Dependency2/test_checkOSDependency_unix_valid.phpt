--TEST--
PEAR_Dependency2->checkOSDependency() unix OS valid
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
$result = $dep->validateOsDependency(array('name' => 'unix'));
$phpunit->assertNoErrors('linux');
$phpunit->assertTrue($result, 'linux');

$dep->setOS('freebsd');
$result = $dep->validateOsDependency(array('name' => 'unix'));
$phpunit->assertNoErrors('freebsd');
$phpunit->assertTrue($result, 'freebsd');

$dep->setOS('darwin');
$result = $dep->validateOsDependency(array('name' => 'unix'));
$phpunit->assertNoErrors('darwin');
$phpunit->assertTrue($result, 'darwin');

$dep->setOS('sunos');
$result = $dep->validateOsDependency(array('name' => 'unix'));
$phpunit->assertNoErrors('sunos');
$phpunit->assertTrue($result, 'sunos');

$dep->setOS('irix');
$result = $dep->validateOsDependency(array('name' => 'unix'));
$phpunit->assertNoErrors('irix');
$phpunit->assertTrue($result, 'irix');

$dep->setOS('hpux');
$result = $dep->validateOsDependency(array('name' => 'unix'));
$phpunit->assertNoErrors('hpux');
$phpunit->assertTrue($result, 'hpux');

$dep->setOS('aix');
$result = $dep->validateOsDependency(array('name' => 'unix'));
$phpunit->assertNoErrors('aix');
$phpunit->assertTrue($result, 'aix');

$dep->setOS('WINDOWS');
$result = $dep->validateOsDependency(array('name' => 'unix', 'conflicts' => 'yes'));
$phpunit->assertNoErrors('windows');
$phpunit->assertTrue($result, 'windows');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
