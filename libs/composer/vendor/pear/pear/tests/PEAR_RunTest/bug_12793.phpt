--TEST--
PEAR_RunTest Bug #12793
--ARGS--
--version
--FILE--
<?php
if (!isset($argc)) {
    $argc = $_SERVER['argc'];
}
if (!isset($argc)) {
    $argv = $_SERVER['argv'];
}
var_dump($argc);
var_dump($argv);
?>
--EXPECTF--
int(2)
array(2) {
  [0]=>
  string(%d) "%sbug_12793.php"
  [1]=>
  string(9) "--version"
}