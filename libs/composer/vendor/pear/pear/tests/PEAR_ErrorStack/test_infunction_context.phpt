--TEST--
PEAR_ErrorStack test context, basic in-function file/line
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
function testfunc()
{
    global $stack, $testline;
    $stack->push(3);
    $testline = __LINE__ - 1;
}

testfunc();

$ret = $stack->pop();
$phpunit->assertEquals(array('file' => __FILE__,
      'line' => $testline,
      'function' => 'testfunc'), $ret['context'], 'context');
echo 'tests done';
?>
--EXPECT--
tests done
