<?php

require_once("formlets.php");
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
