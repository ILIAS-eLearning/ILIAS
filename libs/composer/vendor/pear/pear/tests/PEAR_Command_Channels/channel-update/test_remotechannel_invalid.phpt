--TEST--
channel-update command (remote channel name, changes channel name)
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
$c = $reg->getChannel(strtolower('pear.php.net'));
$pathtochannelxml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'files'. DIRECTORY_SEPARATOR . 'invalidchannel.xml';
$GLOBALS['pearweb']->addHtmlConfig('http://pear.php.net/channel.xml', $pathtochannelxml);
$e = $command->run('channel-update', array(), array('pear.php.net'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' =>
        'ERROR: downloaded channel definition file for channel "oops.we.changedit" from channel "pear.php.net"'),
), 'after');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Updating channel "pear.php.net"',
    'cmd' => 'channel-update',
  ),
), $fakelog->getLog(), 'log');

$reg = new PEAR_Registry($temp_path . DIRECTORY_SEPARATOR . 'php');
$chan = $reg->getChannel('pear.php.net');
$phpunit->assertIsA('PEAR_ChannelFile', $chan, 'updated ok?');
$phpunit->assertEquals('pear.php.net', $chan->getName(), 'name ok?');
$phpunit->assertEquals('PHP Extension and Application Repository', $chan->getSummary(), 'summary ok?');


$e = $command->run('channel-update', array('force' => true), array('pear.php.net'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' =>
        'Error: Channel "oops.we.changedit" does not exist, use channel-add to add an entry'),
), 'after force');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Updating channel "pear.php.net"',
    'cmd' => 'channel-update',
  ),
  1 =>
  array (
    0 => 0,
    1 => 'WARNING: downloaded channel definition file for channel "oops.we.changedit" from channel "pear.php.net"',
  ),
), $fakelog->getLog(), 'log force');

$reg = new PEAR_Registry($temp_path . DIRECTORY_SEPARATOR . 'php');
$chan = $reg->getChannel('pear.php.net');
$phpunit->assertIsA('PEAR_ChannelFile', $chan, 'updated ok?');
$phpunit->assertEquals('pear.php.net', $chan->getName(), 'name ok?');
$phpunit->assertEquals('PHP Extension and Application Repository', $chan->getSummary(), 'summary ok?');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
