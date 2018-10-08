--TEST--
PEAR_Command::setFrontendClass()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$err = &PEAR_Command::setFrontendClass('foo_gobrmlble');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'no such class: foo_gobrmlble')
), 'invalid');
class invalid_frontend {
    function noUserConfirm()
    {
    }
}
$err = &PEAR_Command::setFrontendClass('invalid_frontend');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'not a frontend class: invalid_frontend')
), 'not a frontend');
$ok = &PEAR_Command::setFrontendClass('PEAR_Frontend_CLI');
$phpunit->assertIsa('PEAR_Frontend_CLI', $ok, 'ok');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
