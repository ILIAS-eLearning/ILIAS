--TEST--
channel-info command (remote channel.xml file failure)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('channel-info', array(), array('smurg'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Unknown channel "smurg"'),
), '1');
$phpunit->showall();
$phpunit->assertEquals(array (
), $fakelog->getLog(), 'log 1');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
