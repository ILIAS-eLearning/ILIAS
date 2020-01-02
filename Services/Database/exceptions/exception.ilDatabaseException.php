<?php

require_once("./Services/Exceptions/classes/class.ilException.php");

/**
 * Class ilDatabaseException
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDatabaseException extends ilException
{
    const DB_GENERAL = 10000;


    /**
     * ilDatabaseException constructor.
     *
     * @param string $a_message
     * @param int $a_code
     */
    public function __construct($a_message, $a_code = self::DB_GENERAL)
    {
        $a_message = $this->tranlateException($a_code) . $a_message;
        parent::__construct($a_message, $a_code);
    }


    /**
     * @param $code
     * @return string
     */
    protected function tranlateException($code)
    {
        $message = 'An undefined Database Exception occured';
        switch ($code) {
            case static::DB_GENERAL:
                $message = 'An undefined Database Exception occured';
                break;
        }

        return $message . '. ';
    }
}
