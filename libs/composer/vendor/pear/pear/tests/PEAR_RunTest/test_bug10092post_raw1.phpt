--TEST--
PEAR_RunTest --POST_RAW-- normal
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--POST_RAW--
Content-Type: multipart/form-data; boundary=---------------------------20896060251896012921717172737
-----------------------------20896060251896012921717172737
Content-Disposition: form-data; name="submitter"

testname
-----------------------------20896060251896012921717172737
Content-Disposition: form-data; name="pics"; filename="bug37276.txt"
Content-Type: text/plain

bug37276

-----------------------------20896060251896012921717172737--
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
array(1) {
  ["pics"]=>
  array(5) {
    ["name"]=>
    string(12) "bug37276.txt"
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
array(1) {
  ["submitter"]=>
  string(8) "testname"
}
string(0) ""
