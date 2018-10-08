--TEST--
PEAR_ErrorStack test context, in-static method staticPush
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
class stclass
{
    function staticfunc()
    {
        global $testline;
        PEAR_ErrorStack::staticPush('test', 3);
        $testline = __LINE__ - 1;
    }
}
stclass::staticfunc();

$ret = $stack->pop();
$phpunit->assertEquals(array('file' => __FILE__,
      'line' => $testline,
      'function' => 'staticfunc',
      'class' => 'stclass',
), $ret['context'], 'context');
echo 'tests done';
?>
--EXPECT--
tests done
