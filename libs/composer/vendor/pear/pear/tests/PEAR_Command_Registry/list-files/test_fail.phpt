--TEST--
list-files command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('list-files', array(), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'list-files expects 1 parameter'),
), 'no params');
$e = $command->run('list-files', array(), array('default_channel', 'user'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'list-files expects 1 parameter'),
), '1 params');
$e = $command->run('list-files', array(), array('gronk'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => '`gronk\' not installed'),
), 'unknown layer');
$e = $command->run('list-files', array(), array('gronk/php_dir'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'unknown channel "gronk" in "gronk/php_dir"'),
), 'unknown channel as option');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
