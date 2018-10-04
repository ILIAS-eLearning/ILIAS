--TEST--
PEAR_ErrorStack test context, in-static method eval, staticPush
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
class test3
{
    function test34()
    {
        global $testline, $stack;
        eval('PEAR_ErrorStack::staticPush("test", 3);');
        $testline = __LINE__ - 1;
    }
}
test3::test34();

$ret = $stack->pop();
$phpunit->assertEquals(array('file' => __FILE__,
      'line' => $testline,
      'function' => 'test34',
      'class' => 'test3'), $ret['context'], 'context');
echo 'tests done';
?>
--EXPECT--
tests done
