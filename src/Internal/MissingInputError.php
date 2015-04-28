<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

class MissingInputError extends Exception {
    private $_name; //string
    public function __construct($name) {
        $this->_name = $name;
        parent::__construct("Missing input $name.");
    }
}


