<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Formlets\Internal\Values as V;

class ErrorValueTest extends PHPUnit_Framework_TestCase {
    /**
     * One can get a dictionary out of an error that contains the error messages
     * from the error itself and all the other errors that led to it.
     */
    public function testErrorToDict() {
        $a = V::error("a", "a");
        $b = V::error("b", "b");
        $c = V::error("c", "c");
        $x1 = V::error("1","x");
        $x2 = V::error("2", "x");
        $all = V::error("all"
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
