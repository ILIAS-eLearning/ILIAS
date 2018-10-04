--TEST--
PEAR_Registry->deleteChannel() (API v1.1)
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

$ret = $reg->deleteChannel('snakr');
$phpunit->assertFalse($ret, 'snakr');

$ret = $reg->deleteChannel(array());
$phpunit->assertFalse($ret, 'array()');

$ch = new PEAR_ChannelFile;

$ret = $reg->deleteChannel($ch);
$phpunit->assertErrors(array(
array('package' => 'PEAR_ChannelFile', 'message' => 'Missing channel name'),
array('package' => 'PEAR_ChannelFile', 'message' => 'Missing channel summary'),
), 'invalid channelfile validation errors');
$phpunit->assertFalse($ret, 'empty channelfile');

$ch->setName('test.test.test');
$ch->setAlias('foo');
$ch->setSummary('blah');
$ch->setDefaultPEARProtocols();

$ret = $reg->deleteChannel($ch);
$phpunit->assertFalse($ret, 'delete non-existent channel from channelfile');

$ch->setName('__uri');
$ret = $reg->deleteChannel($ch);
$phpunit->assertFalse($ret, 'delete __uri 1');
$ret = $reg->deleteChannel('__uri');
$phpunit->assertFalse($ret, 'delete __uri 2');

$ch->setName('pear.php.net');
$ret = $reg->deleteChannel($ch);
$phpunit->assertFalse($ret, 'delete pear.php.net 1');
$ret = $reg->deleteChannel('pear.php.net');
$phpunit->assertFalse($ret, 'delete pear.php.net 2');

$ch->setName('test.test.test');
$ret = $reg->addChannel($ch);
$phpunit->assertNoErrors('setup');
$phpunit->assertTrue($ret, 'result add');
$phpunit->assertFileExists($statedir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.channels' . DIRECTORY_SEPARATOR .
    'test.test.test.reg', 'pre-delete test.test.test.reg');

$contents = unserialize(implode('', file($statedir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.channels' . DIRECTORY_SEPARATOR .
    'test.test.test.reg')));
$phpunit->assertTrue(isset($contents['_lastmodified']), 'lastmodified not set');
unset($contents['_lastmodified']);
$phpunit->assertEquals($ch->toArray(), $contents, 'contents');

$ret = $reg->deleteChannel('test.test.test');
$phpunit->assertTrue($ret, 'test.test.test 1');
$phpunit->assertFileNotExists($statedir . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.channels' . DIRECTORY_SEPARATOR .
    'test.test.test.reg', 'test delete test.test.test.reg');
$phpunit->assertFalse($reg->channelExists('test.test.test'), 'make sure reg says test.test.test is gone 1');

$reg->addChannel($ch);

$ret = $reg->deleteChannel($ch);
$phpunit->assertTrue($ret, 'test.test.test 2');
$phpunit->assertFileNotExists($statedir . DIRECTORY_SEPARATOR . '.channels' . DIRECTORY_SEPARATOR .
    'test.test.test.reg', 'test delete test.test.test.reg');
$phpunit->assertFalse($reg->channelExists('test.test.test'), 'make sure reg says test.test.test is gone 2');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
