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
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net'),
 $reg->parsePackageName('hello'), 'hello');
$ret = $reg->parsePackageName('hello', 'snooker');
$phpunit->assertErrors(array('package' => 'PEAR_Error', 'message' =>
'unknown channel "snooker" in "hello"'), 'snooker');

$ch = new PEAR_ChannelFile;
$ch->setName('snooker');
$ch->setAlias('foo');
$ch->setServer('snooker');
$ch->setSummary('blah');
$ch->setDefaultPEARProtocols();
$phpunit->assertTrue($reg->addChannel($ch), 'add snooker');
$ch->setName('snooker/test');
$phpunit->assertTrue($reg->addChannel($ch), 'add snooker/test');

$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'snooker'),
 $reg->parsePackageName('hello', 'snooker'), 'hello, default snooker');

// test complex channel names
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'snooker/test'),
 $reg->parsePackageName('snooker/test/hello'), 'snooker/test/hello');
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'snooker/test'),
 $reg->parsePackageName('channel://snooker/test/hello'), 'channel://snooker/test/hello');
 
// test states
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'state' => 'snapshot'),
 $reg->parsePackageName('hello-snapshot'), 'hello-snapshot');
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'state' => 'devel'),
 $reg->parsePackageName('hello-devel'), 'hello-devel');
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'state' => 'alpha'),
 $reg->parsePackageName('hello-alpha'), 'hello-alpha');
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'state' => 'beta'),
 $reg->parsePackageName('hello-beta'), 'hello-beta');
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'state' => 'stable'),
 $reg->parsePackageName('hello-stable'), 'hello-stable');
// test versions
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'version' => '0.1devel'),
 $reg->parsePackageName('hello-0.1devel'), 'hello-0.1devel');
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'version' => '1.0.0alpha1'),
 $reg->parsePackageName('hello-1.0.0alpha1'), 'hello-1.0.0alpha1');
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'version' => '1.2.3'),
 $reg->parsePackageName('hello-1.2.3'), 'hello-1.2.3');
// test extensions
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'extension' => 'tgz', 'version' => '1.0.0alpha1'),
 $reg->parsePackageName('hello-1.0.0alpha1.tgz'), 'hello-1.0.0alpha1.tgz');
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'extension' => 'tar'),
 $reg->parsePackageName('hello.tar'), 'hello.tar');
// test uri
$phpunit->assertEquals(array('uri' => 'http://pear.php.net/Glom.tgz', 'channel' => '__uri',
    'package' => 'Glom', 'extension' => 'tgz'),
 $reg->parsePackageName('http://pear.php.net/Glom.tgz'), 'http://pear.php.net/Glom.tgz');

// test group
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'group' => 'boop'),
 $reg->parsePackageName('channel://pear.php.net/hello#boop'), 'channel://pear.php.net/hello#boop');

// test user and user/pass
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'group' => 'boop', 'user' => 'user'),
 $reg->parsePackageName('channel://user@pear.php.net/hello#boop'), 'channel://user@pear.php.net/hello#boop');
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
     'group' => 'boop', 'user' => 'user', 'pass' => 'pass'),
 $reg->parsePackageName('channel://user:pass@pear.php.net/hello#boop'), 'channel://user:pass@pear.php.net/hello#boop');

// test opts
$phpunit->assertEquals(array('package' => 'hello', 'channel' => 'pear.php.net',
    'group' => 'boop', 'user' => 'user', 'opts' => array('frong' => 'gloop', 'blong' => 'foop group')),
 $reg->parsePackageName('channel://user@pear.php.net/hello?frong=gloop&blong=foop+group#boop'), 'channel://user@pear.php.net/hello?frong=gloop&blong=foop+group#boop');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
