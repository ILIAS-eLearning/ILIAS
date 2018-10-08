--TEST--
PEAR_Registry->channelExists() (API v1.1)
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

$ret = $reg->channelExists('snark');
$phpunit->assertFalse($ret, 'snark');
$ret = $reg->channelExists('foo');
$phpunit->assertTrue($ret, 'foo');
$ret = $reg->channelExists('foo', true);
$phpunit->assertFalse($ret, 'foo strict');
$ret = $reg->channelExists('test.test.test');
$phpunit->assertTrue($ret, 'test.test.test');
$ret = $reg->channelExists('test.test.test', true);
$phpunit->assertTrue($ret, 'test.test.test strict');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
