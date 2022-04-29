<?php declare(strict_types=1);

/**
 * Class ilDatabaseException
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDatabaseException extends ilException
{
    public const DB_GENERAL = 10000;


    public function __construct(string $a_message, int $a_code = self::DB_GENERAL)
    {
        $a_message = $this->tranlateException($a_code) . $a_message;
        parent::__construct($a_message, $a_code);
    }


    protected function tranlateException(int $code) : string
    {
        $message = 'An undefined Database Exception occured';
        if ($code === static::DB_GENERAL) {
            $message = 'An undefined Database Exception occured';
        }

        return $message . '. ';
    }
}
