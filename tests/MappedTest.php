<?php

require_once("formlets.php");
require_once("tests/FormletTest.php");

class MappedTest extends PHPUnit_Framework_TestCase {
    use FormletTestTrait;

    public function formlets() {
        $id = _fn(function($a) { return $a; });
        $id2 = _fn(function($_, $a) { return $a; });
        $pure = _pure(_val(42));
        return array
            ( array($pure->map($id))
            , array($pure->mapHTML($id2))
            , array($pure->mapBC($id, $id))
            );
    }
}

?>
