--TEST--
PEAR_ErrorStack::staticPush() message callback set in singleton
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
function messagecallback(&$stack, $err)
{
    global $wasCalled, $phpunit;
    $phpunit->assertEquals(4, $err['code'], 'wrong message code');
    $phpunit->assertEquals(array('hello' => 6), $err['params'], 'wrong message params');
    $phpunit->assertEquals('test2', $err['package'], 'wrong error stack');
    $wasCalled = true;
    return 'my silly message';
}
$stack = &PEAR_ErrorStack::singleton('test2', 'messagecallback');
$wasCalled = false;
PEAR_ErrorStack::staticPush('test2', 4, 'error', array('hello' => 6));
$phpunit->assertTrue($wasCalled, 'message callback was not called!');
$err = $stack->pop();
unset($err['time']);
unset($err['context']);
$phpunit->assertEquals(
    array(
        'code' => 4,
        'params' => array('hello' => 6),
        'package' => 'test2',
        'level' => 'error',
        'message' => 'my silly message',
    ),
    $err, 'popped something else'
);
$err = PEAR_ErrorStack::staticPop('test2');
$phpunit->assertNull($err, 'stack not empty!');
echo 'tests done';
?>
--EXPECT--
tests done
