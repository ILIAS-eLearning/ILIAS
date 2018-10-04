--TEST--
config-create command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('config-create', array(), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'config-create: must have 2 parameters, root path and filename to save as'),
), 'no params');
$e = $command->run('config-create', array(), array('hoo'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'config-create: must have 2 parameters, root path and filename to save as'),
), '1 params');
$e = $command->run('config-create', array(), array('default_channel', 'user', 'hoo'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'config-create: must have 2 parameters, root path and filename to save as'),
), '3 params');
$e = $command->run('config-create', array(), array('badroot', $temp_path . '/config.ini'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Root directory must be an absolute path beginning with "/", was: "badroot"'),
), 'bad root dir');
$e = $command->run('config-create', array(), array('C:\\badroot', $temp_path . '/config.ini'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Root directory must be an absolute path beginning with "/", was: "C:/badroot"'),
), 'C:\\badroot dir');
$e = $command->run('config-create', array('windows' => true), array('5:\\badroot', $temp_path . '/config.ini'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Root directory must be an absolute path beginning with "\\" or "C:\\", was: "5:/badroot"'),
), 'C:\\badroot dir');
$phpunit->assertFileNotExists($temp_path . '/config.ini', 'make sure no create');
if (OS_WINDOWS) {
    $e = $command->run('config-create', array(), array('/okroot', $temp_path . '/#\\##/'));
    $phpunit->assertErrors(array(
        array('package' => 'PEAR_Error', 'message' => 'Could not create "' . $temp_path . '/#\##/"'),
    ), 'bad file');
    $phpunit->assertFileNotExists($temp_path . '/#\\##/', 'make sure no create #\\##/');
} else {
    $e = $command->run('config-create', array(), array('/okroot', $temp_path . '/#\\##%$*/'));
    $phpunit->assertErrors(array(
        array('package' => 'PEAR_Error', 'message' => 'Could not create "' . $temp_path . '/#\\##%$*/"'),
    ), 'bad file');
    $phpunit->assertFileNotExists($temp_path . '/#\\##%$*/', 'make sure no create #\\##%$*/');
}
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
