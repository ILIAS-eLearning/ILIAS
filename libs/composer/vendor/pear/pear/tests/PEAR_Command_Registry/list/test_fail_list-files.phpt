--TEST--
list command, pseudo-list-files, failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('list', array(), array('gronk'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => '`gronk\' not installed'),
), 'unknown layer');
$e = $command->run('list', array(), array('gronk/php_dir'));
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
