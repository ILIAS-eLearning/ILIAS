<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesTest
*/

class ilTestParticipantsTableGUI extends ilTable2GUI
{
    protected $manageResultsCommandsEnabled = false;
    protected $manageInviteesCommandsEnabled = false;
    
    protected $rowKeyDataField;
    
    protected $anonymity;
    
    /**
     * @var bool
     */
    protected $participantHasSolutionsFilterEnabled = false;
    
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        $this->setId('tst_participants_' . $a_parent_obj->getTestObj()->getRefId());
        parent::__construct($a_parent_obj, $a_parent_cmd);

        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $this->setStyle('table', 'fullwidth');
        
        $this->setFormName('participantsForm');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        
        $this->setRowTemplate("tpl.il_as_tst_participants_row.html", "Modules/Test");
        
        $this->enable('header');
        $this->enable('sort');
        
        $this->setShowRowsSelector(true);
    }
    
    /**
     * @return bool
     */
    public function isManageResultsCommandsEnabled()
    {
        return $this->manageResultsCommandsEnabled;
    }
    
    /**
     * @param bool $manageResultsCommandsEnabled
     */
    public function setManageResultsCommandsEnabled($manageResultsCommandsEnabled)
    {
        $this->manageResultsCommandsEnabled = $manageResultsCommandsEnabled;
    }
    
    /**
     * @return bool
     */
    public function isManageInviteesCommandsEnabled()
    {
        return $this->manageInviteesCommandsEnabled;
    }
    
    /**
     * @param bool $manageInviteesCommandsEnabled
     */
    public function setManageInviteesCommandsEnabled($manageInviteesCommandsEnabled)
    {
        $this->manageInviteesCommandsEnabled = $manageInviteesCommandsEnabled;
        
        if ($manageInviteesCommandsEnabled) {
            $this->setSelectAllCheckbox('chbUser');
        } else {
            $this->setSelectAllCheckbox('');
        }
    }
    
    /**
     * @return string
     */
    public function getRowKeyDataField()
    {
        return $this->rowKeyDataField;
    }
    
    /**
     * @param string $rowKeyDataField
     */
    public function setRowKeyDataField($rowKeyDataField)
    {
        $this->rowKeyDataField = $rowKeyDataField;
    }
    
    /**
     * @return mixed
     */
    public function getAnonymity()
    {
        return $this->anonymity;
    }
    
    /**
     * @param mixed $anonymity
     */
    public function setAnonymity($anonymity)
    {
        $this->anonymity = $anonymity;
    }
    
    /**
     * @return bool
     */
    public function isParticipantHasSolutionsFilterEnabled()
    {
        return $this->participantHasSolutionsFilterEnabled;
    }
    
    /**
     * @param bool $participantHasSolutionsFilterEnabled
     */
    public function setParticipantHasSolutionsFilterEnabled(bool $participantHasSolutionsFilterEnabled)
    {
        $this->participantHasSolutionsFilterEnabled = $participantHasSolutionsFilterEnabled;
    }
    
    /**
     * @param string $field
     * @return bool
     */
    public function numericOrdering($field)
    {
        return in_array($field, array(
            'access', 'tries'
        ));
    }
    
    protected function needsCheckboxColumn()
    {
        return $this->isManageInviteesCommandsEnabled();
    }
    
    public function initColumns()
    {
        if ($this->needsCheckboxColumn()) {
            $this->addColumn('', '', '1%');
        }
        
        $this->addColumn($this->lng->txt("name"), 'name', '');
        $this->addColumn($this->lng->txt("login"), 'login', '');
        
        if ($this->isManageInviteesCommandsEnabled()) {
            $this->addColumn($this->lng->txt("clientip"), 'clientip', '');
        }
        
        $this->addColumn($this->lng->txt("tst_started"), 'started', '');
        $this->addColumn($this->lng->txt("tst_nr_of_tries_of_user"), 'tries', '');
        
        $this->addColumn($this->lng->txt("unfinished_passes"), 'unfinished', '');
        $this->addColumn($this->lng->txt("tst_finished"), 'finished', '');
        
        $this->addColumn($this->lng->txt("last_access"), 'access', '');
        
        if ($this->isActionsColumnRequired()) {
            $this->addColumn('', '', '');
        }
    }
    
    public function initCommands()
    {
        if ($this->isManageInviteesCommandsEnabled()) {
            $this->addMultiCommand('saveClientIp', $this->lng->txt('save'));
            $this->addMultiCommand('removeParticipants', $this->lng->txt('remove_as_participant'));
        }
    }
    
    public function initFilter()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        if ($this->isParticipantHasSolutionsFilterEnabled()) {
            // title/description
            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
            $ti = new ilSelectInputGUI($lng->txt("selection"), "selection");
            $ti->setOptions(
                array(
                    'all' => $lng->txt('all_participants'),
                    'withSolutions' => $lng->txt('with_solutions_participants'),
                    'withoutSolutions' => $lng->txt('without_solutions_participants')
                )
            );
            $this->addFilterItem($ti);
            $ti->readFromSession();        // get currenty value from session (always after addFilterItem())
            $this->filter["title"] = $ti->getValue();
        }
    }
    
    /**
     * @param array $data
     */
    public function fillRow($data)
    {
        if ($this->needsCheckboxColumn()) {
            $this->tpl->setCurrentBlock('checkbox_column');
            $this->tpl->setVariable("CHB_ROW_KEY", $this->fetchRowKey($data));
            $this->tpl->parseCurrentBlock();
        }

        if ($this->isManageInviteesCommandsEnabled()) {
            $this->tpl->setCurrentBlock('client_ip_column');
            $this->tpl->setVariable("CLIENT_IP", $data['clientip']);
            $this->tpl->setVariable("CIP_ROW_KEY", $this->fetchRowKey($data));
            $this->tpl->parseCurrentBlock();
        }
        
        if ($this->isActionsColumnRequired()) {
            $this->tpl->setCurrentBlock('actions_column');
            
            if ($data['active_id'] > 0) {
                $this->tpl->setVariable('ACTIONS', $this->buildActionsMenu($data)->getHTML());
            } else {
                $this->tpl->touchBlock('actions_column');
            }
            
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("ROW_KEY", $this->fetchRowKey($data));
        $this->tpl->setVariable("LOGIN", $data['login']);
        $this->tpl->setVariable("FULLNAME", $data['name']);
        
        $this->tpl->setVariable("STARTED", ($data['started']) ? $this->buildOkIcon() : '');
        $this->tpl->setVariable("TRIES", $this->fetchTriesValue($data));
        $this->tpl->setVariable("UNFINISHED_PASSES", $this->buildUnfinishedPassesStatusString($data));
        
        $this->tpl->setVariable("FINISHED", ($data['finished']) ? $this->buildOkIcon() : '');
        $this->tpl->setVariable("ACCESS", $this->buildFormattedAccessDate($data));
    }
    
    /**
     * @param array $data
     * @return ilAdvancedSelectionListGUI
     */
    protected function buildActionsMenu($data)
    {
        $asl = new ilAdvancedSelectionListGUI();
        
        $this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $data['active_id']);
        
        if ($this->isManageResultsCommandsEnabled() && $data['unfinished']) {
            $finishHref = $this->ctrl->getLinkTargetByClass('ilTestEvaluationGUI', 'finishTestPassForSingleUser');
            $asl->addItem($this->lng->txt('finish_test'), $finishHref, $finishHref);
        }
        
        return $asl;
    }
    
    /**
     * @return bool
     */
    protected function isActionsColumnRequired()
    {
        if ($this->isManageResultsCommandsEnabled()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @param array $data
     * @return string
     */
    protected function fetchRowKey($data)
    {
        return $data[$this->getRowKeyDataField()];
    }
    
    /**
     * @param array $data
     * @return string
     */
    protected function fetchTriesValue($data)
    {
        if ($data['tries'] < 1) {
            return '';
        }
        
        if ($data['tries'] > 1) {
            return sprintf($this->lng->txt("passes_finished"), $data['tries']);
        }
        
        return sprintf($this->lng->txt("pass_finished"), $data['tries']);
    }
    
    /**
     * @param $data
     * @return string
     */
    protected function buildUnfinishedPassesStatusString($data)
    {
        if ($data['unfinished']) {
            return $this->lng->txt('yes');
        }
        
        return $this->lng->txt('no');
    }
    
    /**
     * @return string
     */
    protected function buildOkIcon()
    {
        return "<img border=\"0\" align=\"middle\" src=\"" . ilUtil::getImagePath("icon_ok.svg") . "\" alt=\"" . $this->lng->txt("ok") . "\" />";
    }
    
    /**
     * @param $data
     * @return string
     */
    protected function buildFormattedAccessDate($data)
    {
        return ilDatePresentation::formatDate(new ilDateTime($data['access'], IL_CAL_DATETIME));
    }
}
