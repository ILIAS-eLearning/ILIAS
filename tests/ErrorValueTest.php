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

require_once("formlets/values.php");

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
     * An Error value tracks origin.
     * @dataProvider error_values 
     */
    public function testErrorOriginsAreCorrect(Value $value, $reason, $origin) { 
        $this->assertEquals($value->origin(), $origin ? $origin : null);
    }
}


class ErrorValueTest extends PHPUnit_Framework_TestCase {
    /**
     * One can get a dictionary out of an error that contains the error messages
     * from the error itself and all the other errors that led to it.
     */
    public function testErrorToDict() {
        $a = _error("a", "a");
        $b = _error("b", "b");
        $c = _error("c", "c");
        $x1 = _error("1","x");
        $x2 = _error("2", "x");
        $all = _error("all"
                     , "all"
                     , array($a, $b, $c, $x1, $x2)
                     );

        $dict = $all->toDict();

        $this->assertArrayHasKey("a", $dict);
        $this->assertArrayHasKey("b", $dict);
        $this->assertArrayHasKey("c", $dict);
        $this->assertArrayHasKey("x", $dict);
        $this->assertArrayHasKey("all", $dict);
        $this->assertCount(5, $dict);

        $this->assertEquals($dict["a"], array("a"));
        $this->assertEquals($dict["b"], array("b"));
        $this->assertEquals($dict["c"], array("c"));
        $this->assertEquals($dict["x"], array("1", "2"));
        $this->assertEquals($dict["all"], array("all"));
    } 
}

?>
