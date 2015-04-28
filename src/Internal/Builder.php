<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

/******************************************************************************
 * Fairly simple implementation of a Builder. Can render strings and supports
 * combining of builders. A more sophisticated version could be build upon
 * HTML primitives.
 */

abstract class Builder {
    /* Returns HTML. */
    abstract public function buildWithDict(RenderDict $dict);
    public function build() {
        return $this->buildWithDict(RenderDict::_empty());
    }

    /**
     * Map a transformation over the result of the Builder. The transformation
     * gets the used RenderDict and the HTML result of the Builder and should
     * return a new HTML.
     */
    public function map(FunctionValue $transformation) {
        return new MappedBuilder($this, $transformation);
    } 
}
   

