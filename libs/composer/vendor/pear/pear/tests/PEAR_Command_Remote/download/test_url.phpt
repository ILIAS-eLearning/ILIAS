--TEST--
download command (url)
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
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'test-1.0.tgz';
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/test-1.0.tgz', $pathtopackagexml);
mkdir($temp_path . DIRECTORY_SEPARATOR . 'bloob');
chdir($temp_path . DIRECTORY_SEPARATOR . 'bloob');
$e = $command->run('download', array(), array('http://www.example.com/test-1.0.tgz'));
$phpunit->assertNoErrors('download');
$phpunit->assertEquals(array (
  array (
    0 => 3,
    1 => 'Downloading "http://www.example.com/test-1.0.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading test-1.0.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download test-1.0.tgz (785 bytes)',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '...done: 785 bytes',
  ),
  array (
    'info' => 'File ' . $temp_path . DIRECTORY_SEPARATOR . 'bloob' .
        DIRECTORY_SEPARATOR . 'test-1.0.tgz downloaded',
    'cmd' => 'download',
  ),
), $fakelog->getLog(), 'log');
$phpunit->showall();
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'setup',
    1 => 'self',
  ),
  1 => 
  array (
    0 => 'saveas',
    1 => 'test-1.0.tgz',
  ),
  2 => 
  array (
    0 => 'start',
    1 => 
    array (
      0 => 'test-1.0.tgz',
      1 => '785',
    ),
  ),
  3 => 
  array (
    0 => 'bytesread',
    1 => 785,
  ),
  4 => 
  array (
    0 => 'done',
    1 => 785,
  ),
), $fakelog->getDownload(), 'download log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
