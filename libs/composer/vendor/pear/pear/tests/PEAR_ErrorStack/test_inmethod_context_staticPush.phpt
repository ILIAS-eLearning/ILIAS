--TEST--
PEAR_ErrorStack test context, in-method staticPush
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
class normalclass
{
    function func()
    {
        global $testline;
        PEAR_ErrorStack::staticPush('test', 3);
        $testline = __LINE__ - 1;
    }
}
$z = new normalclass;
$z->func();

$ret = $stack->pop();
$phpunit->assertEquals(array('file' => __FILE__,
      'line' => $testline,
      'function' => 'func',
      'class' => 'normalclass',
), $ret['context'], 'context');
echo 'tests done';
?>
--EXPECT--
tests done
