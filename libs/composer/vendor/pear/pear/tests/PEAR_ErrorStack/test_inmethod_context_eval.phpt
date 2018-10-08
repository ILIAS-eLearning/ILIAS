--TEST--
PEAR_ErrorStack test context, in-method eval
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
class test4
{
    function test4()
    {
        global $testline, $stack;
        eval('$stack->push(3);');
        $testline = __LINE__ - 1;
    }
}
$z = new test4;
$z->test4();

$ret = $stack->pop();
$phpunit->assertEquals(array('file' => __FILE__,
      'line' => $testline,
      'function' => 'test4',
      'class' => 'test4'), $ret['context'], 'context');
echo 'tests done';
?>
--EXPECT--
tests done
