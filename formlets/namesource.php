<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * The NameSource is used to create unique names for every input. This is 
 * needed for composability of the Formlets without the need to worry about
 * names.
 * It should only be instantiated once per process. Unsafe should only be used
 * for testing or debugging.
 */

final class NameSource {
    private $_i;
    private $_used = false;
    static private $_instantiated = false;
    
    public static function instantiate() {
        if (static::$_instantiated) {
            throw new Exception("NameSource can only be instantiated once.");
        }
        return new NameSource(0);
    } 

    public static function unsafeInstantiate() {
        return new NameSource(0);
    }

    private function __construct($i) {
        $this->_i = $i;
    }

    public function getNameAndNext() {
        if ($this->_used) {
            throw new Exception("NameSource can only be used once.");
        }

        $this->_used = true;
        return array
            ( "name" => "input".$this->_i
            , "name_source" => new NameSource($this->_i + 1)
            );
    }
}

?>
