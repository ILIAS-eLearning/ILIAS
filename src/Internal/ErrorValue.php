<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 *
 */

namespace Lechimp\Formlets\Internal;

use Lechimp\Formlets\IValue;
use Lechimp\Formlets\Internal\Checking as C;

/* Value representing an error. */
final class ErrorValue extends Value {
    private $_reason; // string
    private $_others; // array of other errors
    private $_dict; // dictionary with errors or null

    public function others() {
        return $this->_others;
    }

    public function __construct($reason, $origin, $others = array()) {
        C::guardIsString($reason);
        C::guardEach($others, "guardIsErrorValue");
        $this->_reason = $reason;
        $this->_others = $others;
        $this->_dict = null;
        
        parent::__construct($origin);
    }

    public function get() {
        throw new GetError("ErrorValue");
    } 

    public function apply(IValue $to) {
        return $this;
    }

    public function catchAndReify($exc_class) {
        return $this;
    }

    public function isApplicable() {
        return true;
    }

    public function force() {
        return $this;
    }

    public function isError() {
        return true;
    }

    public function error() {
        return $this->_reason;
    }
    
    /**
     * Get a dictionary of the errors that lead to this error in the form of
     * origin => [error]. If error has more than one origin, the origins are
     * merge together to one string separated by ";".
     */
    public function toDict() {
        if ($this->_dict !== null) {
            return $this->_dict;
        }

        $_dict = array();

        // Record error for the origin of this error.
        $_dict[$this->origin()] = array($this->error());

        // Get all errors contained in others 
        array_map( function($err) use (&$_dict) {
                $d = $err->toDict();

                // Insert each origin/errors pair in our result
                // array.
                foreach($d as $o => $es) {
                    if (!isset($_dict[$o])) {
                        $_dict[$o] = array(); 
                    }
                    foreach($es as $e) {
                        $_dict[$o][] = $e;
                    }
                }    
            }
            , $this->_others
            );

        $this->_dict = $_dict;
        return $this->_dict;
    }
}

?>
