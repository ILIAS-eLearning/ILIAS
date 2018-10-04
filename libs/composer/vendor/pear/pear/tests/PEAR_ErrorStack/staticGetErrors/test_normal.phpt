--TEST--
PEAR_ErrorStack::staticGetErrors(), normal usage
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
$stack->push(3, 'foo');
$ret = PEAR_ErrorStack::staticGetErrors();
for ($i= 0; $i < 3; $i++) {
    unset($ret['test'][$i]['time']);
    unset($ret['test'][$i]['context']);
}
$phpunit->assertEquals(
    array( 'test' => array(
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
        )), $ret, 'incorrect errors, non-purge');
$ret = PEAR_ErrorStack::staticGetErrors(true);
for ($i= 0; $i < 3; $i++) {
    unset($ret['test'][$i]['time']);
    unset($ret['test'][$i]['context']);
}
$phpunit->assertEquals(
    array( 'test' => array(
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
        )), $ret, 'incorrect errors, purge');
$phpunit->assertEquals(array(), PEAR_ErrorStack::staticGetErrors(), 2);
echo 'tests done';
?>
--EXPECT--
tests done
