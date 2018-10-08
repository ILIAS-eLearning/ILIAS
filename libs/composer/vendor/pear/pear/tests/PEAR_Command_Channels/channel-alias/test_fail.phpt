--TEST--
channel-alias command failure
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
$e = $command->run('channel-alias', array(), array('pear.php.net'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'No channel alias specified'),
), 'no params');
$e = $command->run('channel-alias', array(), array('pear.php.net', 'foo', 'bar'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Invalid format, correct is: channel-alias channel alias'),
), 'nonexistent');
$e = $command->run('channel-alias', array(), array('zonk', 'pear'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => '"zonk" is not a valid channel'),
), 'pear');
$e = $command->run('channel-alias', array(), array('__uri', 'pear'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Channel "pear.php.net" is already aliased to "pear", cannot re-alias'),
), 'pear.php.net');
$e = $command->run('channel-alias', array(), array('__uri', '@#$^*&@'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Alias "@#$^*&@" is not a valid channel alias'),
    array('package' => 'PEAR_ChannelFile', 'message' => 'Invalid channel suggestedalias "@#$^*&@"'),
), '__uri');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
