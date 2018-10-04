--TEST--
PEAR_ErrorStack::singleton(), invalid
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$one = &PEAR_ErrorStack::singleton('first');
$two = &PEAR_ErrorStack::singleton('second');
$two->testme = 2;
$phpunit->assertEquals(2, $two->testme, 'duh test');
$one->testme = 4;
$phpunit->assertEquals(4, $one->testme, 'duh test 2');
$phpunit->assertEquals(2, $two->testme, 'same object test');
echo 'tests done';
?>
--EXPECT--
tests done
