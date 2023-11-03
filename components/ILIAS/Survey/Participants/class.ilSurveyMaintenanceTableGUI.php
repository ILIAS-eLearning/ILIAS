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
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyMaintenanceTableGUI extends ilTable2GUI
{
    protected int $counter;
    protected bool $confirmdelete;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        bool $confirmdelete = false
    ) {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->counter = 1;
        $this->confirmdelete = $confirmdelete;

        $this->setFormName('maintenanceform');
        $this->setStyle('table', 'fullwidth');

        if (!$confirmdelete) {
            $this->addColumn('', '', '1%', true);
        }
        $this->addColumn($this->lng->txt("name"), 'name', '');
        $this->addColumn($this->lng->txt("login"), 'login', '');
        $this->addColumn($this->lng->txt("last_access"), 'last_access', '');
        $this->addColumn($this->lng->txt("workingtime"), 'workingtime', '');
        $this->addColumn($this->lng->txt("svy_status"), '', '');
        $this->addColumn($this->lng->txt("survey_results_finished"), 'finished', '');

        $this->setRowTemplate("tpl.il_svy_svy_maintenance_row.html", "Modules/Survey/Participants");

        if ($confirmdelete) {
            $this->addCommandButton('confirmDeleteSelectedUserData', $this->lng->txt('confirm'));
            $this->addCommandButton('cancelDeleteSelectedUserData', $this->lng->txt('cancel'));
        } else {
            $this->addMultiCommand('deleteSingleUserResults', $this->lng->txt('svy_remove_participants'));
        }

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        $this->setShowRowsSelector(true);

        if ($confirmdelete) {
            $this->disable('sort');
            $this->disable('select_all');
        } else {
            $this->setPrefix('chbUser');
            $this->setSelectAllCheckbox('chbUser');
            $this->enable('sort');
            $this->enable('select_all');
        }
        $this->enable('header');
    }

    protected function fillRow(array $a_set): void
    {
        if (!$this->confirmdelete) {
            $this->tpl->setCurrentBlock('checkbox');
            if ($a_set["invited"]) {
                $this->tpl->setVariable("CB_USER_ID", "inv" . $a_set['usr_id']);
            } else {
                $this->tpl->setVariable("CB_USER_ID", $a_set['id']);
            }
        } else {
            $this->tpl->setCurrentBlock('hidden');
            if ($a_set["invited"]) {
                $this->tpl->setVariable("HIDDEN_USER_ID", "inv" . $a_set['usr_id']);
            } else {
                $this->tpl->setVariable("HIDDEN_USER_ID", $a_set['id']);
            }
        }
        $this->tpl->parseCurrentBlock();
        $this->tpl->setVariable("USER_ID", $a_set["id"]);
        $this->tpl->setVariable("VALUE_USER_NAME", $a_set['name']);
        $this->tpl->setVariable("VALUE_USER_LOGIN", $a_set['login']);
        $this->tpl->setVariable("LAST_ACCESS", ilDatePresentation::formatDate(new ilDateTime($a_set['last_access'], IL_CAL_UNIX)));
        $this->tpl->setVariable("WORKINGTIME", $this->formatTime($a_set['workingtime'] ?? null));

        $state = $this->lng->txt("svy_status_in_progress");
        if ($a_set['last_access'] == "" && $a_set["invited"]) {
            $state = $this->lng->txt("svy_status_invited");
        }
        if (($a_set["finished"] ?? false) !== false) {
            $state = $this->lng->txt("svy_status_finished");
        }
        $this->tpl->setVariable("STATUS", $state);
        $finished = "";
        if ((int) ($a_set["finished"] ?? 0) > 0) {
            $finished .= ilDatePresentation::formatDate(new ilDateTime($a_set["finished"], IL_CAL_UNIX));
        } else {
            $finished = "-";
        }
        $this->tpl->setVariable("FINISHED", $finished);
    }

    /**
     * @param mixed $timeinseconds
     * @return string
     */
    protected function formatTime($timeinseconds): string
    {
        if (is_null($timeinseconds)) {
            return " ";
        }

        if ($timeinseconds == 0) {
            return $this->lng->txt('not_available');
        }

        return sprintf("%02d:%02d:%02d", ($timeinseconds / 3600), ($timeinseconds / 60) % 60, $timeinseconds % 60);
    }

    public function numericOrdering(string $a_field): bool
    {
        switch ($a_field) {
            case 'workingtime':
                return true;

            default:
                return false;
        }
    }
}
