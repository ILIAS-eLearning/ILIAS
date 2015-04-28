<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

/* A collector that applies the input from its left collector to the input
 * from its right collector.
 */
final class ApplyCollector extends Collector {
    private $_l;
    private $_r;

    public function __construct(Collector $left, Collector $right) {
        $this->_l = $left;
        $this->_r = $right;
    }

    public function collect($inp) {
        $l = $this->_l->collect($inp);
        $r = $this->_r->collect($inp);
        return $l->apply($r);
    }

    public function isNullaryCollector() {
        return false;
    }
}


