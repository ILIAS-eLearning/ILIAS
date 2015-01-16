<?php

require_once("formlets.php");

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
     * @expectedException ApplyError
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
        $this->assertEquals($value->origins(), $origin ? array($origin) : array());
    }
}

class PlainValueTest extends PHPUnit_Framework_TestCase {
    use PlainValueTestTrait;
    
    public function plain_values() {
        $val = rand();
        $rnd = md5(rand());
        $value = _val($val, array($rnd));
        return array
            ( array($value, $val, $rnd)
            );
    }
}

?>
