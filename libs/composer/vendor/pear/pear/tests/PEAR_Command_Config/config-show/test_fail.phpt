--TEST--
config-show command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('config-show', array(), array('gronk'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'config-show: only the layers: "user" or "system" are supported'),
), 'unknown layer');
$e = $command->run('config-show', array('channel' => 'gronk'), array());
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
