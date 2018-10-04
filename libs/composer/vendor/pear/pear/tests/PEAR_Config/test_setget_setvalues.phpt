--TEST--
PEAR_Config->set() and PEAR_Config->get()
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
// failures
$phpunit->assertFalse($config->set('preferred_state', 'oops'), 'unknown set value');
// successes
$phpunit->assertTrue($config->set('preferred_state', 'devel'), 'set devel');
$phpunit->assertEquals('devel', $config->get('preferred_state'), 'get set value');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
