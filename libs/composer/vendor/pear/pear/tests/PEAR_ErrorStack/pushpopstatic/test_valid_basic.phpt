--TEST--
PEAR_ErrorStack::staticPush(), basic
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
PEAR_ErrorStack::staticPush('test', 1);
$stack = &PEAR_ErrorStack::singleton('test');
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
$err = PEAR_ErrorStack::staticPop('test');
$phpunit->assertNull($err, 'stack not empty!');
echo 'tests done';
?>
--EXPECT--
tests done
