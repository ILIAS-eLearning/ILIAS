<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

/* A collector that always returns a constant value. */
final class ConstCollector extends Collector {
    private $_value; // Value

    public function __construct(Value $value) {
        $this->_value = $value;
    }

    public function collect($inp) {
        return $this->_value;
    }

    public function isNullaryCollector() {
        return false;
    }
}

?>
