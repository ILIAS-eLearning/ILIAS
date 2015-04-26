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
use Lechimp\Formlets\Internal\Values as V;
use Lechimp\Formlets\Internal\Checking as C;

/* Builder where a function is mapped over the result of another builder. */
class MappedBuilder extends Builder {
    private $_builder; // Builder
    private $_transformation; // FunctionValue 

    public function __construct(Builder $builder, FunctionValue $transformation) {
        C::guardHasArity($transformation, 2);
        $this->_builder = $builder;
        $this->_transformation = $transformation;
    }

    public function buildWithDict(RenderDict $dict) {
        $base = $this->_builder->buildWithDict($dict);
        $res = $this->_transformation
                ->apply(V::val($dict))
                ->apply(V::val($base))
                ->get();
        C::guardIsHTML($res);
        return $res;
    }
}
    
?>
