--TEST--
channel-discover command
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pathtochannelxml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'files'. DIRECTORY_SEPARATOR . 'channel.xml';
$GLOBALS['pearweb']->addHtmlConfig('http://zornk.php.net/channel.xml', $pathtochannelxml);
$e = $command->run('channel-discover', array(), array('zornk.php.net'));
$phpunit->assertNoErrors('after');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Adding Channel "zornk.php.net" succeeded',
    'cmd' => 'channel-discover', 
  ),
  1 =>
  array (
    'info' => 'Discovery of channel "zornk.php.net" succeeded',
    'cmd' => 'channel-discover', 
  ),
), $fakelog->getLog(), 'log');

$reg = new PEAR_Registry($temp_path . DIRECTORY_SEPARATOR . 'php');
$chan = $reg->getChannel('zornk');
$phpunit->assertIsA('PEAR_ChannelFile', $chan, 'updated ok?');
$phpunit->assertEquals('zornk.php.net', $chan->getName(), 'name ok?');
$phpunit->assertEquals('foo', $chan->getSummary(), 'summary ok?');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
