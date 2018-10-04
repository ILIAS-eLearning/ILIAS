--TEST--
PEAR_ErrorStack->getErrorMessage() normal errors
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
$stack->push(1);
$stack->push(2, 'warning');
$stack->push(3, 'foo');
$ret = $stack->getErrors();
for ($i= 0; $i < 3; $i++) {
    unset($ret[$i]['time']);
    unset($ret[$i]['context']);
}
$phpunit->assertEquals(
    array(
        array('code' => 3,
        'params' => array(),
        'package' => 'test',
        'level' => 'foo',
        'message' => ''),
        array('code' => 2,
        'params' => array(),
        'package' => 'test',
        'level' => 'warning',
        'message' => ''),
        array('code' => 1,
        'params' => array(),
        'package' => 'test',
        'level' => 'error',
        'message' => ''),
        ), $ret, 'incorrect errors, non-purge');
$ret = $stack->getErrors(true);
for ($i= 0; $i < 3; $i++) {
    unset($ret[$i]['time']);
    unset($ret[$i]['context']);
}
$phpunit->assertEquals(
    array(
        array('code' => 3,
        'params' => array(),
        'package' => 'test',
        'level' => 'foo',
        'message' => ''),
        array('code' => 2,
        'params' => array(),
        'package' => 'test',
        'level' => 'warning',
        'message' => ''),
        array('code' => 1,
        'params' => array(),
        'package' => 'test',
        'level' => 'error',
        'message' => ''),
        ), $ret, 'incorrect errors, purge');
$phpunit->assertEquals(array(), $stack->getErrors(), 'end');
echo 'tests done';
?>
--EXPECT--
tests done
