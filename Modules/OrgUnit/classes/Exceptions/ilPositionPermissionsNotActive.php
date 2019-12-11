<?php

/**
 * Class ilPositionPermissionsNotActive
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilPositionPermissionsNotActive extends ilOrguException
{
    /** @var string  */
    protected $object_type = "";

    /**
     * ilPositionPermissionsNotActive constructor.
     *
     * @param string $message
     * @param string $type
     * @param int    $code
     */
    public function __construct($message, $type, $code = 0)
    {
        parent::__construct($message, $code);

        $this->object_type = $type;
    }


    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->object_type;
    }
}
