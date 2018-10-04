--TEST--
PEAR_ErrorStack->push() basic
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$stack->push(1);
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
