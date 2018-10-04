--TEST--
PEAR_ErrorStack->getErrorMessage() no errors
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$phpunit->assertEquals(array(), $stack->getErrors(), 1);
$phpunit->assertEquals(array(), $stack->getErrors(true), 2);
echo 'tests done';
?>
--EXPECT--
tests done
