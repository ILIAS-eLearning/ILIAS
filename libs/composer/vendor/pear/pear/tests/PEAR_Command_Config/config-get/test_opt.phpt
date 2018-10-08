--TEST--
config-get command with --channel option
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$config->set('verbose', 34, 'user');
$config->set('verbose', 45, 'system');
$config->set('verbose', 56, 'user', '__uri');
$config->set('verbose', 67, 'system', '__uri');
$e = $command->run('config-get', array('channel' => '__uri'), array('verbose'));
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 56,
    'cmd' => 'config-get',
  ),
), $fakelog->getLog(), 'verbose');
$e = $command->run('config-get', array('channel' => '__uri'), array('verbose', 'user'));
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 56,
    'cmd' => 'config-get',
  ),
), $fakelog->getLog(), 'verbose user');
$e = $command->run('config-get', array('channel' => '__uri'), array('verbose', 'system'));
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 67,
    'cmd' => 'config-get',
  ),
), $fakelog->getLog(), 'verbose system');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
