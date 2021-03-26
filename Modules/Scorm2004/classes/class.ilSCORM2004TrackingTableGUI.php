<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for table NewsForContext
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSCORM2004TrackingTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;


    public function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn("", "f", "1");
        $this->addColumn($lng->txt("user"), "user_full_name", "100%");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.table_scorm_2004_tracking_row.html",
            "Modules/Scorm2004"
        );
        $this->setDefaultOrderField("user_full_name");
        $this->addMultiCommand("deleteTrackingData", $lng->txt("cont_delete_track_data"));
        $this->setSelectAllCheckbox("id");
    }

    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        
        $this->tpl->setVariable("USER_NAME", $a_set["user_full_name"]);
        $this->tpl->setVariable("USER_ID", $a_set["user_id"]);
    }
}
