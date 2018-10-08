--TEST--
PEAR_ErrorStack->getErrorMessage() params substitution with template
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$stack->setErrorMessageTemplate(array(6 => '%bar% foo'));
$msg = PEAR_ErrorStack::getErrorMessage($stack, array('message' => '',
    'params' => array('bar' => 'hello'), 'code' => 6));
$phpunit->assertEquals('hello foo', $msg, 'string');
$msg = PEAR_ErrorStack::getErrorMessage($stack, array('message' => '',
    'params' => array('bar' => 'hello'), 'code' => 7));
$phpunit->assertEquals('', $msg, 'string');

echo 'tests done';
?>
--EXPECT--
tests done
