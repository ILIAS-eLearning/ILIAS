--TEST--
channel-add command
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$ch = new PEAR_ChannelFile;
$ch->setName('fake');
$ch->setSummary('fake');
$ch->setBaseURL('REST1.0', 'http://pear.example.com/rest');
$ch->setDefaultPEARProtocols();

$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'fakechannel.xml', 'wb');
fwrite($fp, $ch->toXml());
fclose($fp);

$e = $command->run('channel-add', array(), array($temp_path . DIRECTORY_SEPARATOR . 'fakechannel.xml'));
$phpunit->assertNoErrors('after');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Adding Channel "fake" succeeded',
    'cmd' => 'channel-add',
  ),
), $fakelog->getLog(), 'log');

$reg = new PEAR_Registry($temp_path . DIRECTORY_SEPARATOR . 'php');
$chan = $reg->getChannel('fake');
$phpunit->assertIsA('PEAR_ChannelFile', $chan, 'added ok?');
$phpunit->assertEquals('fake', $chan->getName(), 'name ok?');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
