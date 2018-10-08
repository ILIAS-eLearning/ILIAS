--TEST--
config-get command: bug #18581, "config-get -c" not returning channel's configuration when using alias
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$chf = new PEAR_ChannelFile;

$chf->fromXmlString($first = '<?xml version="1.0" encoding="UTF-8" ?>
<channel version="1.0" xmlns="http://pear.php.net/channel-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/channel-1.0 http://pear.php.net/dtd/channel-1.0.xsd">
    <name>pear.horde.org</name>
    <summary>Horde PEAR server</summary>
    <suggestedalias>horde</suggestedalias>
    <servers>
        <primary>
            <rest>
                <baseurl type="REST1.0">http://pear.horde.org/rest/</baseurl>
                <baseurl type="REST1.1">http://pear.horde.org/rest/</baseurl>
                <baseurl type="REST1.2">http://pear.horde.org/rest/</baseurl>
                <baseurl type="REST1.3">http://pear.horde.org/rest/</baseurl>
            </rest>
        </primary>
    </servers>
</channel>');
$phpunit->assertTrue($chf->validate(), 'initial parse');
$phpunit->assertNoErrors('after');

$registry = $config->getRegistry();
$registry->addChannel($chf);
$phpunit->assertNoErrors('Add channel to registry');

$e = $command->run('config-set', array('channel' => 'pear.horde.org'), array('www_dir', $temp_path . DIRECTORY_SEPARATOR . 'test'));
$phpunit->assertNoErrors('after');
$fakelog->getLog(); // Flush log

$e = $command->run('config-get', array('channel' => 'pear.horde.org'), array('www_dir'));
$phpunit->assertNoErrors('after');
$phpunit->assertEquals(array(
  0 =>
  array (
    'info' => $temp_path . DIRECTORY_SEPARATOR . 'test',
    'cmd'  => 'config-get',
  ),
), $fakelog->getLog(), 'Full length channel name config-get');

$e = $command->run('config-get', array('channel' => 'horde'), array('www_dir'));
$phpunit->assertNoErrors('after');
$phpunit->assertEquals(array(
  0 =>
  array (
    'info' => $temp_path . DIRECTORY_SEPARATOR . 'test',
    'cmd'  => 'config-get',
  ),
), $fakelog->getLog(), 'Alias channel name config-get');