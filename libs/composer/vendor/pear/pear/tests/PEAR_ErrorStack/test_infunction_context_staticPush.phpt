--TEST--
PEAR_ErrorStack test context, in-function staticPush
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
function staticfunc()
{
    global $testline;
    PEAR_ErrorStack::staticPush('test', 3);
    $testline = __LINE__ - 1;
}
staticfunc();

$ret = $stack->pop();
$phpunit->assertEquals(array('file' => __FILE__,
      'line' => $testline,
      'function' => 'staticfunc',
), $ret['context'], 'context');
echo 'tests done';
?>
--EXPECT--
tests done
