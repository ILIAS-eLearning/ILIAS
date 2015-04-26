<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

class HTMLText extends HTML {
    private $_text; // string

    public function text($text = null) {
        if ($text === null) {
            return $this->_text;
        }

        guardIsString($text);
        $this->_text = $text;
    }

    public function __construct($text) {
        guardIsString($text);
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

?>
