--TEST--
PEAR_RunTest --POST--
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--POST--
test=hi
--FILE--
<?php
var_dump($_POST);
?>
--EXPECT--
array(1) {
  ["test"]=>
  string(2) "hi"
}
