--TEST--
PEAR_Error: expected errors
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php // -*- PHP -*-

// Test for: PEAR.php
// Parts tested: - PEAR_Error class
//               - PEAR::expectError
//               - PEAR::popExpect

include_once "PEAR.php";



function errorhandler($eobj)
{
    if (PEAR::isError($eobj)) {
        print "error: ".$eobj->getMessage()."\n";
    } else {
        print "errorhandler called without error object\n";
    }
}

$obj = new PEAR;
$obj->setErrorHandling(PEAR_ERROR_CALLBACK, "errorhandler");

print "subtest 1\n";
$obj->expectError(1);
$obj->raiseError("1", 1);
$obj->popExpect();
$obj->raiseError("2", 2);

print "subtest 2\n";
$obj->expectError(3);
$obj->expectError(2);
$obj->raiseError("3", 3);

print "subtest 3\n";
$obj->popExpect();
$obj->raiseError("3", 3);
$obj->popExpect();

print "subtest 4\n";
$obj->expectError(array(1,2,3,4,5));
$obj->raiseError("0", 0);
$obj->raiseError("1", 1);
$obj->raiseError("2", 2);
$obj->raiseError("3", 3);
$obj->raiseError("4", 4);
$obj->raiseError("5", 5);
$obj->raiseError("6", 6);
$obj->raiseError("error");
$obj->popExpect();

print "subtest 5\n";
$obj->expectError("*");
$obj->raiseError("42", 42);
$obj->raiseError("75", 75);
$obj->raiseError("13", 13);
$obj->popExpect();

print "subtest 6\n";
$obj->expectError();
$obj->raiseError("123", 123);
$obj->raiseError("456", 456);
$obj->raiseError("789", 789);
$obj->popExpect();

print "subtest 7\n";
$obj->expectError("syntax error");
$obj->raiseError("type mismatch");
$obj->raiseError("syntax error");
$obj->popExpect();

print "subtest 8\n";
$obj->expectError(array(1, 2, 3));
$obj->expectError(array(3, 4, 5));
$obj->raiseError(4);
$obj->delExpect(2);
$obj->raiseError(3);
$obj->delExpect(1, 3, 4, 5);
$err = $obj->delExpect(2);

?>
--EXPECT--
subtest 1
error: 2
subtest 2
error: 3
subtest 3
subtest 4
error: 0
error: 6
error: error
subtest 5
subtest 6
subtest 7
error: type mismatch
subtest 8
error: 4
error: 3
error: The expected error you submitted does not exist
