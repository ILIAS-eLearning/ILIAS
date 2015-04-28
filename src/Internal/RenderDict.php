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

/******************************************************************************
 * Turn a value to two dictionaries:
 *  - one contains the original values as inputted by the user.
 *  - one contains the origins and the errors on those values.
 */
class RenderDict {
    private $_values; // array
    private $_errors; // array
    private $_empty; // bool 

    public function isEmpty() {
        return $this->_empty;
    }

    public function value($name) {
        if ($this->valueExists($name))
            return $this->_values[$name];
        return null;
    }

    public function valueExists($name) {
        return array_key_exists($name, $this->_values);
    }

    public function errors($name) {
        if (array_key_exists($name, $this->_errors))
            return $this->_errors[$name];
        return null;
    }

    public function __construct($inp, Value $value, $_empty = false) {
        C::guardIsBool($_empty);
        $this->_values = $inp; 
        $value = $value->force();
        if ($value instanceof ErrorValue) {
            $this->_errors = $value->toDict();
        }
        else {
            $this->_errors = array(); 
        }
        $this->_empty = $_empty;
    }

    private static $_emptyInst = null;

    public static function _empty() {
        // ToDo: Why does this not work?
        /*if (self::_emptyInst === null) {
            self::_emptyInst = new RenderDict(_val(0));
        }
        return self::_emptyInst;*/
        return new RenderDict(array(), V::val(0), true);
    }  
}
    

