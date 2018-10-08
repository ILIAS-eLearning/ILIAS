--TEST--
PEAR_Registry v1.1
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
$ret = $reg->parsePackageName('hello', 'snooker');
$phpunit->assertErrors(array('package' => 'PEAR_Error', 'message' =>
'unknown channel "snooker" in "hello"'), 'snooker');
$ret = $reg->parsePackageName('snooker/hello');
$phpunit->assertErrors(array('package' => 'PEAR_Error', 'message' =>
'unknown channel "snooker" in "snooker/hello"'), 'snooker');

$ch = new PEAR_ChannelFile;
$ch->setName('snooker');
$ch->setAlias('foo');
$ch->setSummary('blah');
$ch->setDefaultPEARProtocols();
$reg->addChannel($ch);
$phpunit->assertNoErrors('setup');

$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'snooker'),
 $reg->parsePackageName('hello', 'snooker'), 'hello, default snooker');

$ret = $reg->parsePackageName('ftp://oops');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'parsePackageName(): only channel:// uris may be downloaded, not "ftp://oops"')
), 'ftp://oops');
$ret = $reg->parsePackageName('channel://');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'parsePackageName(): array $param must contain a valid package name in "channel://"')
), 'channel://');
$ret = $reg->parsePackageName('wrong-alpha-1.0');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'parsePackageName(): only one version/state delimiter "-" is allowed in "wrong-alpha-1.0"')
), 'wrong-alpha-1.0');
$ret = $reg->parsePackageName('!#@$&^%');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'parsePackageName(): invalid package name "!" in "!#@$&^%"')
), '!#@$&^%');
$ret = $reg->parsePackageName('Pretty#!@$&^%');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'parsePackageName(): dependency group "!@$&^%" is not a valid group name in "Pretty#!@$&^%"')
), 'Pretty#!@$&^%');
$ret = $reg->parsePackageName(array('channel' => 'pear.php.net', 'package' => 'Pretty', 'state' =>
    'glonk'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'parsePackageName(): state "glonk" is not a valid state in "channel://pear.php.net/Pretty-glonk"')
), 'bad state in array');
$ret = $reg->parsePackageName(array('channel' => 'pear.php.net', 'package' => 'Pretty', 'state' =>
    'alpha', 'version' => '1.8'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'parsePackageName(): cannot contain both a version and a stability (state) in "channel://pear.php.net/Pretty-1.8alpha"')
), 'both state and version in array');
$ret = $reg->parsePackageName('Pretty-5.(');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'parsePackageName(): "5.(" is neither a valid version nor a valid state in "Pretty-5.("')
), 'Pretty-5.(');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
