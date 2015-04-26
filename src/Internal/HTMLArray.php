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

class HTMLArray extends HTML {
    private $_content; // array of HTML

    public function __construct($content) {
        C::guardEach($content, "guardIsHTML");
        $this->_content = $content;
    }

    public function content() {
        return $_content;
    }

    public function render() {
        $res = "";
        foreach ($this->_content as $cont) {
            $res .= $cont->render();
        }
        return $res;
    }

    public function cat(HTML $other) {
        if ($other instanceof HTMLArray) {
            $this->_content = array_merge($this->_content, $other->content());
            return $this;
        }
        $this->_content[] = $other;
        return $this;
    }
    
    public function goDepth( FunctionValue $predicate
                           , FunctionValue $transformation) {
        // Code start HERE!!
        foreach ($this->_content as $content) {
            $res = $content->depthFirst($predicate, $transformation);
            if ($res !== null) {
                return $res;
            }  
        }
        return null;
    }
}

?>
