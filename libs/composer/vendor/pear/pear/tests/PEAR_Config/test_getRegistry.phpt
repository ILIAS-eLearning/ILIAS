--TEST--
PEAR_Config->getRegistry()
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
$reg = new PEAR_Registry($temp_path . DIRECTORY_SEPARATOR . 'php');
$ch = new PEAR_ChannelFile;
$ch->setName('__uri');
$ch->setServer('server');
$ch->setSummary('sum');
$ch->setDefaultPEARProtocols();
$reg->addChannel($ch);
$config = new PEAR_Config($temp_path . DIRECTORY_SEPARATOR . 'zoo.ini', $temp_path . DIRECTORY_SEPARATOR . 'zoo.ini');
$phpunit->assertIsa('PEAR_Registry', $config->getRegistry(), 'initial blank');
$phpunit->assertIsa('PEAR_Registry', $config->getRegistry('default'), 'initial default');
$phpunit->assertFalse($config->getRegistry('user'), 'initial user');
$phpunit->assertFalse($config->getRegistry('system'), 'initial system');

$config->setChannels(array('pear.php.net', '__uri'));
$config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'whoo', 'user', '__uri');
$phpunit->assertFalse($config->getRegistry('user'), 'set channel php_dir user');
$phpunit->assertFalse($config->getRegistry('system'), 'set channel php_dir system');

$config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'whoo');
$phpunit->assertIsa('PEAR_Registry', $config->getRegistry(), 'set php_dir user');
$r = &$config->getRegistry();
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'whoo' . DIRECTORY_SEPARATOR . '.registry', $r->statedir, 'whoo statedir');
$phpunit->assertFalse($config->getRegistry('system'), 'set php_dir system');

$config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'whoop', 'system');
$phpunit->assertIsa('PEAR_Registry', $config->getRegistry('user'), 'set php_dir user');
$r1 = &$config->getRegistry('user');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'whoo' . DIRECTORY_SEPARATOR . '.registry', $r1->statedir, 'whoo statedir 2');
$phpunit->assertIsa('PEAR_Registry', $config->getRegistry('system'), 'set php_dir system');
$r2 = &$config->getRegistry('system');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'whoop' . DIRECTORY_SEPARATOR . '.registry', $r2->statedir, 'whoop statedir');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
