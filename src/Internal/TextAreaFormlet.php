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

/* A formlet to input some text in an area. */
class TextAreaFormlet extends Formlet implements TagBuilderCallbacks {
    protected $_attributes; // string

    public function __construct($attributes = null) {
        if ($attributes !== null)
            C::guardIsArray($attributes);
        $this->_attributes = $attributes; 
    }

    public function instantiate(NameSource $name_source) {
        $res = $name_source->getNameAndNext();
        return array
            ( "builder"    => new TagBuilder( "textarea", $this, $res["name"] )
            , "collector"   => new AnyCollector($res["name"])
            , "name_source" => $res["name_source"]
            );
    }

    public function getContent(RenderDict $dict, $name) {
        return H::text("");
    }

    public function getAttributes(RenderDict $dict, $name) {
        $attributes = self::_id($this->_attributes);
        $attributes["name"] = $name; 
        return $attributes; 
    }
}

?>
