<?php
/**
*
* Class to report exception
*
*
*/

include_once 'Services/Exceptions/classes/class.ilException.php';

class ilFileUtilsException extends ilException
{
    public static $BROKEN_FILE = 0;
    public static $INFECTED_FILE = 1;
    public static $DOUBLETTES_FOUND = 2;
    /**
     * A message isn't optional as in build in class Exception
     *
     * @access public
     *
     */
    public function __construct($a_message, $a_code = 0)
    {
        parent::__construct($a_message, $a_code);
    }
}
