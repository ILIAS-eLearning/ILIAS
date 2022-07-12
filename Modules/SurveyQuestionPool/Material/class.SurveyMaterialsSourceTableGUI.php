<?php

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
 * TableGUI class for survey question source materials
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class SurveyMaterialsSourceTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        string $a_cancel_cmd
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
                
        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("action"), "");
        $this->setTitle($this->lng->txt('select_object_to_link'));
        
        $this->setLimit(9999);
        $this->disable("numinfo");
                
        $this->setRowTemplate("tpl.il_svy_qpl_material_source_row.html", "Modules/SurveyQuestionPool");

        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), $this->getParentCmd()));
        $this->addCommandButton($a_cancel_cmd, $this->lng->txt('cancel'));
        
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
    }
    
    protected function fillRow(array $a_set) : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $url_cmd = "add" . strtoupper($a_set["item_type"]);
        $url_type = strtolower($a_set["item_type"]);
    
        $ilCtrl->setParameter($this->getParentObject(), $url_type, $a_set["item_id"]);
        $url = $ilCtrl->getLinkTarget($this->getParentObject(), $url_cmd) .
        $ilCtrl->setParameter($this->getParentObject(), $url_type, "");
    
        $this->tpl->setVariable("TITLE", $a_set['title']);
        $this->tpl->setVariable("URL_ADD", $url);
        $this->tpl->setVariable("TXT_ADD", $lng->txt("add"));
    }
}
