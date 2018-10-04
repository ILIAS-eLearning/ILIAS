--TEST--
PEAR_ErrorStack::staticHasErrors(), error level usage
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
PEAR_ErrorStack::staticPush('MyPackage', 1, 'warning');
$phpunit->assertTrue(PEAR_ErrorStack::staticHasErrors('MyPackage', 'warning'), '1');
$stack = &PEAR_ErrorStack::singleton('MyPackage');
$stack->pop();
$phpunit->assertFalse(PEAR_ErrorStack::staticHasErrors('MyPackage', 'warning'), '2');
echo 'tests done';
?>
--EXPECT--
tests done
