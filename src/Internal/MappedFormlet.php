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
use Lechimp\Formlets\Internal\Values as V;

/* A formlet where a function is applied to buiid builder and collector. */
class MappedFormlet extends Formlet {
    private $_formlet; // Formlet
    private $_transform_builder; // FunctionValue 
    private $_transform_collector; // FunctionValue 
    
    public function __construct( Formlet $formlet
                               , FunctionValue $transform_builder
                               , FunctionValue $transform_collector ) {
        C::guardHasArity($transform_builder, 1);
        C::guardHasArity($transform_collector, 1);
        $this->_formlet = $formlet;
        $this->_transform_builder = $transform_builder; 
        $this->_transform_collector = $transform_collector;
    }

    public function instantiate(NameSource $name_source) {
        $fmlt = $this->_formlet->instantiate($name_source);
        $b = $this->_transform_builder
                ->apply(V::val($fmlt["builder"]))
                ->get();
        $c = $this->_transform_collector
                ->apply(V::val($fmlt["collector"]))
                ->get();
        return array( "builder"    => $b
                    , "collector"   => $c
                    , "name_source" => $fmlt["name_source"]
                    );
    }
}


