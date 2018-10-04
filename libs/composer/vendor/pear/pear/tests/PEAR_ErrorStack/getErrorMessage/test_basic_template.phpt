--TEST--
PEAR_ErrorStack->getErrorMessage() basic, template
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$msg = PEAR_ErrorStack::getErrorMessage($stack,
    array('message' => 'boo', 'params' => array()), 'far%__msg%');
$phpunit->assertEquals('farboo', $msg, 'message');
echo 'tests done';
?>
--EXPECT--
tests done
