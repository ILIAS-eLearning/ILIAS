<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets;

/**
 * A formlet represents one part of a form. It can be combined with other formlets
 * to yield new formlets. Formlets are immutable, that is they can be reused in
 * as many places as you like. All methods return fresh Formlets instead of muting
 * the Formlets they are called upon.
 */
interface IFormlet {
    /**
     * Combined the formlet with another formlet and get a new formlet. Will apply 
     * a function value in this formlet to any value in the other formlet.
     *
     * @return  IFormlet
     */
    public function cmb(IFormlet $formlet);

    /**
     * Get a new formlet with an additional check of a predicate on the input to 
     * the formlet and an error message for the case the predicate fails. The 
     * predicates has to be a function from mixed to bool.
     * 
     * @param   IValue  $predicate
     * @param   string  $error
     * @return  IFormlet
     */
    public function satisfies(IValue $predicate, $error);

    /**
     * Map a function over the input value.
     *
     * @return IFormlet 
     */
    public function map(IValue $transformation);
}

?>
