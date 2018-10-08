--TEST--
PEAR_Command::getFrontendObject()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$err = PEAR_Command::setFrontendClass('fronk_oog_booger');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'no such class: fronk_oog_booger')
), 'invalid');
PEAR_Command::setFrontendClass('PEAR_Frontend_CLI');
$ok = &PEAR_Command::getFrontendObject();
$phpunit->assertIsa('PEAR_Frontend_CLI', $ok, 'ok');
$phpunit->assertIsa('PEAR_Error', $err, 'invalid');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
