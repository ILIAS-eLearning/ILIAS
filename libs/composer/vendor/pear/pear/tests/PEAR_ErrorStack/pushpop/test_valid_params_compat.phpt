--TEST--
PEAR_ErrorStack->push() basic, PEAR_Error compatibility
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$stack = new PEAR_ErrorStack('test', false, false, true);
$z = $stack->push(2, 'exception', array('my' => 'param'), 'hello',
    array('test'), array(array('file' => 'boof', 'line' => 34)));
$phpunit->assertIsa('pear_error', $z, 'not pear_error');
$err = $stack->pop('exception');
$phpunit->assertEquals($err, $z->getUserInfo(), 'userinfo wrong');
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
$err = $stack->pop();
$phpunit->assertNull($err, 'stack not empty!');
echo 'tests done';
?>
--EXPECT--
tests done
