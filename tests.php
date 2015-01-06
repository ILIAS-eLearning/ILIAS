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

function alwaysTrue($val) {
    return true;
}

class TestException extends Exception {
};

function alwaysThrows0() {
    throw new TestException("test exception");
}

function alwaysThrows1($one) {
    throw new TestException("test exception");
}

function alwaysThrows2($one, $two) {
    throw new TestException("test exception");
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
 * Tests on Values
 */

function test_PlainValue() {
    $val = rand();
    $rnd = md5(rand());
    $value = _value($val, $rnd);
    return _test_PlainValue($value, $val, $rnd); 
}
 

function _test_PlainValue(Value $value, $val, $origin) {
    return array
        ( "One can get the value out that was stuffed in"
            => $value->get() === $val
        , "An ordinary value is not applicable."
            => !$value->isApplicable()
        , "One can't apply an ordinary value"
            => raises(array($value, "apply"), array($value), "ApplyError")
        , "An ordinary value is no error"
            => !$value->isError()
        , "For an ordinary Value, error() raises"
            => raises(array($value, "error"), array(), "Exception")
        , "Ordinary value tracks origin"
            => $value->origin() === $origin
        );
}

function test_FunctionValue() {
    $fn = _function(1, "id");
    $fn2 = _function(1, "alwaysThrows1")
            ->catchAndReify("TestException");
    $fn3 = _function(2, "alwaysThrows2")
            ->catchAndReify("TestException");
    $val = rand();
    $origin = md5($val);
    $value = _value($val, $origin);

    return array_merge( _test_FunctionValue($fn, $value, 1)
                      , array
        ( "Throwing test function passes function value tests"
            => andR(_test_FunctionValue($fn2, $value, 1))
        , "Result of successfull function application is a value"
            => andR(_test_PlainValue($fn->apply($value), $val, null))
        , "Result of application of throwing function ia an error"
            => andR(_test_ErrorValue($fn2->apply($value), "test exception", null))
        , "Test functions have arity 1"
            => $fn->arity() === 1 && $fn2->arity() === 1
        , "Function still catches after application"
            => andR(_test_ErrorValue($fn3->apply($value)->apply($value), "test exception", null))
        , "Function value returns correct results for intval"
            => _test_FunctionValue_results("intval", 1, array(array("12"), array("122123"), array("45689")))
        , "Function value returns correct results for explode"
            => _test_FunctionValue_results("explode", 2, array(array(" ", "Hello World"), array(";", "1;2"), array("-", "2015-01-02")))
        ));
}

function _test_FunctionValue($fn, $value, $arity) {
    $tmp = $fn;
    for ($i = 0; $i < $arity; ++$i) {
        $tmp = $tmp->apply($value);
    }
    return array
        ( "One can't get a value out of an unsatisfied function value"
            => raises(array($fn, "get"), array(), "GetError")
        , "Function value is applicable"
            => $fn->isApplicable()
        , "One can apply function value to ordinary values."
            => $fn->apply($value)
        , "A function value is no error"
            => !$fn->isError()
        , "For function value, error() raises"
            => raises(array($fn, "error"), array(), "Exception")
        , "Function value origin defaults to null"
            => $fn->origin() === null
        , "Functions has expected arity of $arity"
            => $fn->arity() === $arity
        , "Functions is not satisfied or has arity 0"
            => $arity == 0 || !$fn->isSatisfied()
        , "After $arity applications, function is satisfied"
            => $tmp->isSatisfied()
        );
}

function _test_FunctionValue_result($fn_name, $arity, $args) {
    $fn = _function($arity, $fn_name);
    $res1 = call_user_func_array($fn_name, $args);
    $tmp = $fn;
    for ($i = 0; $i < $arity; ++$i) {
        $tmp = $tmp->apply(_value($args[$i]));
    }
    $res2 = $tmp->get();
    return $res1 === $res2;
}

function _test_FunctionValue_results($fn_name, $arity, $argss) {
    $result = true;
    foreach ($argss as $args) {
        $result = $result && _test_FunctionValue_result($fn_name, $arity, $args);
    }
    return $result;
}

function test_ErrorValue() {
    $rnd = md5(rand());
    $value = _error($rnd, _value($rnd, $rnd));
    return _test_ErrorValue($value, $rnd, $rnd); 
}
 

function _test_ErrorValue(Value $value, $reason, $origin) {
    return array
        ( "One can't get a value out."
            => raises(array($value, "get"), array(), "GetError")
        , "An error value is applicable"
            => $value->isApplicable()
        , "One can apply an error value and gets an error back."
            => $value->apply(_value(1))->isError()
        , "An error value is no error"
            => $value->isError()
        , "One can get the reason out of the error value"
            => $value->error() == $reason
        , "Error value tracks origin"
            => $value->origin() === $origin
        );
}

print_and_record_test("PlainValue");
print_and_record_test("ErrorValue");
print_and_record_test("FunctionValue");

/******************************************************************************
 * Tests on implementations of Formlets.
 */

function _test_isFormlet($formlet) {
    $res = $formlet->build(NameSource::unsafeInstantiate());
    $builder_res = $res["builder"]->build()->render();
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
    return _test_isFormlet(_pure(_value(42)));
}
print_and_record_test("Pure");

function test_Combined() {
    $pure = _pure(_value(1337));
    return _test_isFormlet($pure->cmb($pure));
}
print_and_record_test("Combined");

function test_Checked() {
    $pure = _pure(_value(42));
    return _test_isFormlet($pure->satisfies(_function(1, "alwaysTrue"), "ERROR"));
}
print_and_record_test("Checked");

function test_MappedCollector() {
    $pure = _pure(_value("1337"));
    return _test_isFormlet($pure->mapCollector(_function(1, "intval")));
}
print_and_record_test("MappedCollector");

function test_Text() {
    return _test_isFormlet(_text("foobar"));
}
print_and_record_test("Text");

function test_TextInput() {
    return _test_isFormlet(_text_input());
}
print_and_record_test("TextInput");

function test_TextArea() {
    return _test_isFormlet(_textarea());
}
print_and_record_test("TextArea");

function test_FieldSet() {
    return _test_isFormlet(_fieldset("Static: ", _pure(_value(42))));    
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
    $collected1 = $collected0->apply(_value(1));
    $collected2 = $collected1->apply(_value(2));
    $collected3 = $collected2->apply(_value(3));
    $collected_stop = $collected3->apply(stop());

    $formlet_collected =
        _pure(_collect())
        ->cmb(_pure(_value(3)))
        ->cmb(_pure(_value(2)))
        ->cmb(_pure(_value(1)))
        ->cmb(_pure(stop()))
        ;
    $repr = $formlet_collected->build(NameSource::unsafeInstantiate());
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

