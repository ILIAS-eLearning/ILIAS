<?php
class ilFileException extends ilException
{
    public static $ID_MISMATCH = 0;
    public static $ID_DEFLATE_METHOD_MISMATCH = 1;
    public static $DECOMPRESSION_FAILED = 2;


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
