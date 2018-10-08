--TEST--
PEAR_ErrorStack::staticPop() basic test
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
$z1 = PEAR_ErrorStack::staticPush('test', 3, 'error', array('my' => 'param'), 'hello',
    array('test'), array(array('file' => 'boofr', 'line' => 35)));
$z2 = $stack->push(2, 'exception', array('my' => 'param'), 'hello',
    array('test'), array(array('file' => 'boof', 'line' => 34)));
$zz = $stack->getErrors();
$phpunit->assertEquals(2, count($zz), 'set up');
$err = PEAR_ErrorStack::staticPop('test');
$phpunit->assertEquals($z2, $err, 'popped different error 1');
$phpunit->assertEquals($zz[0], $err, 'popped different error 1.1');
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
$phpunit->assertEquals($z1, $err, 'popped different error 2');
$phpunit->assertEquals($zz[1], $err, 'popped different error 2.1');
unset($err['time']);
$phpunit->assertEquals(
    array(
        'code' => 3,
        'params' => array('my' => 'param'),
        'package' => 'test',
        'level' => 'error',
        'context' =>
            array(
                'file' => 'boofr',
                'line' => 35,
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
