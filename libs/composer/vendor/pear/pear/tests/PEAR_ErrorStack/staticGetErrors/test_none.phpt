--TEST--
PEAR_ErrorStack::staticGetErrors(), no errors
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$phpunit->assertEquals(array(), PEAR_ErrorStack::staticGetErrors(), 1);
$phpunit->assertEquals(array(), PEAR_ErrorStack::staticGetErrors(true), 2);
echo 'tests done';
?>
--EXPECT--
tests done
