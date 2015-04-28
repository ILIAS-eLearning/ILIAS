<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

use Lechimp\Formlets\Internal\Checking as C;
use Lechimp\Formlets\Internal\Values as V;

/* A collector that collects an input by name. */
final class AnyCollector extends Collector {
    private $_name; // string

    protected function name() {
        return $this->_name;
    }
    
    public function __construct($name) {
        C::guardIsString($name);
        $this->_name = $name;
    }

    public function collect($inp) {
        $name = $this->name();
        if (!array_key_exists($name, $inp)) {
            throw new MissingInputError($this->name());
        }
        return V::val($inp[$name], $name);
    }

    public function isNullaryCollector() {
        return false;
    }
}


