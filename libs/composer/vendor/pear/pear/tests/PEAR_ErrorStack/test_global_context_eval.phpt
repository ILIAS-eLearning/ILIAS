--TEST--
PEAR_ErrorStack test context, basic global eval
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
eval('$stack->push(3);');
$testline = __LINE__ - 1;

$ret = $stack->pop();
$phpunit->assertEquals(array('file' => __FILE__,
      'line' => $testline,), $ret['context'], 'context');
echo 'tests done';
?>
--EXPECT--
tests done
