--TEST--
PEAR_Command::registerCommands() (non-standard)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
PEAR_Command::registerCommands(false, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fakecommands');
$phpunit->assertEquals(array (
  'login' => 'PEAR_Command_Grunk',
  'logout' => 'PEAR_Command_Grunk',
  'smong' => 'PEAR_Command_Foo',
  'yertl' => 'PEAR_Command_Foo',
), PEAR_Command::getCommands(), 'getcommands');
$phpunit->assertEquals(array (
  'li' => 'login',
  'lo' => 'logout',
  'sm' => 'smong',
  'ye' => 'yertl',
), PEAR_Command::getShortcuts(), 'getshortcuts');
PEAR_Command::getGetoptArgs('login', $s, $l);
$phpunit->assertEquals('', $s, 'short login'); 
$phpunit->assertEquals(array(), $l, 'long login'); 
PEAR_Command::getGetoptArgs('logout', $s, $l);
$phpunit->assertEquals('', $s, 'short logout'); 
$phpunit->assertEquals(array(), $l, 'long logout'); 
PEAR_Command::getGetoptArgs('smong', $s, $l);
$phpunit->assertEquals('c:', $s, 'short smong'); 
$phpunit->assertEquals(array('channel='), $l, 'long smong'); 
PEAR_Command::getGetoptArgs('yertl', $s, $l);
$phpunit->assertEquals('c', $s, 'short yertl'); 
$phpunit->assertEquals(array('channel'), $l, 'long yertl'); $phpunit->assertEquals('Connects and authenticates to remote server'
    , PEAR_Command::getDescription('login'), 'login');
$phpunit->assertEquals('Logs out from the remote server'
    , PEAR_Command::getDescription('logout'), 'logout');
$phpunit->assertEquals('Connects and authenticates to remote server'
    , PEAR_Command::getDescription('smong'), 'smong');
$phpunit->assertEquals('Logs out from the remote server'
    , PEAR_Command::getDescription('yertl'), 'yertl');

$phpunit->assertEquals(array (
  0 => '
Log in to the remote server.  To use remote functions in the installer
that require any kind of privileges, you need to log in first.  The
username and password you enter here will be stored in your per-user
PEAR configuration (~/.pearrc on Unix-like systems).  After logging
in, your username and password will be sent along in subsequent
operations on the remote server.',
  1 => NULL,
)
    , PEAR_Command::getHelp('login'), 'login');
$phpunit->assertEquals(array (
  0 => '
Logs out from the remote server.  This command does not actually
connect to the remote server, it only deletes the stored username and
password from your user configuration.',
  1 => NULL,
)
    , PEAR_Command::getHelp('logout'), 'logout');
$phpunit->assertEquals(array (
  0 => '
Log in to the remote server.  To use remote functions in the installer
that require any kind of privileges, you need to log in first.  The
username and password you enter here will be stored in your per-user
PEAR configuration (~/.pearrc on Unix-like systems).  After logging
in, your username and password will be sent along in subsequent
operations on the remote server.',
  1 => 'Options:
  -c CHAN, --channel=CHAN
        list installed packages from this channel
',
)
    , PEAR_Command::getHelp('smong'), 'smong');
$phpunit->assertEquals(array (
  0 => '
Logs out from the remote server.  This command does not actually
connect to the remote server, it only deletes the stored username and
password from your user configuration.',
  1 => 'Options:
  -c, --channel
        list installed packages from this channel
',
)
    , PEAR_Command::getHelp('yertl'), 'yertl');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
