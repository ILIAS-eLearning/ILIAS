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

class HTMLText extends HTML {
    private $_text; // string

    public function content($text = null) {
        if ($text === null) {
            return $this->_text;
        }

        C::guardIsString($text);
        $this->_text = $text;
    }

    public function __construct($text) {
        C::guardIsString($text);
        $this->_text = $text;
    }

    public function render() {
        return $this->_text;
    }
    
    public function goDepth( FunctionValue $predicate
                           , FunctionValue $transformation) {
        return null;
    }
}


