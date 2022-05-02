<?php declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base file handler class for appointment classes
 * @author Alex Killing <killing@leifos.de>
 * @ingroup
 */
class ilAppointmentBaseFileHandler
{
    protected array $appointment;

    protected ilLogger $logger;
    protected ilAccessHandler $access;
    protected ilRbacSystem $rbacsystem;
    protected ilObjUser $user;

    public function __construct(array $a_appointment)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->logger = $DIC->logger()->cal();
        $this->appointment = $a_appointment;
    }

    public function getCatId(int $a_entry_id) : int
    {
        return ilCalendarCategoryAssignments::_lookupCategory($a_entry_id);
    }

    /**
     * @return array
     */
    public function getCatInfo() : array
    {
        $cat_id = $this->getCatId($this->appointment['event']->getEntryId());

        $cat = ilCalendarCategory::getInstanceByCategoryId($cat_id);
        $cat_info = array();
        $cat_info["type"] = $cat->getType();
        $cat_info["obj_id"] = $cat->getObjId();
        $cat_info["title"] = $cat->getTitle();
        $cat_info["cat_id"] = $cat_id;
        $cat_info["editable"] = false;

        switch ($cat_info["type"]) {
            case ilCalendarCategory::TYPE_USR:
                if ($cat_info["obj_id"] == $this->user->getId()) {
                    $cat_info["editable"] = true;
                }
                break;

            case ilCalendarCategory::TYPE_OBJ:
                $obj_type = ilObject::_lookupType($cat_info["obj_id"]);
                if ($obj_type == 'crs' or $obj_type == 'grp') {
                    if (ilCalendarSettings::_getInstance()->lookupCalendarActivated($cat_info["obj_id"])) {
                        foreach (ilObject::_getAllReferences($cat_info["obj_id"]) as $ref_id) {
                            if ($this->access->checkAccess('edit_event', '', $ref_id)) {
                                $cat_info["editable"] = true;
                            }
                        }
                    }
                }
                break;

            case ilCalendarCategory::TYPE_GLOBAL:
                if ($this->rbacsystem->checkAccess(
                    'edit_event',
                    ilCalendarSettings::_getInstance()->getCalendarSettingsId()
                )) {
                    $cat_info["editable"] = true;
                }
                break;
        }
        return $cat_info;
    }
}
