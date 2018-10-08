--TEST--
channel-discover command failure
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
$e = $command->run('channel-discover', array(), array('pear'));
$phpunit->assertErrors(array(
    array(
        'package' => 'PEAR_Error',
        'message' => 'A channel alias named "pear" already exists, aliasing channel "pear.php.net"'
    ),
), 'no params');

$e = $command->run('channel-discover', array(), array('pear.php.net'));
$phpunit->assertErrors(array(
    array(
        'package' => 'PEAR_Error',
        'message' => 'Channel "pear.php.net" is already initialized'
    ),
), 'nonexistent');

$e = $command->run('channel-discover', array(), array());
$phpunit->assertErrors(array(
    array(
        'package' => 'PEAR_Error',
        'message' => 'No channel server specified'
    ),
), 'pear');

$e = $command->run('channel-discover', array(), array('zornk.net'));
$phpunit->assertErrors(array(
    array(
        'package' => 'PEAR_Error',
        'message' => 'Discovery of channel "zornk.net" failed (channel-add: Cannot open "https://zornk.net/channel.xml" (File https://zornk.net:443/channel.xml not valid (received: HTTP/1.1 404 https://zornk.net/channel.xml Is not valid)))'
    ),
), 'zornk');

$phpunit->assertNoErrors('all errors caught');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
