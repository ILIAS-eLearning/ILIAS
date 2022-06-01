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
 * TableGUI class for role assignments
 * @author Fabian Wolf <wolf@leifos.com>
 */
class ilCategoryAssignRoleTableGUI extends ilTable2GUI
{
    public function __construct(
        ilObjCategoryGUI $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->setId("ilcatluaar");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn("", "", "", true, "4%");
        $this->addColumn($lng->txt("title"), "title", "35%");
        $this->addColumn($lng->txt("description"), "desc", "45%");
        $this->addColumn($lng->txt("type"), "type", "16%");
        
        $this->addMultiCommand('assignSave', $lng->txt("change_assignment"));
    
        $ilCtrl->saveParameter($a_parent_obj, 'obj_id');
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.cat_role_assignment.html", "Modules/Category");
        $this->setDefaultOrderDirection("asc");
        $this->setShowRowsSelector(false);
        $this->setLimit(999999);
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("INPUT_CHCKBX", $a_set["checkbox"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", $a_set["desc"]);
        $this->tpl->setVariable("TXT_TYPE", $a_set["type"]);
    }
}
