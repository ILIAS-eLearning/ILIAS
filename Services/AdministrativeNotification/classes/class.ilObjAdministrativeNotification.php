<?php

/**
 * Class ilObjAdministrativeNotification
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjAdministrativeNotification extends ilObject
{

    /**
     * ilObjAdministrativeNotification constructor.
     * @param int  $id
     * @param bool $call_by_reference
     */
    public function __construct($id = 0, bool $call_by_reference = true)
    {
        $this->type = "adn";
        parent::__construct($id, $call_by_reference);
    }

    /**
     * @inheritDoc
     */
    public function getPresentationTitle() : string
    {
        return $this->lng->txt("obj_adn");
    }

    /**
     * @inheritDoc
     */
    public function getLongDescription() : string
    {
        return $this->lng->txt("administrative_notification_description");
    }
}
