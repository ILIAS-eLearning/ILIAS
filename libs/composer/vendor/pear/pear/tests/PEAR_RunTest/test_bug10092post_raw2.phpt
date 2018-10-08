--TEST--
PEAR_RunTest --POST_RAW-- php://input
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--POST_RAW--
Content-Type: application/x-www-form-urlencoded
userid=joe&password=guessme
--FILE--
<?php
var_dump($_FILES);
var_dump($_POST);
$fp = fopen('php://input', 'r');
$a = fread($fp, 8192);
fclose($fp);
var_dump($a);
?>
--EXPECT--
array(0) {
}
array(2) {
  ["userid"]=>
  string(3) "joe"
  ["password"]=>
  string(7) "guessme"
}
string(27) "userid=joe&password=guessme"
