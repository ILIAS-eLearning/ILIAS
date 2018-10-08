--TEST--
logout command
--SKIPIF--
skip
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$fakelog->setDialogOutput(array('login',
            array('Username', 'Password'),
            array('text',     'password'),
            array(@$_ENV['USER'],  '')), array('cellog', 'hi'));
$pearweb->addXmlrpcConfig("pear.php.net", "logintest",  null, true);
$command->run('login', array(), array());
$phpunit->assertNoErrors('test');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Logging in to pear.php.net.',
    'cmd' => 'login',
  ),
  1 =>
  array (
    'info' => 'Logged in.',
    'cmd' => 'login',
  ),
), $fakelog->getLog(), 'log');
$phpunit->assertEquals('cellog', $config->get('username'), 'username');
$phpunit->assertEquals('hi', $config->get('password'), 'password');
$command->run('logout', array(), array());
$phpunit->assertNoErrors('test');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Logging out from pear.php.net.',
    'cmd' => 'logout',
  ),
), $fakelog->getLog(), 'log');
$phpunit->assertEquals('', $config->get('username'), 'username');
$phpunit->assertEquals('', $config->get('password'), 'password');
echo 'tests done';
?>
--EXPECT--
tests done
