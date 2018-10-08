--TEST--
PEAR_RunTest Avoid running --CLEAN-- with the cgi-bin interpreter
--POST_RAW--
Foo
--FILE--
<?php ?>
--CLEAN--
<?php ?>
