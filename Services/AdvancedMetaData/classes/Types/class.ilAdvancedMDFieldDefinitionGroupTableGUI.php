<?php

declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Table GUI for complex AdvMD options
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionGroupTableGUI extends ilTable2GUI
{
    protected ilAdvancedMDFieldDefinition $def;

    public function __construct($a_parent_obj, $a_parent_cmd, ilAdvancedMDFieldDefinition $a_def)
    {
        $this->def = $a_def;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn($this->lng->txt("option"), "option");

        foreach ($this->def->getTitles() as $element => $title) {
            $this->addColumn($title, $element);
        }

        $this->addColumn($this->lng->txt("action"), "");

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.edit_complex_row.html", "Services/AdvancedMetaData");
        $this->setDefaultOrderField("option");
        $this->initItems($a_def);
    }

    protected function initItems(ilAdvancedMDFieldDefinition $a_def): void
    {
        $data = array();

        foreach ($a_def->getOptions() as $option) {
            $item = array("option" => $option);

            $a_def->exportOptionToTableGUI($option, $item);

            $data[] = $item;
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("OPTION", $a_set["option"]);

        $this->tpl->setCurrentBlock("field_bl");
        foreach (array_keys($this->def->getTitles()) as $element) {
            $this->tpl->setVariable("FIELD", trim($a_set[$element]));
            $this->tpl->parseCurrentBlock();
        }

        // action
        $this->ctrl->setParameter($this->getParentObject(), "oid", md5($a_set["option"]));
        $url = $this->ctrl->getLinkTarget($this->getParentObject(), "editComplexOption");
        $this->ctrl->setParameter($this->getParentObject(), "oid", "");

        $this->tpl->setVariable("ACTION_URL", $url);
        $this->tpl->setVariable("ACTION_TXT", $this->lng->txt("edit"));
    }
}
