<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

require_once("src/formlets.php");
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
