--TEST--
channel-add command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('channel-add', array(), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'channel-add: no channel file specified'),
), 'no params');
$e = $command->run('channel-add', array(), array('@#$#$@'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'channel-add: cannot open "@#$#$@"'),
), 'no params');
touch($temp_path . DIRECTORY_SEPARATOR . 'nofile.xml');
$e = $command->run('channel-add', array(), array($temp_path . DIRECTORY_SEPARATOR . 'nofile.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'channel-add: invalid channel.xml file'),
    array('package' => 'PEAR_ChannelFile', 'message' => 'No version number found in <channel> tag'),
), 'no params');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Error: No version number found in <channel> tag',
    'cmd' => 'no command',
  ),
), $fakelog->getLog(), 'log, bad file');

$ch = new PEAR_ChannelFile;
$ch->setName('pear.php.net');
$ch->setSummary('fake');
$ch->setDefaultPEARProtocols();
$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'fakechannel.xml', 'wb');
fwrite($fp, $ch->toXml());
fclose($fp);

$e = $command->run('channel-add', array(), array($temp_path . DIRECTORY_SEPARATOR . 'fakechannel.xml'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'channel-add: Channel "pear.php.net" exists,' .
        ' use channel-update to update entry'),
), 'no params');

$phpunit->assertEquals(array (
), $fakelog->getLog(), 'log, bad file');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
