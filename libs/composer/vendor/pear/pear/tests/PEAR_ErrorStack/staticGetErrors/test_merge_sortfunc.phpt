--TEST--
PEAR_ErrorStack::staticGetErrors(), merge usage with custom sorting function
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

function _sortErrorsRev($a, $b)
{
    global $wasCalled;
    $wasCalled = true;
    if ($a['time'] == $b['time']) {
        return 0;
    }
    if ($a['time'] < $b['time']) {
        return -1;
    }
    return 1;
}
$stack = &PEAR_ErrorStack::singleton('test');
$phpunit->assertEquals(array(), PEAR_ErrorStack::staticGetErrors(), 1);
$stack->push(1);
$stack->push(2, 'warning');
PEAR_ErrorStack::staticPush('fronk', 3, 'foo');
$wasCalled = false;
$ret = PEAR_ErrorStack::staticGetErrors(true, false, true, '_sortErrorsRev');
$phpunit->assertTrue($wasCalled, 'sortfunc was not called!');
for ($i= 0; $i < 3; $i++) {
    unset($ret[$i]['time']);
    unset($ret[$i]['context']);
}
$phpunit->assertEquals(
    array(
        array('code' => 1,
        'params' => array(),
        'package' => 'test',
        'level' => 'error',
        'message' => ''),
        array('code' => 2,
        'params' => array(),
        'package' => 'test',
        'level' => 'warning',
        'message' => ''),
        array('code' => 3,
        'params' => array(),
        'package' => 'fronk',
        'level' => 'foo',
        'message' => ''),
        ), $ret, 'incorrect errors, non-purge');
$test = PEAR_ErrorStack::staticGetErrors();
$phpunit->assertEquals(array(), $test, 'normal array');
echo 'tests done';
?>
--EXPECT--
tests done
