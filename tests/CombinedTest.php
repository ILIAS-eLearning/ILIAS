<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Formlets\Internal\Formlet as F;
use Lechimp\Formlets\Internal\Values as V;

class CombinedTest extends PHPUnit_Framework_TestCase {
    use FormletTestTrait;

    public function formlets() {
        $p = F::pure(V::val(42));
        return array
            ( array($p->cmb($p))
            );
    }
}

?>
