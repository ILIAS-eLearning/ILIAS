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
 * Class ilObjStudyProgrammeAutoCategoriesGUI
 *
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilStudyProgrammeAutoCategoriesTableGUI extends ilTable2GUI
{
    public function __construct(
        ilObjStudyProgrammeAutoCategoriesGUI $a_parent_obj,
        string $a_parent_cmd = "",
        string $a_template_context = ""
    ) {
        $this->setId("sp_ac_list");
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

        $this->setTitle($this->lng->txt('content_automation_title'));
        $this->setEnableTitle(true);
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setExternalSorting(false);
        $this->setExternalSegmentation(true);
        $this->setRowTemplate("tpl.autocats_table_row.html", "Modules/StudyProgramme");
        $this->setShowRowsSelector(false);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, "view"));
        $this->disable('sort');

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('last_edited_by'), 'editor');
        $this->addColumn($this->lng->txt('last_edited'), 'last');
        $this->addColumn($this->lng->txt(''), 'actions');

        $this->setSelectAllCheckbox(ilObjStudyProgrammeAutoCategoriesGUI::CHECKBOX_CATEGORY_REF_IDS . '[]');
        $this->setEnableAllCommand(true);
        $this->addMultiCommand('deleteConfirmation', $this->lng->txt('delete'));
    }

    protected function fillRow(array $a_set) : void
    {
        [$ac, $title, $usr, $actions] = $a_set;

        $this->tpl->setVariable("ID", $ac->getCategoryRefId());
        $this->tpl->setVariable("TITLE", $title);
        $this->tpl->setVariable("EDITOR", $usr);
        $this->tpl->setVariable("LAST_EDITED", $ac->getLastEdited()->format('Y/m/d H:i:s'));
        $this->tpl->setVariable("ACTIONS", $actions);
    }
}
