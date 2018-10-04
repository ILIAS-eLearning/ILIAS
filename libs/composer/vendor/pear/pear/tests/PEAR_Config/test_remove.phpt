--TEST--
PEAR_Config->remove()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$config = new PEAR_Config($temp_path . DIRECTORY_SEPARATOR . 'pear.ini', $temp_path .
    DIRECTORY_SEPARATOR . 'nofile');
$phpunit->assertTrue($config->set('umask', '123'), 'umask set');
$phpunit->assertEquals('123', $config->get('umask'), 'after set');
$phpunit->assertFalse($config->remove('foo'), 'foo');
$phpunit->assertTrue($config->remove('umask'), 'umask');
$phpunit->assertEquals(PEAR_CONFIG_DEFAULT_UMASK, $config->get('umask'), 'after remove');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
