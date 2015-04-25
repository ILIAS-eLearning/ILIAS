<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Formlets\Internal\Values as V;

class FunctionValueTest extends PHPUnit_Framework_TestCase {
    use PlainValueTestTrait;
    use FunctionValueTestTrait;
    use ErrorValueTestTrait;
 
    /**
     * Check weather compose works as expected: (f . g)(x) = f(g(x))
     * @dataProvider compose_functions
     **/
    public function testFunctionComposition($fn, $fn2, $value) {
        $res1 = $fn->composeWith($fn2)->apply($value);
        $tmp = $fn2->apply($value);
        $res2 = $fn->apply($tmp);
        $this->assertEquals($res1->get(), $res2->get());
    }

    /**
     * Check weather application operator works as expected: f $ x = f x
     * @dataProvider compose_functions
     **/
    public function testApplicationOperator($fn, $fn2, $value) {
        $fn = $fn->composeWith($fn2);
        $res1 = $fn->apply($value);
        $res2 = _application_to($value)->apply($fn);
        $this->assertEquals($res1->get(), $res2->get());
    }

   
    public function plain_values() {
        $fn = _fn("id");
        $val = rand();
        $origin = md5($val);
        $value = _val($val, $origin);
        return array
            // Result of successfull function application is a value.
            ( array($fn->apply($value)->force(), $val, "id")
            );
    }

    public function function_values() {
        $fn = _fn("id");
        $fn2 = $this->alwaysThrows1()
                ->catchAndReify("TestException");
        $val = rand();
        $origin = md5($val);
        $value = _val($val, $origin);

        return array
            ( array($fn, $value, 1, "id")
            , array($fn2, $value, 1, ANONYMUS_FUNCTION_ORIGIN)
            );
    }

    public function error_values() {
        $fn = $this->alwaysThrows1()
                ->catchAndReify("TestException");
        $fn2 = $this->alwaysThrows2()
                ->catchAndReify("TestException");
        $val = rand();
        $origin = md5($val);
        $value = _val($val, $origin);
        return array
            // Result of application of throwing function is an error.
            ( array($fn->apply($value)->force(), "test exception", ANONYMUS_FUNCTION_ORIGIN)
            // Function still catches after application.
            , array($fn2->apply($value)->apply($value)->force(), "test exception", ANONYMUS_FUNCTION_ORIGIN)
            );
    }

    public function compose_functions() {
        $times2 = V::fn(function($v) { return $v * 2; });
        return array
            ( array($times2, V::fn("intval", 1), V::val("42"))
            , array(V::fn("count", 1), V::fn("explode", 2, array(" ")), V::val("x x x x"))
            );
    }


    protected function alwaysThrows0 () {
      return _fn(function () {
         throw new TestException("test exception");
      });
    }

    protected function alwaysThrows1 () {
      return _fn(function ($a) {
         throw new TestException("test exception");
      });
    }

    protected function alwaysThrows2 () {
      return _fn(function ($a, $b) {
         throw new TestException("test exception");
      });
    }
}

class TestException extends Exception {
};

?>
