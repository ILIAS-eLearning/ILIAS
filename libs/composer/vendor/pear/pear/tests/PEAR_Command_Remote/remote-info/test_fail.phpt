--TEST--
remote-info command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pearweb->addRESTConfig('http://pear.php.net/rest/p/boog/info.xml', false, false);

$e = $command->run('remote-info', array(), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'remote-info expects one param: the remote package name'),
), 'wrong params');

$e = $command->run('remote-info', array(), array('smoog/boog'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'unknown channel "smoog" in "smoog/boog"'),
    array('package' => 'PEAR_Error', 'message' => 'Invalid package name "smoog/boog"'),
), 'unknown channel');

$e = $command->run('remote-info', array(), array('boog'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Unknown package: "boog" in channel "pear.php.net"
Debug: File http://pear.php.net:80/rest/p/boog/info.xml not valid (received: HTTP/1.1 404 http://pear.php.net/rest/p/boog/info.xml Is not valid)'),
    array('package' => 'PEAR_Error', 'message' => 'Unknown package: "boog" in channel "pear.php.net"
Debug: File http://pear.php.net:80/rest/p/boog/info.xml not valid (received: HTTP/1.1 404 http://pear.php.net/rest/p/boog/info.xml Is not valid)'),
), 'boog');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
