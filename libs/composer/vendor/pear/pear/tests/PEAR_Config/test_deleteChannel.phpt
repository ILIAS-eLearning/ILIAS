--TEST--
PEAR_Config->deleteChannel()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
require_once 'PEAR/ChannelFile.php';
$ch = new PEAR_ChannelFile;
$ch->setName('mychannel');
$ch->setSummary('mychannel');
$ch->setServer('mychannel');
$ch->setDefaultPEARProtocols();
$config = new PEAR_Config($temp_path . DIRECTORY_SEPARATOR . 'pear.ini', $temp_path .
    DIRECTORY_SEPARATOR . 'nofile');
$reg = &$config->getRegistry();
$reg->addChannel($ch);
$config->removeLayer('user');
$phpunit->assertTrue($config->setChannels(array('pear.php.net', '__uri', 'mychannel')), 'set');
$config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'hi', 'user', 'mychannel');
$phpunit->assertEquals(array (
  '__channels' => 
  array (
    '__uri' => 
    array (
    ),
    'mychannel' => 
    array (
      'php_dir' => $temp_path . DIRECTORY_SEPARATOR . 'hi',
    ),
  ),
), $config->configuration['user'], 'raw test');

$config->deleteChannel('mychannel');

$phpunit->assertEquals(array (
  '__channels' => 
  array (
    '__uri' => 
    array (
    ),
  ),
), $config->configuration['user'], 'raw test after delete');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
