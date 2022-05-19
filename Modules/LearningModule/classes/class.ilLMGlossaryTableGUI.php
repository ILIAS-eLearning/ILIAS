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
 * TableGUI class for glossary tables
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMGlossaryTableGUI extends ilTable2GUI
{
    protected ilObjLearningModule $lm;
    protected ilAccessHandler $access;

    public function __construct(
        ilObjLearningModule $a_lm,
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->lm = $a_lm;
        $this->id = "lm_glo";
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $data = array();
        foreach ($a_lm->getAutoGlossaries() as $glo_id) {
            $data[] = array("glo_id" => $glo_id, "title" => ilObject::_lookupTitle($glo_id));
        }
        $this->setData($data);
        $this->setTitle($lng->txt("cont_auto_glossaries"));
        
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.lm_glossary_row.html", "Modules/LearningModule");
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, "glo_id", $a_set["glo_id"]);
        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("CMD_HREF", $ilCtrl->getLinkTarget($this->parent_obj, "removeLMGlossary"));
        $this->tpl->setVariable("CMD_TXT", $lng->txt("remove"));
        $this->tpl->parseCurrentBlock();
        
        $this->tpl->setVariable("TITLE", ilObject::_lookupTitle($a_set["glo_id"]));
    }
}
