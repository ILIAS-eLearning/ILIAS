--TEST--
channel-alias command
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
$phpunit->assertFalse($reg->isAlias('foo'), 'test alias before');
$e = $command->run('channel-alias', array(), array('pear.php.net', 'foo'));
$phpunit->assertNoErrors('after');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Channel "pear.php.net" aliased successfully to "foo"',
    'cmd' => 'no command', 
  ),
), $fakelog->getLog(), 'log');
$phpunit->assertTrue($reg->isAlias('foo'), 'test alias foo after');
$phpunit->assertFalse($reg->isAlias('pear'), 'test alias pear after');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
