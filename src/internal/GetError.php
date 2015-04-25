<?php

/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 *
 */

namespace Lechimp\Formlets\Internal;

class GetError extends Exception {
    public function __construct($what) {
        parent::__construct("Can't get value from $what");
    }
}

?>
