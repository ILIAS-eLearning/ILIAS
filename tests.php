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

function alwaysTrue($val) {
    return true;
}

function id($val) {
    return $val;
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

function print_test($name) {
    $test_name = "test_".$name;
    $res = $test_name();
    echo "Checking $name:\n";
    foreach($res as $test => $result) {
        echo "\t$test: ".O_F($result)."\n";
    }
    echo "=> ".O_F(andR($res))."\n";
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
    $val = rand();
    $origin = md5($val);
    $value = _value($val, $origin);

    return array
        ( "One can't get a value out of a function value"
            => raises(array($fn, "get"), array(), "GetError")
        , "A function is applicable"
            => $fn->isApplicable()
        , "One can apply a function value to an ordinary value."
            => $fn->apply($value)
        , "An function value is no error"
            => !$value->isError()
        , "For a function Value, error() raises"
            => raises(array($value, "error"), array(), "Exception")
        , "Function values origin defaults to null"
            => $fn->origin() === null 
        , "Result of application is a value."
            => andR(_test_PlainValue($fn->apply($value), $val, null))
        );
} 

print_test("PlainValue");
print_test("FunctionValue");
exit();

/******************************************************************************
 * Tests on implementations of Formlets.
 */

function verboseCheck_isFormlet($name, $args) {
    $name .= "Factory";
    $formlet = $name::instantiate($args);
    //$formlet_pred = $formlet
    //    ->satisfies(_function(1, alwaysTrue), "This is impossible");
    $res = $formlet->build(NameSource::unsafeInstantiate());
    $renderer_res = $res["renderer"]->render();
    return array
        ( "Formlet has correct class."
            => $formlet instanceof Formlet
        , "Renderer has correct instance"
            => $res["renderer"] instanceof Renderer
        , "Renderer returns string."
            => is_string($renderer_res)
        , "Collector has correct instance"
            => $res["collector"] instanceof Collector
        , "Name source has correct instance."
            => $res["name_source"] instanceof NameSource
        );
}

function check_isFormlet($name, $args) {
    $res = verboseCheck_isFormlet($name, $args);
    return andR($res);
}

function print_check_isFormlet($name, $args) {
    $res = verboseCheck_isFormlet($name, $args);
    echo "Checking $name:\n";
    foreach($res as $test => $result) {
        echo "\t$test: ".O_F($result)."\n";
    }
    echo "=> ".O_F(andR($res))."\n";
}

// PureFormlet
print_check_isFormlet("PureFormlet", array(new PlainValue(42)));
echo "\n";

// CombinedFormlets
$pv = PureFormletFactory::instantiate(array(new PlainValue(1337)));
print_check_isFormlet("CombinedFormlets", array($pv, $pv));
echo "\n";

// CheckedFormlet
print_check_isFormlet("CheckedFormlet", array
                        ( _pure(_value(3))
                        , _function(1, "alwaysTrue")
                        , "ERROR"
                        ));
echo "\n";

// MappedCollectorFormlet
print_check_isFormlet("MappedCollectorFormlet", array
                        ( _pure(_value("3"))
                        , _function(1, "intval")
                        ));
echo "\n";

// StaticFormlet
print_check_isFormlet("StaticFormlet", array("Static"));
echo "\n";

// TextInputFormlet
print_check_isFormlet("TextInput", array("Hello World!"));
echo "\n";

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

