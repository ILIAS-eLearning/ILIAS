<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 *
 */

namespace Lechimp\Formlets\Internal;

use Exception;

/**
 * TypeErrors are needed to find typing problems that aren't revealed by PHPs 
 * type hinting. 
 */
class TypeError extends Exception {
    private $_expected;
    private $_found;

    public function __construct($expected, $found) {
        $this->_expected = $expected;
        $this->_found = $found;

        parent::__construct("Expected $expected, found $found.");
    }
}

?>
