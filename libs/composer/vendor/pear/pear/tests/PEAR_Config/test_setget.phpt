--TEST--
PEAR_Config->set() and PEAR_Config->get()
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
$reg = $config->getRegistry();
$ch = $reg->getChannel('pear.php.net');
$ch->addMirror('poo.example.com');
$ch->setDefaultPEARProtocols('1.0', 'poo.example.com');
$reg->updateChannel($ch);
// failures
$phpunit->assertFalse($config->set('__channels', 'oops'), '__channels');
$phpunit->assertFalse($config->set('###', 'oops'), '###');
$phpunit->assertFalse($config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'hi', 'gronk'), 'gronk layer');
$phpunit->assertFalse($config->set('preferred_mirrors', 'oops'), 'unknown mirror');
// successes
$phpunit->assertTrue($config->set('data_dir', 'hi'), 'data_dir=hi');
$phpunit->assertEquals('hi', $config->get('data_dir', 'user', 'pear.php.net'),
    'confirm data_dir=hi 1');
$phpunit->assertEquals(null, $config->get('data_dir', 'system', 'pear.php.net'),
    'confirm data_dir=hi 2');
$phpunit->assertTrue($config->set('preferred_mirror', 'poo.example.com'), 'preferred_mirror=poo.example.com');
$phpunit->assertEquals('poo.example.com', $config->get('preferred_mirror', 'user', 'pear.php.net'),
    'confirm preferred_mirror=poo.example.com 1');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
