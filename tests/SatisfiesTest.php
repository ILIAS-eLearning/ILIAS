<?php

require_once("formlets.php");
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
