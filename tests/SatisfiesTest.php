<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Formlets\Internal\Formlet as F;
use Lechimp\Formlets\Internal\Values as V;

class SatisfiesTest extends PHPUnit_Framework_TestCase {
    use FormletTestTrait;

    public function formlets() {
        $alwaysTrue = V::fn(function ($_) { return true; });
        $pure = F::pure(V::val(42));
        return array
            ( array($pure->satisfies($alwaysTrue, "ERROR"))
            );
    }
}

?>
