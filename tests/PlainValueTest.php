<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Formlets\Internal\Values as V;

class PlainValueTest extends PHPUnit_Framework_TestCase {
    use PlainValueTestTrait;
    
    public function plain_values() {
        $val = rand();
        $rnd = md5(rand());
        $value = V::val($val, $rnd);
        return array
            ( array($value, $val, $rnd)
            );
    }
}

?>
