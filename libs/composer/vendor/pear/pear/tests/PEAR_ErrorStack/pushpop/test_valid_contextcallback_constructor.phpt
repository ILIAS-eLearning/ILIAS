--TEST--
PEAR_ErrorStack->push() context callback set in constructor
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
function contextcallback($code, $params, $trace)
{
    global $wasCalled, $phpunit;
    $phpunit->assertEquals(4, $code, 'wrong context code');
    $phpunit->assertEquals(array('hello' => 6), $params, 'wrong context params');
    $wasCalled = true;
    return array('hi' => 'there', 'you' => 'fool');
}
    
$stack = new PEAR_ErrorStack('test', false, 'contextcallback');
$wasCalled = false;
$stack->push(4, 'error', array('hello' => 6));
$phpunit->assertTrue($wasCalled, 'context callback was not called!');
$err = $stack->pop();
unset($err['time']);
$phpunit->assertEquals(
    array(
        'code' => 4,
        'params' => array('hello' => 6),
        'package' => 'test',
        'level' => 'error',
        'context' => array('hi' => 'there', 'you' => 'fool'),
        'message' => '',
    ),
    $err, 'popped something else'
);
$err = $stack->pop();
$phpunit->assertNull($err, 'stack not empty!');
echo 'tests done';
?>
--EXPECT--
tests done
