--TEST--
PEAR_ErrorStack::staticGetErrors(), merge usage
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$stack = &PEAR_ErrorStack::singleton('test');
$phpunit->assertEquals(array(), PEAR_ErrorStack::staticGetErrors(), 1);
$stack->push(1);
$stack->push(2, 'warning');
PEAR_ErrorStack::staticPush('fronk', 3, 'foo');
$ret = PEAR_ErrorStack::staticGetErrors(true, false, true);
for ($i= 0; $i < 3; $i++) {
    unset($ret[$i]['time']);
    unset($ret[$i]['context']);
}
$phpunit->assertEquals(
    array(
        array('code' => 3,
        'params' => array(),
        'package' => 'fronk',
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
$test = PEAR_ErrorStack::staticGetErrors();
$phpunit->assertEquals(array(), $test, 'normal array');
echo 'tests done';
?>
--EXPECT--
tests done
