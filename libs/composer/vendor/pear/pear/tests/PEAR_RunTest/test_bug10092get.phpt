--TEST--
PEAR_RunTest --GET--
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--GET--
test=hi
--FILE--
<?php
var_dump($_GET);
?>
--EXPECT--
array(1) {
  ["test"]=>
  string(2) "hi"
}
