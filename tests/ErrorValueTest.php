<?php

trait ErrorValueTestTrait {
    /** 
     * One can't get a value out.
     * @dataProvider error_values 
     * @expectedException GetError
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
        $this->assertTrue($value->apply(_val(1))->isError());
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
     * AN Error value tracks origin.
     * @dataProvider error_values 
     */
    public function testErrorOriginsAreCorrect(Value $value, $reason, $origin) { 
        $this->assertEquals($value->origins(), $origin ? array($origin) : array());
    }
}

?>
