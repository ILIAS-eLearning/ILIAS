<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

require_once("src/formlets.php");
require_once("tests/FormletTest.php");

class FieldsetTest extends PHPUnit_Framework_TestCase {
    use FormletTestTrait;

    public function formlets() {
        return array
            ( array(_fieldset("Static: ", _pure(_val(42))))
            );
    }
}

?>
