<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

class HTMLNop extends HTML {
    public function render() {
        return "";
    }
    
    public function goDepth( FunctionValue $predicate
                           , FunctionValue $transformation) {
        return null;
    }
}


