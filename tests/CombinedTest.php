<?php

require_once("formlets.php");
require_once("tests/FormletTest.php");

class CombinedTest extends PHPUnit_Framework_TestCase {
    use FormletTestTrait;

    public function formlets() {
        $p = _pure(_val(42));
        return array
            ( array($p->cmb($p))
            );
    }
}

?>
