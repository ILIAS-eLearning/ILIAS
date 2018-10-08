--TEST--
config-set command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('config-set', array(), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'config-set expects 2 or 3 parameters'),
), 'no params');
$e = $command->run('config-set', array(), array('default_channel'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'config-set expects 2 or 3 parameters'),
), '1 params');
$e = $command->run('config-set', array(), array('default_channel', 'pear', 'gronk'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'config-set: only the layers: "user" or "system" are supported'),
), 'unknown layer');
$e = $command->run('config-set', array(), array('default_channel', 'gronk'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Channel "gronk" does not exist'),
), 'unknown channel');
$e = $command->run('config-set', array('channel' => 'gronk'), array('php_dir', 'gronk'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Channel "gronk" does not exist'),
), 'unknown channel as option');
$e = $command->run('config-set', array(), array('__channels', 'gronk'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'config-set (__channels, gronk, user) failed, channel pear.php.net'),
), 'unknown channel as option');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
