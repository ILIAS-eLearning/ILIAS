<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

/* A collector that collects nothing and will be dropped by apply collectors. */
final class NullaryCollector extends Collector {
    public function collect($inp) {
        throw new Exception("NullaryCollector::collect: This should never be called.");
    }
    public function isNullaryCollector() {
        return true;
    }
}


