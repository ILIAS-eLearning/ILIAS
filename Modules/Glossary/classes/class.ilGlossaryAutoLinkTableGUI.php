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
 * TableGUI class for auto link glossaries
 * @author Alexander Killing <killing@leifos.de>
 */
class ilGlossaryAutoLinkTableGUI extends ilTable2GUI
{
    protected ilObjGlossary $glossary;

    public function __construct(
        ilObjGlossary $a_glossary,
        object $a_parent_obj,
        string $a_parent_cmd
    ) {
        global $DIC;
        
        $this->glossary = $a_glossary;
        $this->id = "glo_glo";
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $data = array();
        foreach ($a_glossary->getAutoGlossaries() as $glo_id) {
            $data[] = array("glo_id" => $glo_id, "title" => ilObject::_lookupTitle($glo_id));
        }
        $this->setData($data);
        $this->setTitle($this->lng->txt("cont_auto_glossaries"));
        
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.glo_glossary_auto_link_row.html", "Modules/Glossary");
    }
    
    protected function fillRow(array $a_set) : void
    {
        $this->ctrl->setParameter($this->parent_obj, "glo_id", $a_set["glo_id"]);
        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("CMD_HREF", $this->ctrl->getLinkTarget($this->parent_obj, "removeGlossary"));
        $this->tpl->setVariable("CMD_TXT", $this->lng->txt("remove"));
        $this->tpl->parseCurrentBlock();
        $this->tpl->setVariable("TITLE", ilObject::_lookupTitle($a_set["glo_id"]));
    }
}
