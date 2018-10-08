--TEST--
PEAR_ErrorStack->getErrorMessage() normal usage with code
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$stack->setErrorMessageTemplate(array(23 => '%foo% has %__msg%'));
$phpunit->assertEquals('%foo% has %__msg%', $stack->getErrorMessageTemplate(23), 'test');
echo 'tests done';
?>
--EXPECT--
tests done
