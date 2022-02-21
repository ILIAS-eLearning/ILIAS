<?php declare(strict_types=1);

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
        parent::__construct($a_message, (int) $a_code);
    }


    /**
     * @param $code
     */
    protected function tranlateException($code) : string
    {
        $message = 'An undefined Database Exception occured';
        if ($code === static::DB_GENERAL) {
            $message = 'An undefined Database Exception occured';
        }

        return $message . '. ';
    }
}
