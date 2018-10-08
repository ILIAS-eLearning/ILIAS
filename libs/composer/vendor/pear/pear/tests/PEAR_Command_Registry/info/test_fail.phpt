--TEST--
info command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('info', array(), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'pear info expects 1 parameter'),
), 'no params');
$e = $command->run('info', array(), array('default_channel', 'user'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'pear info expects 1 parameter'),
), '1 params');
touch($temp_path . DIRECTORY_SEPARATOR . 'smong.xml');
$e = $command->run('info', array(), array($temp_path . DIRECTORY_SEPARATOR . 'smong.xml'));
if (version_compare(phpversion(), '5.0.0', '>=')) {
    if (version_compare(phpversion(), '5.0.3', '>=')) {
        $err = 'Invalid document end';
    } else {
        $err = 'XML_ERR_DOCUMENT_END';
    }
} else {
    $err = 'no element found';
}
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile', 'message' => 'package.xml "' . $temp_path . DIRECTORY_SEPARATOR . 'smong.xml" has no package.xml <package> version'),
    array('package' => 'PEAR_Error', 'message' => "XML error: $err at line 1"),
), 'invalid file');
$e = $command->run('info', array(), array('gronk/php_dir'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'unknown channel "gronk" in "gronk/php_dir"'),
    array('package' => 'PEAR_Error', 'message' => 'unknown channel "gronk" in "gronk/php_dir"'),
), 'unknown channel as option');
$e = $command->run('info', array(), array('__uri/php_dir'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'No information found for `__uri/php_dir\''),
), 'unknown package');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
