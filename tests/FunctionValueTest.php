<?php

require_once("formlets.php");
require_once("tests/PlainValueTest.php");
require_once("tests/ErrorValueTest.php");

trait FunctionValueTestTrait {
    /** 
     * One can't get a value out of an unsatisfied function value.
     * @dataProvider function_values 
     * @expectedException GetError
     */
    public function testNotSatisfiedNoValue($fn, $value, $arity) {
        if ($arity !== 0) {
            $fn->get();
        }
        else {
            throw new GetError("mock");
        }
    }

    /** 
     * Function value is applicable.
     * @dataProvider function_values 
     */
    public function testFunctionIsApplicable($fn, $value, $arity) {
        if ($arity !== 0) {
            $this->assertTrue($fn->isApplicable());
        }
    }

    /** 
     * One can apply function value to ordinary values.
     * @dataProvider function_values 
     */
    public function testFunctionCanBeApplied($fn, $value, $arity) {
        if ($arity > 0) {
            $this->assertInstanceOf('FunctionValue', $fn->apply($value));
        }
    }

    /** 
     * A function value is no error.
     * @dataProvider function_values 
     */
    public function testFunctionIsNoError($fn, $value, $arity) {
        $this->assertFalse($fn->isError());
    }

    /** 
     * For function value, error() raises.
     * @dataProvider function_values 
     * @expectedException Exception 
     */
    public function testFunctionHasNoError($fn, $value, $arity) {
        $fn->error();
    }

    /** 
     * Function value origin defaults to empty array.
     * @dataProvider function_values 
     */
    public function testFunctionsOriginsAreCorrect($fn, $value, $arity) {
        $this->assertEquals($fn->origins(), array());
    }

    /** 
     * Functions has expected arity of $arity.
     * @dataProvider function_values 
     */
    public function testFunctionsArityIsCorrect($fn, $value, $arity) {
        $this->assertEquals($fn->arity(), $arity);
    }

    /** 
     * Functions is not satisfied or has arity 0.
     * @dataProvider function_values 
     */
    public function testFunctionSatisfaction($fn, $value, $arity) {
        if ($arity === 0) {
            $this->assertTrue($fn->isSatisfied());
        }
        else {
            $this->assertFalse($fn->isSatisfied());
        }
    }

    /** 
     * After $arity applications, function is satisfied.
     * @dataProvider function_values 
     */
    public function testFunctionIsSatisfiedAfterEnoughApplications($fn, $value, $arity) {
        $tmp = $this->getAppliedFunction($fn, $value, $arity);
        $this->assertTrue($tmp->isSatisfied());
    }

    /**
     * Check weather compose works as expected: (f . g)(x) = f(g(x))
     * @dataProvider function_vales
     **/
    public function testFunctionComposition($fn, $value, $arity) {
        throw new Exception("NYI!");
    }

    /**
     * Check weather application operator works as expected: f $ x = f x
     * @dataProvider function_vales
     **/
    public function testApplicationOperator($fn, $value, $arity) {
        throw new Exception("NYI!");
    }

    protected function getAppliedFunction($fn, $value, $arity) {
        $tmp = $fn;
        for ($i = 0; $i < $arity; ++$i) {
            $tmp = $tmp->apply($value);
        }
        return $tmp;
    }


}

class FunctionValueTest extends PHPUnit_Framework_TestCase {
    use PlainValueTestTrait;
    use FunctionValueTestTrait;
    use ErrorValueTestTrait;
    
    public function plain_values() {
        $fn = _fn("id");
        $val = rand();
        $origin = md5($val);
        $value = _val($val, array($origin));
        return array
            // Result of successfull function application is a value.
            ( array($fn->apply($value)->force(), $val, $origin)
            );
    }

    public function function_values() {
        $fn = _fn("id");
        $fn2 = $this->alwaysThrows1()
                ->catchAndReify("TestException");
        $val = rand();
        $origin = md5($val);
        $value = _val($val, array($origin));

        return array
            ( array($fn, $value, 1)
            , array($fn2, $value, 1)
            );
    }

    public function error_values() {
        $fn = $this->alwaysThrows1()
                ->catchAndReify("TestException");
        $fn2 = $this->alwaysThrows2()
                ->catchAndReify("TestException");
        $val = rand();
        $origin = md5($val);
        $value = _val($val, array($origin));
        return array
            // Result of application of throwing function is an error.
            ( array($fn->apply($value)->force(), "test exception", null)
            // Function still catches after application.
            , array($fn2->apply($value)->apply($value)->force(), "test exception", null)
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
