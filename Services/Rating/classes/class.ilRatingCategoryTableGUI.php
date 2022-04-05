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
 * List rating categories
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilRatingCategoryTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_parent_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->setId("rtgcat");

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn($this->lng->txt("position"));
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("description"), "description");
        $this->addColumn($this->lng->txt("actions"));

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.rating_category_row.html", "Services/Rating");
        
        $this->addCommandButton("updateorder", $lng->txt("rating_update_positions"));
        
        $this->getItems($a_parent_id);
    }

    // Build item rows for given object and filter(s)
    public function getItems(int $a_parent_obj_id)
    {
        $data = ilRatingCategory::getAllForObject($a_parent_obj_id);
        
        $this->setMaxCount(sizeof($data));
        $this->setData($data);
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_POS", $a_set["pos"]);
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", nl2br($a_set["description"]));
        
        $ilCtrl->setParameter($this->parent_obj, "cat_id", $a_set["id"]);
        
        $items = array();
        $items["edit"] = array($lng->txt("edit"), $ilCtrl->getLinkTarget($this->parent_obj, "edit"));
        $items["delete"] = array($lng->txt("delete"), $ilCtrl->getLinkTarget($this->parent_obj, "confirmDelete"));
        
        $this->tpl->setCurrentBlock("actions");
        foreach ($items as $item) {
            $this->tpl->setVariable("ACTION_CAPTION", $item[0]);
            $this->tpl->setVariable("ACTION_LINK", $item[1]);
            $this->tpl->parseCurrentBlock();
        }
    }
}
