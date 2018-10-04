--TEST--
PEAR_Registry->updateChannel() (API v1.1)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
require_once 'PEAR/Registry.php';
$pv = phpversion() . '';
$av = $pv{0} == '4' ? 'apiversion' : 'apiVersion';
if (!in_array($av, get_class_methods('PEAR_Registry'))) {
    echo 'skip';
}
if (PEAR_Registry::apiVersion() != '1.1') {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$ch = new PEAR_ChannelFile;
$ch->setName('test.test.test');
$ch->setAlias('foo');
$ch->setSummary('blah');
$ch->setDefaultPEARProtocols();
$reg->addChannel($ch);
$phpunit->assertNoErrors('setup');

$contents = unserialize(implode('', file($statedir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.channels' . DIRECTORY_SEPARATOR .
    'test.test.test.reg')));
$phpunit->assertTrue(isset($contents['_lastmodified']), 'lastmodified not set');
unset($contents['_lastmodified']);
$phpunit->assertEquals($ch->toArray(), $contents, 'contents');

$ch->setAlias('gronk');
$phpunit->assertEquals('gronk', $ch->getAlias(), 'make sure set worked');
$reg->updateChannel($ch);
$phpunit->assertNoErrors('setup');

$contents = unserialize(implode('', file($statedir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.channels' . DIRECTORY_SEPARATOR .
    'test.test.test.reg')));
$phpunit->assertTrue(isset($contents['_lastmodified']), 'lastmodified not set');
unset($contents['_lastmodified']);
$phpunit->assertEquals($ch->toArray(), $contents, 'contents');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
