--TEST--
PEAR_Config->getDefaultChannel()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$config = new PEAR_Config($temp_path . DIRECTORY_SEPARATOR . 'pear.ini', $temp_path .
    DIRECTORY_SEPARATOR . 'nofile');
require_once 'PEAR/ChannelFile.php';
$ch = new PEAR_ChannelFile;
$ch->setName('mychannel');
$ch->setSummary('mychannel');
$ch->setServer('mychannel');
$ch->setDefaultPEARProtocols();
$reg = &$config->getRegistry();
$phpunit->assertTrue($reg->addChannel($ch), 'add channel');

$phpunit->assertEquals('pear.php.net', $config->getDefaultChannel(), 'user');
$phpunit->assertTrue($config->set('default_channel', 'mychannel', 'user'), 'set');
$phpunit->assertEquals('mychannel', $config->getDefaultChannel(), 'user 2');
$phpunit->assertEquals('pear.php.net', $config->getDefaultChannel('system'), 'system');
$phpunit->assertTrue($config->set('default_channel', 'mychannel', 'user', 'mypackage'), 'channel set');
$phpunit->assertEquals('mychannel', $config->getDefaultChannel(), 'user');
$phpunit->assertEquals('pear.php.net', $config->getDefaultChannel('system'), 'system 2');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
