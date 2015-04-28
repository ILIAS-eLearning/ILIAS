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

/* A simple html input. */
class InputFormlet extends Formlet implements TagBuilderCallbacks {
    protected $_attributes;

    public function __construct($attributes) {
        C::guardEachAndKeys($attributes, "guardIsString", "guardIsString");
        $this->_attributes = $attributes;
    }

    public function instantiate(NameSource $name_source) {
        $res = $name_source->getNameAndNext();
        return array
            ( "builder"    => new TagBuilder( "input", $this, $res["name"])
            , "collector"   => new AnyCollector($res["name"])
            , "name_source" => $res["name_source"]
            );
    }

    public function getContent(RenderDict $dict, $name) {
        return null; 
    }

    public function getAttributes(RenderDict $dict, $name) {
        $attributes = self::_id($this->_attributes);
        $attributes["name"] = $name; 
        return $attributes; 
    }
}


