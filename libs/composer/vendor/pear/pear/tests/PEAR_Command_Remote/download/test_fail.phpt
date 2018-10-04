--TEST--
download command failure
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
$e = $command->run('download', array(), array('http://www.example.com/bloo.tgz'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'download failed'),
), '404');
$phpunit->assertEquals( array (
  array (
    0 => 3,
    1 => 'Downloading "http://www.example.com/bloo.tgz"',
  ),
  array (
    0 => 0,
    1 => 'Could not download from "http://www.example.com/bloo.tgz" (File http://www.example.com:80/bloo.tgz not valid (received: HTTP/1.1 404 http://www.example.com/bloo.tgz Is not valid))',
  ),
  array (
    0 => 0,
    1 => 'Invalid or missing remote package file',
  ),
  array (
    0 => 2,
    1 => 'Package "http://www.example.com/bloo.tgz" is not valid',
  ),
 )
, $fakelog->getLog(), 'log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
