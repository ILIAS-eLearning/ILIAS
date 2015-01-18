<?php

require_once("formlets.php");
require_once("tests/FormletTest.php");

class InputTest extends PHPUnit_Framework_TestCase {
    use FormletTestTrait;

    public function formlets() {
        return array
            ( array(_input("text"))
            , array(_input("foo"))
            );
    }
}

?>
