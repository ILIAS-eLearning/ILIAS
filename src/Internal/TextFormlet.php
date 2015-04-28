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

/* A formlet collecting nothing and building a constant string. */
class TextFormlet extends Formlet {
    private $_content; // string

    public function __construct($content) { 
        C::guardIsString($content);
        $this->_content = $content;
    }

    public function instantiate(NameSource $name_source) {
        return array
            ( "builder"    => new TextBuilder($this->_content)
            , "collector"   => new NullaryCollector()
            , "name_source" => $name_source
            );
    }
}


