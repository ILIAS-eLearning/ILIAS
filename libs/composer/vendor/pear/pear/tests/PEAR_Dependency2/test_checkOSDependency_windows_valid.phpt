--TEST--
PEAR_Dependency2->checkOSDependency() windows OS valid
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

$dep->setOS('WINDOWS');
$result = $dep->validateOsDependency(array('name' => 'windows'));
$phpunit->assertNoErrors('windows');
$phpunit->assertTrue($result, 'windows');

$dep->setOS('darwin');
$result = $dep->validateOsDependency(array('name' => 'windows', 'conflicts' => 'yes'));
$phpunit->assertNoErrors('windows/darwin conflicts');
$phpunit->assertTrue($result, 'windows/darwin conflicts');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
