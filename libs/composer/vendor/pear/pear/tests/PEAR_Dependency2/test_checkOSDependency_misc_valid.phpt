--TEST--
PEAR_Dependency2->checkOSDependency() miscellaneous OS valid
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
$result = $dep->validateOsDependency(array('name' => 'foo'));
$phpunit->assertNoErrors('foo');
$phpunit->assertTrue($result, 'foo');

$dep->setOS('bar');
$result = $dep->validateOsDependency(array('name' => 'foo', 'conflicts' => 'yes'));
$phpunit->assertNoErrors('bar');
$phpunit->assertTrue($result, 'bar');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
