<?php

declare(strict_types=1);

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
* Class ilStudyProgrammeAutoMembershipsTableGUI
*
* @author: Nils Haagen <nils.haagen@concepts-and-training.de>
*/
class ilStudyProgrammeAutoMembershipsTableGUI extends ilTable2GUI
{
    public function __construct(
        ilObjStudyProgrammeAutoMembershipsGUI $parent_obj,
        string $parent_cmd = "",
        string $template_context = ""
    ) {
        $this->setId("sp_ac_list");
        parent::__construct($parent_obj, $parent_cmd, $template_context);
        $this->setTitle($this->lng->txt('auto_membership_title'));
        $this->setDescription($this->lng->txt('auto_membership_description'));
        $this->setEnableTitle(true);
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setExternalSorting(false);
        $this->setExternalSegmentation(true);
        $this->setRowTemplate("tpl.automembers_table_row.html", "Modules/StudyProgramme");
        $this->setShowRowsSelector(false);
        $this->setFormAction($this->ctrl->getFormAction($parent_obj, "view"));
        $this->disable('sort');
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt('auto_membership_src_type'), 'type');
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('last_edited_by'), 'editor');
        $this->addColumn($this->lng->txt('last_edited'), 'last');
        $this->addColumn($this->lng->txt('status'), 'status');
        $this->addColumn($this->lng->txt('actions'), 'actions');
        $this->setSelectAllCheckbox(ilObjStudyProgrammeAutoMembershipsGUI::CHECKBOX_SOURCE_IDS . '[]');
        $this->setEnableAllCommand(true);
        $this->addMultiCommand('deleteConfirmation', $this->lng->txt('delete'));
    }

    protected function fillRow(array $a_set): void
    {
        [$ams, $title, $usr, $actions] = $a_set;

        $id = $ams->getSourceType() . '-' . $ams->getSourceId();
        $status = $ams->isEnabled() ? $this->lng->txt('active') : $this->lng->txt('inactive');
        $date = $this->getDatePresentation($ams->getLastEdited()->getTimestamp());

        $this->tpl->setVariable("ID", $id);
        $this->tpl->setVariable("TYPE", $this->lng->txt($ams->getSourceType()));
        $this->tpl->setVariable("TITLE", $title);
        $this->tpl->setVariable("EDITOR", $usr);
        $this->tpl->setVariable("LAST_EDITED", $date);
        $this->tpl->setVariable("STATUS", $status);
        $this->tpl->setVariable("ACTIONS", $actions);
    }

    protected function getDatePresentation(int $timestamp): string
    {
        $date = new ilDateTime($timestamp, IL_CAL_UNIX);
        return ilDatePresentation::formatDate($date) ?? "";
    }
}
