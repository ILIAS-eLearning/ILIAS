--TEST--
PEAR_ErrorStack test context, in-static method create_function(), staticPush
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
$a = create_function('', 'PEAR_ErrorStack::staticPush("test", 3);');
$testline = __LINE__ - 1;
class test8
{
    function test7()
    {
        global $a;
        $a();
    }
}
test8::test7();

$ret = $stack->pop();
$phpunit->assertEquals(array('file' => __FILE__,
      'line' => $testline,
      'function' => 'create_function() code',
), $ret['context'], 'context');
echo 'tests done';
?>
--EXPECT--
tests done
