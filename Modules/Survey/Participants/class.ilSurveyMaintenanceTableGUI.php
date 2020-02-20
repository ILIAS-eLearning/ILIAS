<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyMaintenanceTableGUI extends ilTable2GUI
{
    protected $counter;
    protected $confirmdelete;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $confirmdelete = false)
    {
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

    /**
     * fill row
     *
     * @access public
     * @param
     * @return
     */
    public function fillRow($data)
    {
        if (!$this->confirmdelete) {
            $this->tpl->setCurrentBlock('checkbox');
            if ($data["invited"]) {
                $this->tpl->setVariable("CB_USER_ID", "inv" . $data['usr_id']);
            } else {
                $this->tpl->setVariable("CB_USER_ID", $data['id']);
            }
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('hidden');
            if ($data["invited"]) {
                $this->tpl->setVariable("HIDDEN_USER_ID", "inv" . $data['usr_id']);
            } else {
                $this->tpl->setVariable("HIDDEN_USER_ID", $data['id']);
            }
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("USER_ID", $data["id"]);
        $this->tpl->setVariable("VALUE_USER_NAME", $data['name']);
        $this->tpl->setVariable("VALUE_USER_LOGIN", $data['login']);
        $this->tpl->setVariable("LAST_ACCESS", ilDatePresentation::formatDate(new ilDateTime($data['last_access'], IL_CAL_UNIX)));
        $this->tpl->setVariable("WORKINGTIME", $this->formatTime($data['workingtime']));

        $state = $this->lng->txt("svy_status_in_progress");
        if ($data['last_access'] == "" && $data["invited"]) {
            $state = $this->lng->txt("svy_status_invited");
        }
        if ($data["finished"] !== false) {
            $state = $this->lng->txt("svy_status_finished");
        }
        $this->tpl->setVariable("STATUS", $state);
        
        if ($data["finished"] !== null) {
            if ($data["finished"] !== false) {
                $finished .= ilDatePresentation::formatDate(new ilDateTime($data["finished"], IL_CAL_UNIX));
            } else {
                $finished = "-";
            }
            $this->tpl->setVariable("FINISHED", $finished);
        } else {
            $this->tpl->setVariable("FINISHED", "&nbsp;");
        }
    }
    
    protected function formatTime($timeinseconds)
    {
        if (is_null($timeinseconds)) {
            return " ";
        } elseif ($timeinseconds == 0) {
            return $this->lng->txt('not_available');
        } else {
            return sprintf("%02d:%02d:%02d", ($timeinseconds / 3600), ($timeinseconds / 60) % 60, $timeinseconds % 60);
        }
    }

    /**
     * @access	public
     * @param	string
     * @return	boolean	numeric ordering
     */
    public function numericOrdering($a_field)
    {
        switch ($a_field) {
            case 'workingtime':
                return true;

            default:
                return false;
        }
    }
}
