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
 * TableGUI class for survey question materials
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyMaterialsTableGUI extends ilTable2GUI
{
    private int $counter;
    private bool $write_access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        bool $a_write_access = false
    ) {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->write_access = $a_write_access;
        $this->counter = 1;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->setFormName('evaluation_all');
        $this->setStyle('table', 'fullwidth');
        $this->addColumn('', 'f', '1%');
        $this->addColumn($lng->txt("type"), "type", "");
        $this->addColumn($lng->txt("material"), "material", "");
        $this->setTitle($this->lng->txt('materials'));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.il_svy_qpl_material_row.html", "Modules/SurveyQuestionPool");
        $this->setPrefix('idx');
        $this->setSelectAllCheckbox('idx');
        $this->disable('sort');
        $this->enable('header');

        if ($this->write_access) {
            $this->addMultiCommand('deleteMaterial', $this->lng->txt('remove'));
        }
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("TYPE", $a_set['type']);
        $this->tpl->setVariable("TITLE", $a_set['title']);
        $this->tpl->setVariable("HREF", $a_set['href']);
        $this->tpl->setVariable("CHECKBOX_VALUE", $this->counter - 1);
        $this->tpl->setVariable("COUNTER", $this->counter++);
    }
}
