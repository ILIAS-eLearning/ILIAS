--TEST--
PEAR_ErrorStack->getErrorMessage() params substitution
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$msg = PEAR_ErrorStack::getErrorMessage($stack, array('message' => '',
    'params' => array('bar' => 'hello')), '%bar% foo');
$phpunit->assertEquals('hello foo', $msg, 'string');
$msg = PEAR_ErrorStack::getErrorMessage($stack, array('message' => '',
    'params' => array('bar' => array('hello', 'there'))), '%bar% foo');
$phpunit->assertEquals('hello, there foo', $msg, 'array');
$msg = PEAR_ErrorStack::getErrorMessage($stack, array('message' => '',
    'params' => array('bar' => new testgemessage)), '%bar% foo');
$phpunit->assertEquals('__toString() called foo', $msg, 'first object, __toString()');
$msg = PEAR_ErrorStack::getErrorMessage($stack, array('message' => '',
    'params' => array('bar' => new testgemessage1)), '%bar% foo');
$phpunit->assertEquals('Object foo', $msg, 'second object, no __toString()');
$errs = PEAR_ErrorStack::staticGetErrors();
unset($errs['PEAR_ErrorStack'][0]['time']);
unset($errs['PEAR_ErrorStack'][0]['context']['file']);
unset($errs['PEAR_ErrorStack'][0]['context']['line']);
if (version_compare(phpversion(), '5.0.0', '<')) {
    $phpunit->assertEquals(
    array('PEAR_ErrorStack' =>
        array(
            array(
                'code' => PEAR_ERRORSTACK_ERR_OBJTOSTRING,
                'params' => array('obj' => 'testgemessage1'),
                'package' => 'PEAR_ErrorStack',
                'level' => 'warning',
                'context' =>
                array (
                    'function' => 'geterrormessage',
                    'class' => 'pear_errorstack',
                ),
                'message' => 'object testgemessage1 passed into getErrorMessage, but has no __toString() method',
            )
        ),
    ), $errs, 'warning not raised');
} else {
    $phpunit->assertEquals(
    array('PEAR_ErrorStack' =>
        array(
            array(
                'code' => PEAR_ERRORSTACK_ERR_OBJTOSTRING,
                'params' => array('obj' => 'testgemessage1'),
                'package' => 'PEAR_ErrorStack',
                'level' => 'warning',
                'context' =>
                array (
                    'function' => 'getErrorMessage',
                    'class' => 'PEAR_ErrorStack',
                ),
                'message' => 'object testgemessage1 passed into getErrorMessage, but has no __toString() method',
            )
        ),
    ), $errs, 'warning not raised');
}
echo 'tests done';
?>
--EXPECT--
tests done
