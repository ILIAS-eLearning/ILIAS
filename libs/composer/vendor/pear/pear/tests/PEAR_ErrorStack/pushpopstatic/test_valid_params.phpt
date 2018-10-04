--TEST--
PEAR_ErrorStack->getErrorMessage() basic, with params
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
$z = PEAR_ErrorStack::staticPush('test', 2, 'exception', array('my' => 'param'), 'hello',
    array('test'), array(array('file' => 'boof', 'line' => 34)));
$err = $stack->pop('exception');
$phpunit->assertEquals($z, $err, 'popped different error');
unset($err['time']);
$phpunit->assertEquals(
    array(
        'code' => 2,
        'params' => array('my' => 'param'),
        'package' => 'test',
        'level' => 'exception',
        'context' =>
            array(
                'file' => 'boof',
                'line' => 34,
            ),
        'message' => 'hello',
        'repackage' => array('test'),
    ),
    $err, 'popped something else'
);
$err = PEAR_ErrorStack::staticPop('test');
$phpunit->assertNull($err, 'stack not empty!');
echo 'tests done';
?>
--EXPECT--
tests done
