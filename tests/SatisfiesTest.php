<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

require_once("src/formlets.php");
require_once("tests/FormletTest.php");

class SatisfiesTest extends PHPUnit_Framework_TestCase {
    use FormletTestTrait;

    public function formlets() {
        $alwaysTrue = _fn(function ($_) { return true; });
        $pure = _pure(_val(42));
        return array
            ( array($pure->satisfies($alwaysTrue, "ERROR"))
            );
    }
}

?>
