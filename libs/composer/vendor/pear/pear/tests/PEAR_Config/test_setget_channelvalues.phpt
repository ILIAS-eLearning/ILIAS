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
// failures
$phpunit->assertFalse($config->set('php_dir', $temp_path . DIRECTORY_SEPARATOR . 'oops', 'user', 'unknown'), 'unknown channel');
$phpunit->assertFalse($config->set('sig_bin', 'oops', 'user', '__uri'), 'global value');
// successes

$config->setChannels(array('pear.php.net', '__uri'));
$phpunit->assertTrue($config->set('bin_dir', 'yay', 'system', '__uri'), 'global value');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'bin',
    $config->get('bin_dir', 'user', 'pear.php.net'),
    'confirm bin_dir=yay 1');
$phpunit->assertEquals(null, $config->get('bin_dir', 'system', 'pear.php.net'),
    'confirm bin_dir=yay 2');
$phpunit->assertEquals(null, $config->get('bin_dir', 'system'),
    'confirm bin_dir=yay 3');
$phpunit->assertEquals('yay', $config->get('bin_dir', 'system', '__uri'),
    'confirm bin_dir=yay 4');

$config->set('default_channel', '__uri');
$phpunit->assertEquals($temp_path . DIRECTORY_SEPARATOR . 'bin', $config->get('bin_dir', 'user'), 'default __uri user');
$phpunit->assertEquals('yay', $config->get('bin_dir', 'system'), 'default __uri system');
$phpunit->assertEquals('yay', $config->get('bin_dir'), 'default __uri default');
$phpunit->assertTrue($config->set('default_channel', 'pear'), 'set pear');
$phpunit->assertEquals('pear.php.net', $config->get('default_channel'), 'pear default');

$reg = &$config->getRegistry();
$ch = $reg->getChannel('__uri');
// this is laziness - no require_once/new needed
$ch->setName('foo');
$ch->addMirror('foo.example.com');
$ch->setDefaultPEARProtocols();
$ch->setDefaultPEARProtocols('1.0', 'foo.example.com');
$reg->addChannel($ch);
$config->setChannels($reg->listChannels());

$phpunit->assertEquals('foo', $config->get('preferred_mirror', null, 'foo'), 'Bug #8516 test - make sure preferred_mirror is right for foo');
$phpunit->assertTrue($config->set('default_channel', 'foo'), 'set default channel to foo');
$phpunit->assertEquals('pear.php.net', $config->get('preferred_mirror', null, 'pear.php.net'), 'Bug #8516 test - make sure preferred_mirror is right for pear');
$phpunit->assertEquals('foo', $config->get('preferred_mirror', null, 'foo'), 'before set to foo.example.com');
$phpunit->assertTrue($config->set('preferred_mirror', 'foo.example.com', 'user', 'foo'), 'set to foo.example.com');
$phpunit->assertEquals('pear.php.net', $config->get('preferred_mirror', null, 'pear.php.net'), 'after set to foo.example.com, pear');
$phpunit->assertEquals('foo.example.com', $config->get('preferred_mirror', null, 'foo'), 'after set to foo.example.com, foo');
$phpunit->assertFalse($config->set('preferred_mirror', 'foor.example.com', 'user', 'foo'), 'set to foor.example.com');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
