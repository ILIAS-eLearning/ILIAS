<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 + This program is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program.  If not, see <http://www.gnu.org/licenses/>.
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
