--TEST--
PEAR_Config->setChannels()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$config = new PEAR_Config($temp_path . DIRECTORY_SEPARATOR . 'fii', $temp_path . DIRECTORY_SEPARATOR . 'fii');
$config->removeLayer('user');
$phpunit->assertFalse($config->setChannels(1), 1);
$phpunit->assertTrue($config->setChannels(array('pear.php.net', '__uri', 'mychannel')), 'set');
$phpunit->assertEquals(array (
  '__channels' => 
  array (
    '__uri' => 
    array (
    ),
    'mychannel' => 
    array (
    ),
  ),
), $config->configuration['user'], 'raw test');
$phpunit->assertEquals(array (
  '__channels' => 
  array (
    '__uri' => 
    array (
    ),
    'mychannel' => 
    array (
    ),
  ),
), $config->configuration['system'], 'raw test');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
