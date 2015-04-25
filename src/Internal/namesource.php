<?php

/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

/*
 * The NameSource is used to create unique names for every input. This is 
 * needed for composability of the Formlets without the need to worry about
 * names.
 * It should only be instantiated once per process. Unsafe should only be used
 * for testing or debugging.
 */

final class NameSource {
    private $_i;
    private $_used = false;
    private $_prefix;
    static private $_instantiated = false;
    
    public static function instantiate($prefix) {
        if (static::$_instantiated) {
            throw new Exception("NameSource can only be instantiated once.");
        }
        return new NameSource(0, $prefix);
    } 

    private function __construct($i, $prefix) {
        $this->_i = $i;
        $this->_prefix = $prefix;
    }

    public function getNameAndNext() {
        if ($this->_used) {
            throw new Exception("NameSource can only be used once.");
        }

        $this->_used = true;
        return array
            ( "name" => $this->_prefix."_input_".$this->_i
            , "name_source" => new NameSource($this->_i + 1, $this->_prefix)
            );
    }
}

?>
