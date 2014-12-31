<?php

require_once("formlets.php");

/****************************/
/* Tests on implementations */
/****************************/

function alwaysTrue($val) {
    return true;
}

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
    return _and($res);
}

function print_check_isFormlet($name, $args) {
    $res = verboseCheck_isFormlet($name, $args);
    echo "Checking $name:\n";
    foreach($res as $test => $result) {
        echo "\t$test: "._o_f($result)."\n";
    }
    echo "=> "._o_f(_and($res))."\n";
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

