--TEST--
config-get command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('config-get', array(), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'config-get expects 1 or 2 parameters'),
), 'no params');
$e = $command->run('config-get', array(), array('default_channel', 'user', 'hoo'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'config-get expects 1 or 2 parameters'),
), '1 params');
$e = $command->run('config-get', array(), array('default_channel', 'gronk'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'config-get: only the layers: "user" or "system" are supported'),
), 'unknown layer');
$e = $command->run('config-get', array('channel' => 'gronk'), array('php_dir'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Channel "gronk" does not exist'),
), 'unknown channel as option');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
