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

declare(strict_types=1);

/**
 * TableGUI class for system style to category assignments
 */
class ilSysStyleCatAssignmentTableGUI extends ilTable2GUI
{
    protected string $skin_id;
    protected string $style_id;
    protected string $sub_style_id;

    public function __construct(
        ilSystemStyleSettingsGUI $a_parent_obj,
        string $a_parent_cmd,
        string $skin_id,
        string $style_id,
        string $sub_style_id
    ) {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->skin_id = $skin_id;
        $this->style_id = $style_id;
        $this->sub_style_id = $sub_style_id;

        $this->getStyleCatAssignments();
        $this->setTitle($this->lng->txt('sty_cat_assignments'));

        $this->addColumn('', '', '1');
        $this->addColumn($this->lng->txt('obj_cat'));

        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj));
        $this->setRowTemplate('tpl.sty_cat_ass_row.html', 'Services/Style/System');

        $this->addMultiCommand('deleteAssignments', $DIC->language()->txt('remove_assignment'));
    }

    public function getStyleCatAssignments() : void
    {
        $this->setData(ilSystemStyleSettings::getSubStyleCategoryAssignments(
            $this->skin_id,
            $this->style_id,
            $this->sub_style_id
        ));
    }

    /**
     * Fill table row
     */
    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('REF_ID', $a_set['ref_id']);
        $this->tpl->setVariable(
            'CATEGORY',
            ilObject::_lookupTitle(ilObject::_lookupObjId($a_set['ref_id']))
        );
    }
}
