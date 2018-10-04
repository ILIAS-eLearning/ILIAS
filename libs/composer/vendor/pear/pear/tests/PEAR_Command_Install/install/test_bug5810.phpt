--TEST--
install command, bug #5810 (internet should not be contacted on install if dependencies are installed)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);
$pearweb->_continue = true;
$_test_dep->setPEARVersion('1.4.3');
$_test_dep->setPHPVersion('5.0.0');
$_test_dep->setExtensions(array('gd' => 0));
$dir = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR;
$res = $command->run('install', array(), array($dir . 'Image_Color-1.0.2.tgz',
    $dir . 'Image_Canvas-0.2.2.tgz'));
$phpunit->assertNoErrors('setup');
$fakelog->getLog();
$res = $command->run('install', array(), array($dir . 'Image_Canvas-0.2.2.tgz'));
$phpunit->assertEquals(array(
  0 =>
  array (
    'info' => 'Ignoring installed package pear/Image_Canvas',
    'cmd' => 'no command',
  ),
  1 =>
  array (
    'info' => 'Nothing to install',
    'cmd' => 'no command',
  ),
), $fakelog->getLog(), 'log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
