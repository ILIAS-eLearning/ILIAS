<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Formlets\Internal\Value;
use Lechimp\Formlets\Internal\Values as V;

trait PlainValueTestTrait {
    /** 
     * One can get the value out that was stuffed in *(
     * @dataProvider plain_values 
     */
    public function testInOut($value, $val, $origin) {
        $this->assertEquals($value->get(), $val);
    }

    /**
     * An ordinary value is not applicable.
     * @dataProvider plain_values 
     */
    public function testValueIsNotApplicable($value, $val, $origin) {
        $this->assertFalse($value->isApplicable());
    }

    /**
     * One can't apply an ordinary value.
     * @dataProvider plain_values 
     * @expectedException Lechimp\Formlets\Internal\ApplyError
     */
    public function testValueCantBeApply($value, $val, $origin) {
        $value->apply($value);
    }

    /**
     * An ordinary value is no error.
     * @dataProvider plain_values 
     */
    public function testValueIsNoError($value, $val, $origin) {
        $this->assertFalse($value->isError());
    }

    /**
     * For an ordinary Value, error() raises.
     * @dataProvider plain_values 
     * @expectedException Exception 
     */
    public function testValueHasNoError($value, $val, $origin) {
        $value->error();
    }

    /**
     * Ordinary value tracks origin.
     * @dataProvider plain_values 
     */
    public function testValuesOriginsAreCorrect($value, $val, $origin) {
        $this->assertEquals($value->origin(), $origin ? $origin : null);
    }
}

trait ErrorValueTestTrait {
    /** 
     * One can't get a value out.
     * @dataProvider error_values 
     * @expectedException Lechimp\Formlets\Internal\GetError
     */
    public function testErrorHasNoValue(Value $value, $reason, $origin) { 
        $value->get();
    }

    /** 
     * An error value is applicable.
     * @dataProvider error_values 
     */
    public function testErrorIsApplicable(Value $value, $reason, $origin) { 
        $this->assertTrue($value->isApplicable());
    }

    /** 
     * One can apply an error value and gets an error back.
     * @dataProvider error_values 
     */
    public function testErrorAppliedIsError(Value $value, $reason, $origin) { 
        $this->assertTrue($value->apply(V::val(1))->isError());
    }

    /** 
     * An error value is an error.
     * @dataProvider error_values 
     */
    public function testErrorIsError(Value $value, $reason, $origin) { 
        $this->assertTrue($value->isError());
    }

    /** 
     * One can get the reason out of the error value.
     * @dataProvider error_values 
     */
    public function testErrorHasMessage(Value $value, $reason, $origin) { 
        $this->assertEquals($value->error(), $reason);
    }

    /** 
     * An Error value tracks origin.
     * @dataProvider error_values 
     */
    public function testErrorOriginsAreCorrect(Value $value, $reason, $origin) { 
        $this->assertEquals($value->origin(), $origin ? $origin : null);
    }
}

trait FunctionValueTestTrait {
    /** 
     * One can't get a value out of an unsatisfied function value.
     * @dataProvider function_values 
     * @expectedException Lechimp\Formlets\Internal\GetError
     */
    public function testNotSatisfiedNoValue($fn, $value, $arity, $origin) {
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
    public function testFunctionIsApplicable($fn, $value, $arity, $origin) {
        if ($arity !== 0) {
            $this->assertTrue($fn->isApplicable());
        }
    }

    /** 
     * One can apply function value to ordinary values.
     * @dataProvider function_values 
     */
    public function testFunctionCanBeApplied($fn, $value, $arity, $origin) {
        if ($arity > 0) {
            $this->assertInstanceOf('Lechimp\Formlets\Internal\FunctionValue', $fn->apply($value));
        }
    }

    /** 
     * A function value is no error.
     * @dataProvider function_values 
     */
    public function testFunctionIsNoError($fn, $value, $arity, $origin) {
        $this->assertFalse($fn->isError());
    }

    /** 
     * For function value, error() raises.
     * @dataProvider function_values 
     * @expectedException Exception 
     */
    public function testFunctionHasNoError($fn, $value, $arity, $origin) {
        $fn->error();
    }

    /** 
     * Function value origin defaults to empty array.
     * @dataProvider function_values 
     */
    public function testFunctionsOriginsAreCorrect($fn, $value, $arity, $origin) {
        $this->assertEquals($fn->origin(), $origin);
    }

    /** 
     * Functions has expected arity of $arity.
     * @dataProvider function_values 
     */
    public function testFunctionsArityIsCorrect($fn, $value, $arity, $origin) {
        $this->assertEquals($fn->arity(), $arity);
    }

    /** 
     * Functions is not satisfied or has arity 0.
     * @dataProvider function_values 
     */
    public function testFunctionSatisfaction($fn, $value, $arity, $origin) {
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
    public function testFunctionIsSatisfiedAfterEnoughApplications($fn, $value, $arity, $origin) {
        $tmp = $this->getAppliedFunction($fn, $value, $arity);
        $this->assertTrue($tmp->isSatisfied());
    }

    protected function getAppliedFunction($fn, $value, $arity) {
        $tmp = $fn;
        for ($i = 0; $i < $arity; ++$i) {
            $tmp = $tmp->apply($value);
        }
        return $tmp;
    }
}

trait FormletTestTrait {
    protected function instantiateFormlet($formlet) {
        return $formlet->instantiate(Lechimp\Formlets\Internal\NameSource::instantiate("test"));
    }

    /**
     * Thing has correct class.
     * @dataProvider formlets
     */
    public function testHasFormletClass($formlet) {
        $this->assertInstanceOf("Lechimp\Formlets\Internal\Formlet", $formlet);
    }
     
    /**
     * Builder has correct class.
     * @dataProvider formlets
     */
    public function testBuilderHasBuilderClass($formlet) { 
        $res = $this->instantiateFormlet($formlet);
        $this->assertInstanceOf("Lechimp\Formlets\Internal\Builder", $res["builder"]);
    }

    /**
     * Collector has correct class.
     * @dataProvider formlets
     */
    public function testCollectorHasCollectorClass($formlet) { 
        $res = $this->instantiateFormlet($formlet);
        $this->assertInstanceOf("Lechimp\Formlets\Internal\Collector", $res["collector"]);
    }

    /**
     * Name source has correct class.
     * @dataProvider formlets
     */
    public function testNameSourceHasNameSourceClass($formlet) {
        $res = $this->instantiateFormlet($formlet);
        $this->assertInstanceOf("Lechimp\Formlets\Internal\NameSource", $res["name_source"]);
    }
}

?>
