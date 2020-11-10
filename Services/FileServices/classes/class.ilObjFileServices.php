<?php

/**
 * Class ilObjFileServices
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 */
class ilObjFileServices extends ilObject
{
    /**
     * ilObjFileServices constructor.
     * @param int  $id
     * @param bool $call_by_reference
     */
    public function __construct($id = 0, bool $call_by_reference = true)
    {
        $this->type = "fils";
        parent::__construct($id, $call_by_reference);
    }

    /**
     * @inheritDoc
     */
    public function getPresentationTitle()
    {
        return $this->lng->txt("file_services");
    }

    /**
     * @inheritDoc
     */
    public function getLongDescription()
    {
        return $this->lng->txt("file_services_description");
    }
}