--TEST--
list-all command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$reg = &$config->getRegistry();
$e = $command->run('list-all', array('channel' => 'smoog'), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Channel "smoog" does not exist'),
), 'unknown channel');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
