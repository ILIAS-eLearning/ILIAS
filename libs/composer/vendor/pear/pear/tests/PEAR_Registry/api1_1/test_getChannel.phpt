--TEST--
PEAR_Registry->getChannel() (API v1.1)
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

$ret = $reg->getChannel('snark');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Unknown channel: snark')
), 'after snark');
$phpunit->assertIsa('PEAR_Error', $ret, 'snark');
$ret = $reg->getChannel('foo', true);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Unknown channel: foo')
), 'after foo strict');
$phpunit->assertIsa('PEAR_Error', $ret, 'foo strict');
$ret = $reg->getChannel('foo');
$phpunit->assertIsa('PEAR_ChannelFile', $ret, 'class 1');
$phpunit->assertEquals('test.test.test', $ret->getName(), 'foo');
$ret = $reg->getChannel('test.test.test');
$phpunit->assertIsa('PEAR_ChannelFile', $ret, 'class 2');
$phpunit->assertEquals('test.test.test', $ret->getName(), 'test.test.test');
$ret = $reg->getChannel('test.test.test', true);
$phpunit->assertIsa('PEAR_ChannelFile', $ret, 'class 3');
$phpunit->assertEquals('test.test.test', $ret->getName(), 'test.test.test strict');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
