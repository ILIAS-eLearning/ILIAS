--TEST--
list command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('list', array('channel' => 'gronk'), array());
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
