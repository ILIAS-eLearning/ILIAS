<?php
class PHP_Dump {
    var $_var;
    function __construct($var)
    {
        $this->_var = $var;
    }
    
    function toPHP()
    {
        return var_export($this->_var, true);
    }
}
?>
