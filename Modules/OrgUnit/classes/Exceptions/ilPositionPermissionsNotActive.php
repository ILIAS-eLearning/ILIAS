<?php

/**
 * Class ilPositionPermissionsNotActive
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilPositionPermissionsNotActive extends ilOrguException
{
    protected string $object_type = "";

    /**
     * ilPositionPermissionsNotActive constructor.
     */
    public function __construct(string $message, string $type, int $code = 0)
    {
        parent::__construct($message, $code);

        $this->object_type = $type;
    }

    public function getObjectType(): string
    {
        return $this->object_type;
    }
}
