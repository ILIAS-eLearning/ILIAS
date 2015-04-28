<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

use Lechimp\Formlets\Internal\HTML as H;

/* Builder that combines two sub builders by adding the output of the 
 * builders.
 */
class CombinedBuilder extends Builder {
    private $_l; // Builder
    private $_r; // Builder

    public function __construct(Builder $left, Builder $right) {
        $this->_l = $left;
        $this->_r = $right;
    }

    public function buildWithDict(RenderDict $dict) {
        return H::concat( $this->_l->buildWithDict($dict)
                          , $this->_r->buildWithDict($dict)
                          );
    }
}
   

