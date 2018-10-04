--TEST--
PEAR_RunTest --UPLOAD--
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--POST--
hi=there&fool=you
--UPLOAD--
file1.txt=setup.php.inc
myfile2.txt=test_bug9971.phpt
--FILE--
<?php
var_dump($_FILES);
var_dump($_POST);
$fp = fopen('php://input', 'r');
$a = fread($fp, 8192);
fclose($fp);
var_dump($a);
?>
--EXPECTF--
array(2) {
  ["file1_txt"]=>
  array(5) {
    ["name"]=>
    string(13) "setup.php.inc"
    ["type"]=>
    string(10) "text/plain"
    ["tmp_name"]=>
    string(%d) "%s"
    ["error"]=>
    int(0)
    ["size"]=>
    int(%d)
  }
  ["myfile2_txt"]=>
  array(5) {
    ["name"]=>
    string(17) "test_bug9971.phpt"
    ["type"]=>
    string(10) "text/plain"
    ["tmp_name"]=>
    string(%d) "%s"
    ["error"]=>
    int(0)
    ["size"]=>
    int(%d)
  }
}
array(2) {
  ["hi"]=>
  string(5) "there"
  ["fool"]=>
  string(3) "you"
}
string(0) ""
