<?php
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

$TEST_MODE = false;

require_once("formlets.php");

/*$formlet = CombinedFormletsFactory::instantiate(array
            ( PureValueFactory::instantiate(array(new FunctionValue("intval")))
            , TextInputFactory::instantiate(array())
            )); 
*/
/*$formlet = _pure(_function(1, "intval"))->cmb(_text_input());

$res = $formlet->build(NameSource::instantiate());

echo $res["renderer"]->render()."\n";
$val = $res["collector"]->collect(array("input0" => "123"))->get();
echo $val.(is_int($val)?" is int":" is no int");
*/

class _Date {
    public function __construct($y, $m, $d) {
        guardIsInt($y);
        guardIsInt($m);
        guardIsInt($d);
        $this->y = $y;
        $this->m = $m;
        $this->d = $d;
    }

    public function toISO() {
        return $this->y."-".$this->m."-".$this->d;
    }
}

function mkDate($y, $m, $d) {
    return new _Date($y, $m, $d);
}

$int_formlet = _pure(_function(1, "intval"))->cmb(_text_input());

$formlet = _pure(_function(3, "mkDate"))
                ->cmb($int_formlet)
                ->cmb($int_formlet)
                ->cmb($int_formlet);

$res = $formlet->build(NameSource::instantiate());
$val = $res["collector"]->collect(array
                            ( "input0" => "2014"
                            , "input1" => "12"
                            , "input2" => "24"
                            ))
                        ->get();
echo $val->toISO();


/*function guardInRange($l,$r,$value) {
    if ($value < $l || $value > $r) {
        throw new Exception("Expected value to be in range $l to $r, but is $value");
    }
}*/

?>

