--TEST--
PEAR_RunTest Bug #10286 - no output from fatal errors displayed.
--FILE--
<?php
$oops->method();
// PHP5: Fatal error:
// PHP7: Catchable fatal error:
?>
--EXPECTF--
Notice: Undefined variable: oops in %sbug10286.php on line %d

%satal error:%sCall to a member function method() on %s in %sbug10286.php on line %d
