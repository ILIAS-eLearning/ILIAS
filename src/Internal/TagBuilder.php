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
use Lechimp\Formlets\Internal\Checking as C;

/* Builds a simple html tag. */
class TagBuilder extends Builder {
    private $_tag_name; // string
    private $_callback_object; // object
    private $_name; // string

    public function __construct( $tag_name, TagBuilderCallbacks $callback_object, $name = null) {  
        C::guardIsString($tag_name);
        C::guardIfNotNull($name, "guardIsString");
        $this->_tag_name = $tag_name;
        $this->_callback_object = $callback_object;
        $this->_name = $name;
    }

    public function buildWithDict(RenderDict $dict) {
        $attributes = $this->_callback_object->getAttributes($dict, $this->_name);
        $content = $this->_callback_object->getContent($dict, $this->_name);
        return H::tag($this->_tag_name, $attributes, $content); 
    }
}
    
?>
