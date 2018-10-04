--TEST--
PEAR_Registry->isAlias (API v1.1)
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

$ret = $reg->isAlias('snark');
$phpunit->assertFalse($ret, 'snark');
$ret = $reg->isAlias('foo');
$phpunit->assertTrue($ret, 'foo');
$ret = $reg->isAlias('test.test.test');
$phpunit->assertFalse($ret, 'test.test.test');
// test for change of alias
$ch->setAlias('bar');
$reg->updateChannel($ch);
$phpunit->assertFalse($reg->isAlias('foo'), 'foo alias after');
$phpunit->assertTrue($reg->isAlias('bar'), 'bar alias after');
// test change of alias to alias that is in use
$ch->setAlias('pear');
$reg->updateChannel($ch);
$phpunit->assertFalse($reg->isAlias('foo'), 'foo alias after pear');
$phpunit->assertFalse($reg->isAlias('bar'), 'bar alias after pear');
$phpunit->assertTrue($reg->isAlias('test.test.test'), 'test.test.test alias after pear');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
