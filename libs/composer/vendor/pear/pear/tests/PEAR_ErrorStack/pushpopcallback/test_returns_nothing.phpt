--TEST--
PEAR_ErrorStack callback, returns PEAR_ERRORSTACK_IGNORE
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
function returnsnothing($err)
{
    global $wasCalled;
    $wasCalled = true;
}
$stack->pushCallback('returnsnothing');
$wasCalled = false;
$stack->push(1);
$phpunit->assertTrue($wasCalled, 'returnsnothing not called');
$err = $stack->pop();
unset($err['context']);
unset($err['time']);
$phpunit->assertEquals(
    array(
        'code' => 1,
        'params' => array(),
        'package' => 'test',
        'level' => 'error',
        'message' => '',
    ),
    $err, 'popped something else'
);
$stack->popCallback();
$wasCalled = false;
$stack->push(1);
$phpunit->assertFalse($wasCalled, 'returnsnothing called');
$err = $stack->pop();
unset($err['context']);
unset($err['time']);
$phpunit->assertEquals(
    array(
        'code' => 1,
        'params' => array(),
        'package' => 'test',
        'level' => 'error',
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
