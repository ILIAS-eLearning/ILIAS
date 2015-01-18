<?php

require_once("formlets.php");

/******************************************************************************
 * Helpers
 */

function andReducer($carry, $item) {
    return $carry && $item;
}

function andR($arr) {
    return array_reduce($arr, "andReducer", true);
}

function O_F($val) {
    return $val?"OK":"FAIL"; 
}

function print_r_id($val) {
    print_r($val);
    return $val;
}


function alwaysTrue() {
    static $fn = null;
    if ($fn === null) {
        $fn = _fn(function ($val) {
            return true;
        });
    }
    return $fn;
}

class TestException extends Exception {
};

function alwaysThrows0 () {
    static $fn = null;
    if ($fn === null) {
        $fn = _fn(function () {
            throw new TestException("test exception");
        });
    }
    return $fn;
};

function alwaysThrows1 () {
    static $fn = null;
    if ($fn === null) {
        $fn = _fn(function ($one) {
            throw new TestException("test exception");
        });
    }
    return $fn;
}

function alwaysThrows2 () {
    static $fn = null;
    if ($fn === null) {
        $fn = _fn(function ($one, $two) {
            throw new TestException("test exception");
        });
    }
    return $fn;
}

function raises(callable $fun, $args, $error) {
    try {
        call_user_func_array($fun, $args);
    }
    catch (Exception $e) {
        return $e instanceof $error;
    }
    return false;
}

$num_tests = 0;
$successfull_tests = 0;

function print_and_record_test($name) {
    global $num_tests, $successfull_tests;
    $test_name = "test_".$name;
    $res = $test_name();
    echo "Checking $name:\n";
    $num_tests += count($res);
    foreach($res as $test => $result) {
        echo "\t$test: ".O_F($result)."\n";
        if ($result) ++$successfull_tests;
    }
    echo "=> ".O_F(andR($res))."\n";
    echo "\n";
}

function print_results() {
    global $num_tests, $successfull_tests;
    echo "Performed $num_tests tests, $successfull_tests where successfull.\n"
        ."=> ".O_F($num_tests == $successfull_tests)."\n"
        ;
}

/******************************************************************************
 * Tests on implementations of Formlets.
 */

function _test_isFormlet($formlet) {
    $res = $formlet->instantiate(NameSource::unsafeInstantiate());
    $builder_res = $res["builder"]->instantiate()->render();
    return array
        ( "Formlet has correct class"
            => $formlet instanceof Formlet
        , "Builder has correct instance"
            => $res["builder"] instanceof Builder
        , "Builder->render() returns string"
            => is_string($builder_res)
        , "Collector has correct instance"
            => $res["collector"] instanceof Collector
        , "Name source has correct instance"
            => $res["name_source"] instanceof NameSource
        );
}

function test_Pure() {
    return _test_isFormlet(_pure(_val(42)));
}
print_and_record_test("Pure");

function test_Combined() {
    $pure = _pure(_val(1337));
    return _test_isFormlet($pure->cmb($pure));
}
print_and_record_test("Combined");

function test_Checked() {
    $pure = _pure(_val(42));
    return _test_isFormlet($pure->satisfies(alwaysTrue(), "ERROR"));
}
print_and_record_test("Checked");

function test_MappedFormlet() {
    $pure = _pure(_val("1337"));
    return _test_isFormlet($pure->map(_intval()));
}
print_and_record_test("MappedFormlet");

function test_MappedHTMLFormlet() {
    $pure = _pure(_val("1337"));
    return _test_isFormlet($pure->mapHTML(_fn( function($_, $a) { return $a; })));
}
print_and_record_test("MappedHTMLFormlet");

function test_Text() {
    return _test_isFormlet(_text("foobar"));
}
print_and_record_test("Text");

function test_Input() {
    return _test_isFormlet(_input("foo"));
}
print_and_record_test("Input");

function test_TextInput() {
    return _test_isFormlet(_text_input());
}
print_and_record_test("TextInput");

function test_TextArea() {
    return _test_isFormlet(_textarea());
}
print_and_record_test("TextArea");

function test_FieldSet() {
    return _test_isFormlet(_fieldset("Static: ", _pure(_val(42))));    
}
print_and_record_test("FieldSet");

function test_Checkbox() {
    return _test_isFormlet(_checkbox());
}
print_and_record_test("Checkbox");

function test_Submit() {
    return _test_isFormlet(_submit("Submit"));
}
print_and_record_test("Submit");


/******************************************************************************
 * Tests on premade helpers.
 */

function test__collect() {
    $collected0 = _collect();
    $collected1 = $collected0->apply(_val(1));
    $collected2 = $collected1->apply(_val(2));
    $collected3 = $collected2->apply(_val(3));
    $collected_stop = $collected3->apply(stop());

    $formlet_collected =
        _pure(_collect())
        ->cmb(_pure(_val(3)))
        ->cmb(_pure(_val(2)))
        ->cmb(_pure(_val(1)))
        ->cmb(_pure(stop()))
        ;
    $repr = $formlet_collected->instantiate(NameSource::unsafeInstantiate());
    $formlet_result = $repr["collector"]->collect(array());
    
    return array
        ( "_collect is function value after apply"
            => $collected1->isApplicable()
            && $collected2->isApplicable()
            && $collected3->isApplicable()
        , "_collect is value after application to stop"
            => !$collected_stop->isApplicable()
        , "_collect returns collected array after application of stop"
            => $collected_stop->get() === array(1,2,3)
        , "_collect works in formlet"
            => $formlet_result->get() === array(3,2,1) 
        );
}
print_and_record_test("_collect");


echo "\n";
print_results();

/******************************************************************************
 * Things i needed to try during implementation.
 */

/*
function foo() {
    echo "bar";
}

$baz = "foo";

$baz();
*/

/*class Foo {
    static function bar() {
        echo "foobar";
    }
}

$foo = "Foo";
$bar = "bar";

$foo::$bar();
*/

//echo "is_int(0) = ".(is_int(0) ? "true" : "false");

//echo (1 && 1 && 1)?"true":"false";

//require_once("formlets.php");
//echo array_reduce(array(1,1,1), "andR")?"TRUE":"FALSE";

/*class Foo {
}

$foo = new Foo();
echo getType($foo);
*/

/*$foo = array("foo");

function bar($arr) {
    $arr[] = "bar";
}

print_r($foo);
*/

/*
$foo = array("foo");
$bar = $foo;
$bar[] = "bar";

print_r($bar);
*/

/*class FooError extends Exception {};

$error = "FooError";

try {
    throw new FooError("foobar");
}
catch(Exception $e) {
    if ($e instanceof $error) {
        echo "CAUGHT";
    }
    else {
        throw $e;
    }
}
*/
/*
$foo = function() {
    echo "Hello World!\n";
};

<<<<<<< HEAD
/*$foo = function($name) {
    echo "Hello $name";  
};

$foo("World");

call_user_func($foo, "ECHO Echo echo .... ");
*/
/*
class Foo {
    public function bar($a, $b) {
        return; 
    }
}

$foo = new Foo();
$foo_c = array($foo, "bar");
call_user_func($foo_c, null, null);
$refl = new ReflectionFunction($foo_c);
print_r($refl);
*/
?>
