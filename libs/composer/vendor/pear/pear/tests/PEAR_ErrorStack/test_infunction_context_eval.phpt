--TEST--
PEAR_ErrorStack test context, in-function eval
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
function test2()
{
    global $testline, $stack;
    eval('$stack->push(3);');
    $testline = __LINE__ - 1;
}
test2();

$ret = $stack->pop();
$phpunit->assertEquals(array('file' => __FILE__,
      'line' => $testline,
      'function' => 'test2',), $ret['context'], 'context');
echo 'tests done';
?>
--EXPECT--
tests done
