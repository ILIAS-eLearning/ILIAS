<?php

 declare(strict_types=1);

 /**
 * Class ilStudyProgrammeAutoMembershipsTableGUI
 *
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilStudyProgrammeAutoMembershipsTableGUI extends ilTable2GUI
{
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd = "",
        $a_template_context = ""
    ) {
        $this->setId("sp_ac_list");
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
        $this->setTitle($this->lng->txt('auto_membership_title'));
        $this->setEnableTitle(true);
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setExternalSorting(false);
        $this->setExternalSegmentation(true);
        $this->setRowTemplate("tpl.automembers_table_row.html", "Modules/StudyProgramme");
        $this->setShowRowsSelector(false);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, "view"));
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

    protected function fillRow($set)
    {
        list($ams, $title, $usr, $actions) = $set;

        $username = ilObjUser::_lookupName($ams->getLastEditorId());
        $editor = implode(' ', [
            $username['firstname'],
            $username['lastname'],
            '(' . $username['login'] . ')'
        ]);

        $id = $ams->getSourceType() . '-' . $ams->getSourceId();
        $status = $ams->isEnabled()  ? $this->lng->txt('active') : $this->lng->txt('inactive');
        $date = $this->getDatePresentation($ams->getLastEdited()->getTimestamp());

        $this->tpl->setVariable("ID", $id);
        $this->tpl->setVariable("TYPE", $this->lng->txt($ams->getSourceType()));
        $this->tpl->setVariable("TITLE", $title);
        $this->tpl->setVariable("EDITOR", $usr);
        $this->tpl->setVariable("LAST_EDITED", $date);
        $this->tpl->setVariable("STATUS", $status);
        $this->tpl->setVariable("ACTIONS", $actions);
    }

    protected function getDatePresentation(int $timestamp) : string
    {
        $date = new ilDateTime($timestamp, IL_CAL_UNIX);
        return ilDatePresentation::formatDate($date);
    }
}
