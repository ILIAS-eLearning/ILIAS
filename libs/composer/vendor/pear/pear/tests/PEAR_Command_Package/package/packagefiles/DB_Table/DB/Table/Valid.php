<?php

/**
* 
* DB_Table_Valid validates values against DB_Table column types.
* 
* @category Database
* 
* @package DB_Table
*
* @author Paul M. Jones <pmjones@php.net>
* @author Mark Wiesemann <wiesemann@php.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Valid.php,v 1.9 2007/04/03 00:07:32 morse Exp $
*
*/

/**
* DB_Table class for constants and other globals.
*/
require_once 'DB/Table.php';


/**
* validation ranges for integers
*/
if (! isset($GLOBALS['_DB_TABLE']['valid'])) {
    $GLOBALS['_DB_TABLE']['valid'] = array(
        'smallint' => array(pow(-2, 15), pow(+2, 15) - 1),
        'integer' => array(pow(-2, 31), pow(+2, 31) - 1),
        'bigint' => array(pow(-2, 63), pow(+2, 63) - 1)
    );
}


/**
* 
* DB_Table_Valid validates values against DB_Table column types.
* 
* @category Database
* 
* @package DB_Table
* 
* @author Paul M. Jones <pmjones@php.net>
* @author Mark Wiesemann <wiesemann@php.net>
*
*/

class DB_Table_Valid {
    
    /**
    * 
    * Check if a value validates against the 'boolean' data type.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isBoolean($value)
    {
        if ($value === true || $value === false) {
            return true;
        } elseif (is_numeric($value) && ($value == 0 || $value == 1)) {
            return true;
        } else {
            return false;
        }
    }
    
    
    /**
    * 
    * Check if a value validates against the 'char' and 'varchar' data type.
    * 
    * We allow most anything here, only checking that the length is in range.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isChar($value, $colsize)
    {
    	$is_scalar = (! is_array($value) && ! is_object($value));
        $in_range = (strlen($value) <= $colsize);
        return $is_scalar && $in_range;
    }
    
    
    /**
    * 
    * Check if a value validates against the 'smallint' data type.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isSmallint($value)
    {
        return is_integer($value) &&
            ($value >= $GLOBALS['_DB_TABLE']['valid']['smallint'][0]) &&
            ($value <= $GLOBALS['_DB_TABLE']['valid']['smallint'][1]);
    }
    
    
    /**
    * 
    * Check if a value validates against the 'integer' data type.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isInteger($value)
    {
        return is_integer($value) &&
            ($value >= $GLOBALS['_DB_TABLE']['valid']['integer'][0]) &&
            ($value <= $GLOBALS['_DB_TABLE']['valid']['integer'][1]);
    }
    
    
    /**
    * 
    * Check if a value validates against the 'bigint' data type.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isBigint($value)
    {
        return is_integer($value) &&
            ($value >= $GLOBALS['_DB_TABLE']['valid']['bigint'][0]) &&
            ($value <= $GLOBALS['_DB_TABLE']['valid']['bigint'][1]);
    }
    
    
    /**
    * 
    * Check if a value validates against the 'decimal' data type.
    * 
    * For the column defined "DECIMAL(5,2)" standard SQL requires that
    * the column be able to store any value with 5 digits and 2
    * decimals. In this case, therefore, the range of values that can be
    * stored in the column is from -999.99 to 999.99.  DB_Table attempts
    * to enforce this behavior regardless of the RDBMS backend behavior.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @param string $colsize The 'size' to use for validation (to make
    * sure of min/max and decimal places).
    * 
    * @param string $colscope The 'scope' to use for validation (to make
    * sure of min/max and decimal places).
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isDecimal($value, $colsize, $colscope)
    {
        if (! is_numeric($value)) {
            return false;
        }
        
        // maximum number of digits allowed to the left
        // and right of the decimal point.
        $right_max = $colscope;
        $left_max = $colsize - $colscope;
        
        // ignore negative signs in all validation
        $value = str_replace('-', '', $value);
        
        // find the decimal point, then get the left
        // and right portions.
        $pos = strpos($value, '.');
        if ($pos === false) {
            $left = $value;
            $right = '';
        } else {
            $left = substr($value, 0, $pos);
            $right = substr($value, $pos+1);
        }
        
        // how long are the left and right portions?
        $left_len = strlen($left);
        $right_len = strlen($right);
        
        // do the portions exceed their maxes?
        if ($left_len > $left_max ||
            $right_len > $right_max) {
            // one or the other exceeds the max lengths
            return false;
        } else {
            // both are within parameters
            return true;
        }
    }
    
    
    /**
    * 
    * Check if a value validates against the 'single' data type.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isSingle($value)
    {
        return is_float($value);
    }
    
    
    /**
    * 
    * Check if a value validates against the 'double' data type.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isDouble($value)
    {
        return is_float($value);
    }
    
    
    /**
    * 
    * Check if a value validates against the 'time' data type.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isTime($value)
    {
        // hh:ii:ss
        // 01234567
        $h  = substr($value, 0, 2);
        $s1 = substr($value, 2, 1);
        $i  = substr($value, 3, 2);
        $s2 = substr($value, 5, 1);
        $s  = substr($value, 6, 2);
        
        // time check
        if (strlen($value) != 8 ||
            ! is_numeric($h) || $h < 0 || $h > 23  ||
            $s1 != ':' ||
            ! is_numeric($i) || $i < 0 || $i > 59 ||
            $s2 != ':' ||
            ! is_numeric($s) || $s < 0 || $s > 59) {
            
            return false;
            
        } else {
        
            return true;
            
        }
    }
    
    
    /**
    * 
    * Check if a value validates against the 'date' data type.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isDate($value)
    {
        // yyyy-mm-dd
        // 0123456789
        $y  = substr($value, 0, 4);
        $s1 = substr($value, 4, 1);
        $m  = substr($value, 5, 2);
        $s2 = substr($value, 7, 1);
        $d  = substr($value, 8, 2);
        
        // date check
        if (strlen($value) != 10 || $s1 != '-' || $s2 != '-' ||
            ! checkdate($m, $d, $y)) {
            
            return false;
            
        } else {
        
            return true;
            
        }
    }
    
    
    /**
    * 
    * Check if a value validates against the 'timestamp' data type.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isTimestamp($value)
    {
        // yyyy-mm-dd hh:ii:ss
        // 0123456789012345678
        $date = substr($value, 0, 10);
        $sep = substr($value, 10, 1);
        $time = substr($value, 11, 8);
        
        if (strlen($value) != 19 || $sep != ' ' ||
            ! DB_Table_Valid::isDate($date) ||
            ! DB_Table_Valid::isTime($time)) {
            
            return false;
            
        } else {
        
            return true;
            
        }
    }
    
    
    /**
    * 
    * Check if a value validates against the 'clob' data type.
    * 
    * @static
    * 
    * @access public
    * 
    * @param mixed $value The value to validate.
    * 
    * @return boolean True if the value is valid for the data type, false
    * if not.
    * 
    */
    
    function isClob($value)
    {
        return is_string($value);
    }
}

?>
