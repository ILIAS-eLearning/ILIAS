<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base file handler class for appointment classes
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup
 */
class ilAppointmentBaseFileHandler
{
    protected $appointment;

    /**
     * Constructor
     *
     * @param
     */
    public function __construct($a_appointment)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();

        $this->appointment = $a_appointment;
    }


    /**
     * Get instance
     * @return self
     */
    public static function getInstance($a_appointment)
    {
        return new static($a_appointment);
    }

    public function getCatId($a_entry_id)
    {
        return ilCalendarCategoryAssignments::_lookupCategory($a_entry_id);
    }

    public function getCatInfo()
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
                if ($this->rbacsystem->checkAccess('edit_event', ilCalendarSettings::_getInstance()->getCalendarSettingsId())) {
                    $cat_info["editable"] = true;
                }
                break;
        }

        return $cat_info;
    }
}
