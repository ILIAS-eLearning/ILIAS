<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * TableGUI class for table NewsForContext
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSCORM2004TrackingTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;


    public function __construct(object $a_parent_obj, string $a_parent_cmd = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn("", "f", "1");
        $this->addColumn($this->lng->txt("user"), "user_full_name", "100%");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.table_scorm_2004_tracking_row.html",
            "Modules/Scorm2004"
        );
        $this->setDefaultOrderField("user_full_name");
        $this->addMultiCommand("deleteTrackingData", $this->lng->txt("cont_delete_track_data"));
        $this->setSelectAllCheckbox("id");
    }

    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow(array $a_set) : void
    {
//        $lng = $this->lng;
//        $ilCtrl = $this->ctrl;
//        $ilAccess = $this->access;
        
        $this->tpl->setVariable("USER_NAME", $a_set["user_full_name"]);
        $this->tpl->setVariable("USER_ID", $a_set["user_id"]);
    }
}
