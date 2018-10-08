--TEST--
PEAR_Config::arrayMergeRecursive()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$a = array(
'hello' => 'there',
'I' => 'am',
'an' => array(
    'of' => 'many',
    'different' => array(
        'items' => 'like',
        'this' => 'for example',
    )
)
);
$b = array(
'hi' => 'there',
'I' => 'rock',
'but' => 'am',
'an' => array(
    'of' => 'few',
    'different' => array(
        'items' => 'not',
        'this' => 'thing',
        'but' => 'this one',
    )
)
);

$phpunit->assertEquals(array (
  'hello' => 'there',
  'an' => 
  array (
    'different' => 
    array (
      'items' => 'not',
      'this' => 'thing',
      'but' => 'this one',
    ),
    'of' => 'many',
  ),
  'hi' => 'there',
  'I' => 'rock',
  'but' => 'am',
), PEAR_Config::arrayMergeRecursive($a, $b), 'a, b');
$phpunit->assertEquals(array (
  'hi' => 'there',
  'but' => 'am',
  'an' => 
  array (
    'different' => 
    array (
      'but' => 'this one',
      'items' => 'like',
      'this' => 'for example',
    ),
    'of' => 'few',
  ),
  'hello' => 'there',
  'I' => 'am',
), PEAR_Config::arrayMergeRecursive($b, $a), 'b, a');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
