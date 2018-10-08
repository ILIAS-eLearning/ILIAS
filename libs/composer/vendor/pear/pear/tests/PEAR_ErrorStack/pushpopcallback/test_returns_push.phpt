--TEST--
PEAR_ErrorStack callback, returns PEAR_ERRORSTACK_PUSH
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
function returnspush($err)
{
    global $wasCalled;
    $wasCalled = true;
    return PEAR_ERRORSTACK_PUSH;
}
$stack->pushCallback('returnspush');
$log = new Burflog;
$a = array(&$log, 'log');
$stack->setLogger($a);
$wasCalled = $wasLogged = false;
$stack->push(1);
$phpunit->assertTrue($wasCalled, 'returnspush not called');
$phpunit->assertFalse($wasLogged, 'was logged');
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
$wasCalled = $wasLogged = false;
$stack->push(1);
$phpunit->assertFalse($wasCalled, 'returnspush called');
$phpunit->assertTrue($wasLogged, 'was not logged');
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
