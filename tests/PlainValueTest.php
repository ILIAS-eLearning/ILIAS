<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 + This program is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
        $this->assertEquals($value->origin(), $origin ? $origin : null);
    }
}

class PlainValueTest extends PHPUnit_Framework_TestCase {
    use PlainValueTestTrait;
    
    public function plain_values() {
        $val = rand();
        $rnd = md5(rand());
        $value = _val($val, $rnd);
        return array
            ( array($value, $val, $rnd)
            );
    }
}

?>
