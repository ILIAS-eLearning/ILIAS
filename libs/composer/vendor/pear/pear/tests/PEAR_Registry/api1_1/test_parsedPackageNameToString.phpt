--TEST--
PEAR_Registry->parsedPackageNameToString() (API v1.1)
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
$phpunit->assertEquals('channel://grob/foo', $reg->parsedPackageNameToString(
array('channel' => 'grob', 'package' => 'foo')), 'grob/foo');
$phpunit->assertEquals('channel://grob/foo-1.2', $reg->parsedPackageNameToString(
array('channel' => 'grob', 'package' => 'foo', 'version' => '1.2')), 'grob/foo-1.2');
$phpunit->assertEquals('channel://grob/foo-alpha', $reg->parsedPackageNameToString(
array('channel' => 'grob', 'package' => 'foo', 'state' => 'alpha')), 'grob/foo-alpha');
$phpunit->assertEquals('channel://grob/foo-alpha.tgz', $reg->parsedPackageNameToString(
array('channel' => 'grob', 'package' => 'foo', 'state' => 'alpha', 'extension' => 'tgz')),
'grob/foo-alpha.tgz');
$phpunit->assertEquals('channel://user@grob/foo-alpha.tgz', $reg->parsedPackageNameToString(
array('channel' => 'grob', 'package' => 'foo', 'state' => 'alpha', 'extension' => 'tgz', 'user' =>
'user')),
'user@grob/foo-alpha.tgz');
$phpunit->assertEquals('channel://user:pass@grob/foo-alpha.tgz', $reg->parsedPackageNameToString(
array('channel' => 'grob', 'package' => 'foo', 'state' => 'alpha', 'extension' => 'tgz', 'user' =>
'user', 'pass' => 'pass')),
'user:pass@grob/foo-alpha.tgz');
$phpunit->assertEquals('channel://user:pass@grob/foo-alpha.tgz?arg1=poo', $reg->parsedPackageNameToString(
array('channel' => 'grob', 'package' => 'foo', 'state' => 'alpha', 'extension' => 'tgz', 'user' =>
'user', 'pass' => 'pass', 'opts' => array('arg1' => 'poo'))),
'user:pass@grob/foo-alpha.tgz?arg1=poo');
$phpunit->assertEquals('channel://user:pass@grob/foo-alpha.tgz?arg1=poo&arg2=wow#group',
    $reg->parsedPackageNameToString(
array('channel' => 'grob', 'package' => 'foo', 'state' => 'alpha', 'extension' => 'tgz', 'user' =>
'user', 'pass' => 'pass', 'opts' => array('arg1' => 'poo', 'arg2' => 'wow'), 'group' => 'group')),
'user:pass@grob/foo-alpha.tgz?arg1=poo&arg2=wow#group');
$phpunit->assertEquals('channel://user:pass@grob/sub/foo-alpha.tgz?arg1=poo&arg2=wow#group',
    $reg->parsedPackageNameToString(
array('channel' => 'grob/sub', 'package' => 'foo', 'state' => 'alpha', 'extension' => 'tgz', 'user' =>
'user', 'pass' => 'pass', 'opts' => array('arg1' => 'poo', 'arg2' => 'wow'), 'group' => 'group')),
'user:pass@grob/sub/foo-alpha.tgz?arg1=poo&arg2=wow#group');

$phpunit->assertEquals('http://pear.php.net/Glom.tgz',
    $reg->parsedPackageNameToString(array('uri' => 'http://pear.php.net/Glom.tgz',
    'channel' => '__uri',
    'package' => 'Glom', 'extension' => 'tgz')), 'uri test');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
