--TEST--
PEAR_RunTest --INI--
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--INI--
include_path=hooba
--FILE--
<?php
var_dump(ini_get('include_path'));
?>
--EXPECT--
string(5) "hooba"
