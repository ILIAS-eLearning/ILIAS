--TEST--
PEAR_RunTest --COOKIE--
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--COOKIE--
cookie1=val1  ; cookie2=val2%20; cookie3=val 3.; cookie 4= value 4 %3B; cookie1=bogus; %20cookie1=ignore;+cookie1=ignore;cookie1;cookie  5=%20 value; cookie%206=�;cookie+7=;$cookie.8;cookie-9=1;;;- & % $cookie 10=10
--FILE--
<?php
var_dump($_COOKIE);
?>
--EXPECT--
array(10) {
  ["cookie1"]=>
  string(6) "val1  "
  ["cookie2"]=>
  string(5) "val2 "
  ["cookie3"]=>
  string(6) "val 3."
  ["cookie_4"]=>
  string(10) " value 4 ;"
  ["cookie__5"]=>
  string(7) "  value"
  ["cookie_6"]=>
  string(3) "�"
  ["cookie_7"]=>
  string(0) ""
  ["$cookie_8"]=>
  string(0) ""
  ["cookie-9"]=>
  string(1) "1"
  ["-_&_%_$cookie_10"]=>
  string(2) "10"
}
