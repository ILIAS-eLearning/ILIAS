--TEST--
PEAR_ErrorStack->getErrorMessage() normal usage
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$phpunit->assertEquals('%__msg%', $stack->getErrorMessageTemplate(23), 'basic');
echo 'tests done';
?>
--EXPECT--
tests done
