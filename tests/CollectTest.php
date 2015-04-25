<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

require_once("src/formlets.php");
require_once("tests/FormletTest.php");

class CollectTest extends PHPUnit_Framework_TestCase {
    /**
    * _collect is still applicable after apply.
    * @dataProvider values_to_collect
    */
    public function testIsApplicableAfterApply($values) {
        $this->performApplications($values);
        for ($i = 1; $i < count($values); ++$i) {
            $this->assertTrue($this->collected[$i]->isApplicable());
        }
    }

    /**
     *_collect is value after application to stop.
     * @dataProvider values_to_collect
     */
    public function testIsValueAfterApplicationOfStop($values) {
        $this->performApplications($values);
        $this->assertFalse($this->collected[count($values)]->isApplicable());
    }

    /**
     * _collect returns collected array after application of stop.
     * @dataProvider values_to_collect
     */
    public function testContainsArrayAfterApplicationOfStop($values) {
        $this->performApplications($values);
        $this->assertEquals( $this->collected[count($values)]->get()
                           , $values
                           );
    }

    /**
     * _collect works in formlet.
     * @dataProvider values_to_collect
     */
    public function testCollectWorksInFormlet($values) {
        $this->asFormlet($values);
        $this->assertEquals($this->formlet_result->get(), $values);
    }
         
    public function values_to_collect() {
        return array
            ( array(array())
            , array(array(1,2,3,4))
            );
    }
    
    public function performApplications($values) {
        $this->collected = array(_collect());
        $count = 0;
        foreach ($values as $value) {
            $this->collected[] = $this->collected[$count]->apply(_val($value));
            $count++;
        }
        $this->collected[$count] = $this->collected[$count]->apply(stop());
        $this->count = $count;
    }
    
    public function asFormlet($values) {
        $this->formlet_collected = _pure(_collect());
        foreach ($values as $value) {
            $this->formlet_collected = $this->formlet_collected
                                            ->cmb(_pure(_val($value)))
                                            ;
        }
        $this->formlet_collected = $this->formlet_collected
                                        ->cmb(_pure(stop()))
                                        ;

        $ns = NameSource::instantiate("test");
        $repr = $this->formlet_collected->instantiate($ns);
        $this->formlet_result = $repr["collector"]->collect(array());
    }

    public function testCollectCanBeReused() {
        $fn = collect();
        $fn_a = $fn->apply(val(1))->apply(val(2))->apply(stop());
        $fn_b = $fn->apply(val(3))->apply(val(4))->apply(stop());

        $res_a = $fn_a->get();
        $res_b = $fn_b->get();

        $this->assertEquals($res_a, array(1, 2));
        $this->assertEquals($res_b, array(3, 4));
    } 
}

?>
