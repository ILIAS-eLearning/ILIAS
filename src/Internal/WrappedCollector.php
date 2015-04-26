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

/* A collector where a wrapper is around an underlying collect. */
class WrappedCollector extends Collector {
    private $_collector; // Collector
    private $_wrapper; // FunctionValue

    public function __construct(Collector $collector, FunctionValue $wrapper) {
        C::guardHasArity($wrapper, 2);
        if ($collector->isNullaryCollector()) {
            throw new Exception("It makes no sense to wrap around a nullary collector.");
        }
        $this->_collector = $collector;
        $this->_wrapper = $wrapper;
    }

    public function collect($inp) {
        $wrapped = $this->_wrapper
                        ->apply(V::val($this->_collector))
                        ->apply(V::val($inp));
        return $wrapped->force();
    }

    public function isNullaryCollector() {
        return false;
    }
}

?>
