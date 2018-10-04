--TEST--
channel-update command (remote channel name up to date)
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
    'files'. DIRECTORY_SEPARATOR . 'pearchannel.xml';
$GLOBALS['pearweb']->addHtmlConfig('http://pear.php.net/channel.xml', $pathtochannelxml);
$return304 = true;
$e = $command->run('channel-update', array(), array('pear.php.net'));
unset($return304);
$phpunit->assertNoErrors('after');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Updating channel "pear.php.net"',
    'cmd' => 'channel-update',
  ),
  1 =>
  array (
    'info' => 'Channel "pear.php.net" is up to date',
    'cmd' => 'no command', 
  ),
), $fakelog->getLog(), 'log');

$reg = new PEAR_Registry($temp_path . DIRECTORY_SEPARATOR . 'php');
$chan = $reg->getChannel('pear.php.net');
$phpunit->assertIsa('PEAR_ChannelFile', $chan, 'updated ok?');
$phpunit->assertEquals('pear.php.net', $chan->getName(), 'name ok?');
$phpunit->assertEquals('PHP Extension and Application Repository', $chan->getSummary(), 'summary ok?');

$e = $command->run('channel-update', array('force' => true), array('pear.php.net'));
$phpunit->assertNoErrors('after');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Updating channel "pear.php.net"',
    'cmd' => 'channel-update',
  ),
  1 =>
  array (
    'info' => 'Update of Channel "pear.php.net" succeeded',
    'cmd' => 'no command', 
  ),
), $fakelog->getLog(), 'log');

$reg = new PEAR_Registry($temp_path . DIRECTORY_SEPARATOR . 'php');
$chan = $reg->getChannel('pear.php.net');
$phpunit->assertIsa('PEAR_ChannelFile', $chan, 'updated ok?');
$phpunit->assertEquals('pear.php.net', $chan->getName(), 'name ok?');
$phpunit->assertEquals('foo', $chan->getSummary(), 'summary ok?');echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
