--TEST--
PEAR_ErrorStack test context, in-function create_function()
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
$a = create_function('', '$GLOBALS["stack"]->push(3);');
$testline = __LINE__ - 1;
function test7()
{
    global $a;
    $a();
}
test7();

$ret = $stack->pop();
$phpunit->assertEquals(array('file' => __FILE__,
      'line' => $testline,
      'function' => 'create_function() code',
), $ret['context'], 'context');
echo 'tests done';
?>
--EXPECT--
tests done
