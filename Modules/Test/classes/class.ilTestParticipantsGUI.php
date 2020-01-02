<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 *
 * @ilCtrl_Calls ilTestParticipantsGUI: ilTestParticipantsTableGUI
 * @ilCtrl_Calls ilTestParticipantsGUI: ilRepositorySearchGUI
 * @ilCtrl_Calls ilTestParticipantsGUI: ilTestEvaluationGUI
 */
class ilTestParticipantsGUI
{
    /**
     * Command/Callback Constants
     */
    
    const CMD_SHOW = 'show';
    const CMD_SET_FILTER = 'setFilter';
    const CMD_RESET_FILTER = 'resetFilter';
    const CMD_SAVE_CLIENT_IP = 'saveClientIp';
    
    const CALLBACK_ADD_PARTICIPANT = 'addParticipants';
    
    /**
     * @var ilObjTest
     */
    protected $testObj;
    
    /**
     * @var ilTestQuestionSetConfig
     */
    protected $questionSetConfig;
    
    /**
     * @var ilTestObjectiveOrientedContainer
     */
    protected $objectiveParent;
    
    /**
     * @var ilTestAccess
     */
    protected $testAccess;
    
    /**
     * ilTestParticipantsGUI constructor.
     * @param ilObjTest $testObj
     */
    public function __construct(ilObjTest $testObj, ilTestQuestionSetConfig $questionSetConfig)
    {
        $this->testObj = $testObj;
        $this->questionSetConfig = $questionSetConfig;
    }
    
    /**
     * @return ilObjTest
     */
    public function getTestObj()
    {
        return $this->testObj;
    }
    
    /**
     * @param ilObjTest $testObj
     */
    public function setTestObj($testObj)
    {
        $this->testObj = $testObj;
    }
    
    /**
     * @return ilTestQuestionSetConfig
     */
    public function getQuestionSetConfig()
    {
        return $this->questionSetConfig;
    }
    
    /**
     * @param ilTestQuestionSetConfig $questionSetConfig
     */
    public function setQuestionSetConfig($questionSetConfig)
    {
        $this->questionSetConfig = $questionSetConfig;
    }
    
    /**
     * @return ilTestObjectiveOrientedContainer
     */
    public function getObjectiveParent() : ilTestObjectiveOrientedContainer
    {
        return $this->objectiveParent;
    }
    
    /**
     * @param ilTestObjectiveOrientedContainer $objectiveParent
     */
    public function setObjectiveParent(ilTestObjectiveOrientedContainer $objectiveParent)
    {
        $this->objectiveParent = $objectiveParent;
    }
    
    /**
     * @return ilTestAccess
     */
    public function getTestAccess() : ilTestAccess
    {
        return $this->testAccess;
    }
    
    /**
     * @param ilTestAccess $testAccess
     */
    public function setTestAccess(ilTestAccess $testAccess)
    {
        $this->testAccess = $testAccess;
    }
    
    /**
     * Execute Command
     */
    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        
        switch ($DIC->ctrl()->getNextClass($this)) {
            case 'ilrepositorysearchgui':
                
                require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
                require_once 'Services/Search/classes/class.ilRepositorySearchGUI.php';
                
                $gui = new ilRepositorySearchGUI();
                $gui->setCallback($this, self::CALLBACK_ADD_PARTICIPANT, array());
                
                $gui->addUserAccessFilterCallable(ilTestParticipantAccessFilter::getManageParticipantsUserFilter(
                    $this->getTestObj()->getRefId()
                ));
                
                
                $DIC->ctrl()->setReturn($this, self::CMD_SHOW);
                $DIC->ctrl()->forwardCommand($gui);
                
                break;
                
            case "iltestevaluationgui":
                
                require_once 'Modules/Test/classes/class.ilTestEvaluationGUI.php';
                
                $gui = new ilTestEvaluationGUI($this->getTestObj());
                $gui->setObjectiveOrientedContainer($this->getObjectiveParent());
                $gui->setTestAccess($this->getTestAccess());
                $DIC->tabs()->clearTargets();
                $DIC->tabs()->clearSubTabs();
                
                $DIC->ctrl()->forwardCommand($gui);
                
                break;
                
            default:
                
                $command = $DIC->ctrl()->getCmd(self::CMD_SHOW) . 'Cmd';
                $this->{$command}();
        }
    }
    
    /**
     * @param array $a_user_ids
     * @return bool
     */
    public function addParticipants($a_user_ids = array())
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
        $filterCallback = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());
        $a_user_ids = call_user_func_array($filterCallback, [$a_user_ids]);
        
        $countusers = 0;
        // add users
        if (is_array($a_user_ids)) {
            $i = 0;
            foreach ($a_user_ids as $user_id) {
                $client_ip = $_POST["client_ip"][$i];
                $this->getTestObj()->inviteUser($user_id, $client_ip);
                $countusers++;
                $i++;
            }
        }
        $message = "";
        if ($countusers) {
            $message = $DIC->language()->txt("tst_invited_selected_users");
        }
        if (strlen($message)) {
            ilUtil::sendInfo($message, true);
        } else {
            ilUtil::sendInfo($DIC->language()->txt("tst_invited_nobody"), true);
            return false;
        }
        
        $DIC->ctrl()->redirect($this, self::CMD_SHOW);
    }
    
    /**
     * @return ilTestParticipantsTableGUI
     */
    protected function buildTableGUI()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        require_once 'Modules/Test/classes/tables/class.ilTestParticipantsTableGUI.php';
        $tableGUI = new ilTestParticipantsTableGUI($this, self::CMD_SHOW);
        
        $tableGUI->setParticipantHasSolutionsFilterEnabled(
            $this->getTestObj()->getFixedParticipants()
        );
        
        if ($this->getTestObj()->getFixedParticipants()) {
            $tableGUI->setTitle($DIC->language()->txt('tst_tbl_invited_users'));
        } else {
            $tableGUI->setTitle($DIC->language()->txt('tst_tbl_participants'));
        }
        
        return $tableGUI;
    }
    
    /**
     * set table filter command
     */
    protected function setFilterCmd()
    {
        $tableGUI = $this->buildTableGUI();
        $tableGUI->initFilter($this->getTestObj()->getFixedParticipants());
        $tableGUI->writeFilterToSession();
        $tableGUI->resetOffset();
        $this->showCmd();
    }
    
    /**
     * reset table filter command
     */
    protected function resetFilterCmd()
    {
        $tableGUI = $this->buildTableGUI();
        $tableGUI->resetFilter();
        $tableGUI->resetOffset();
        $this->showCmd();
    }
    
    /**
     * show command
     */
    public function showCmd()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $tableGUI = $this->buildTableGUI();
        
        if (!$this->getQuestionSetConfig()->areDepenciesBroken()) {
            if ($this->getTestObj()->getFixedParticipants()) {
                $participantList = $this->getTestObj()->getInvitedParticipantList()->getAccessFilteredList(
                    ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId())
                );
                
                $tableGUI->setData($this->applyFilterCriteria($participantList->getParticipantsTableRows()));
                $tableGUI->setRowKeyDataField('usr_id');
                $tableGUI->setManageInviteesCommandsEnabled(true);
                $tableGUI->setDescription($DIC->language()->txt("fixed_participants_hint"));
            } else {
                $participantList = $this->getTestObj()->getActiveParticipantList()->getAccessFilteredList(
                    ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId())
                );
                
                $tableGUI->setData($participantList->getParticipantsTableRows());
                $tableGUI->setRowKeyDataField('active_id');
            }
            
            $tableGUI->setManageResultsCommandsEnabled(true);
            
            $this->initToolbarControls($participantList);
        }
        
        $tableGUI->setAnonymity($this->getTestObj()->getAnonymity());
        
        $tableGUI->initColumns();
        $tableGUI->initCommands();
        
        $tableGUI->initFilter();
        $tableGUI->setFilterCommand(self::CMD_SET_FILTER);
        $tableGUI->setResetCommand(self::CMD_RESET_FILTER);
        
        $DIC->ui()->mainTemplate()->setContent($DIC->ctrl()->getHTML($tableGUI));
    }
    
    /**
     * @param array $in_rows
     * @return array
     */
    protected function applyFilterCriteria($in_rows)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $sess_filter = $_SESSION['form_tst_participants_' . $this->getTestObj()->getRefId()]['selection'];
        $sess_filter = str_replace('"', '', $sess_filter);
        $sess_filter = explode(':', $sess_filter);
        $filter = substr($sess_filter[2], 0, strlen($sess_filter[2])-1);
        
        if ($filter == 'all' || $filter == false) {
            return $in_rows; #unchanged - no filter.
        }
        
        $with_result = array();
        $without_result = array();
        foreach ($in_rows as $row) {
            $result = $DIC->database()->query(
                'SELECT count(solution_id) count
				FROM tst_solutions
				WHERE active_fi = ' . $DIC->database()->quote($row['active_id'])
            );
            $count = $DIC->database()->fetchAssoc($result);
            $count = $count['count'];
            
            if ($count == 0) {
                $without_result[] = $row;
            } else {
                $with_result[] = $row;
            }
        }
        
        if ($filter == 'withSolutions') {
            return $with_result;
        }
        return $without_result;
    }
    
    protected function initToolbarControls(ilTestParticipantList $participantList)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if ($this->getTestObj()->getFixedParticipants()) {
            $this->addUserSearchControls($DIC->toolbar());
        }
        
        if ($this->getTestObj()->getFixedParticipants() && $participantList->hasUnfinishedPasses()) {
            $DIC->toolbar()->addSeparator();
        }
        
        if ($participantList->hasUnfinishedPasses()) {
            $this->addFinishAllPassesButton($DIC->toolbar());
        }
    }
    
    /**
     * @param ilToolbarGUI $toolbar
     * @param ilLanguage $lng
     */
    protected function addUserSearchControls(ilToolbarGUI $toolbar)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        // search button
        include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $toolbar,
            array(
                'auto_complete_name'	=> $DIC->language()->txt('user'),
                'submit_name'			=> $DIC->language()->txt('add')
            )
        );
        
        require_once  'Services/UIComponent/Button/classes/class.ilLinkButton.php';
        $search_btn = ilLinkButton::getInstance();
        $search_btn->setCaption('tst_search_users');
        $search_btn->setUrl($DIC->ctrl()->getLinkTargetByClass('ilRepositorySearchGUI', 'start'));
        
        $toolbar->addSeparator();
        $toolbar->addButtonInstance($search_btn);
    }
    
    /**
     * @param ilToolbarGUI $toolbar
     */
    protected function addFinishAllPassesButton(ilToolbarGUI $toolbar)
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $finish_all_user_passes_btn = ilLinkButton::getInstance();
        $finish_all_user_passes_btn->setCaption('finish_all_user_passes');
        $finish_all_user_passes_btn->setUrl($DIC->ctrl()->getLinkTargetByClass('iltestevaluationgui', 'finishAllUserPasses'));
        $toolbar->addButtonInstance($finish_all_user_passes_btn);
    }
    
    /**
     * save client ip command
     */
    protected function saveClientIpCmd()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
        $filterCallback = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());
        $a_user_ids = call_user_func_array($filterCallback, [(array) $_POST["chbUser"]]);
        
        if (is_array($a_user_ids)) {
            foreach ($a_user_ids as $user_id) {
                $this->getTestObj()->setClientIP($user_id, $_POST["clientip_" . $user_id]);
            }
        } else {
            ilUtil::sendInfo($DIC->language()->txt("select_one_user"), true);
        }
        $DIC->ctrl()->redirect($this, self::CMD_SHOW);
    }
    
    /**
     * remove participants command
     */
    protected function removeParticipantsCmd()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        require_once 'Modules/Test/classes/class.ilTestParticipantAccessFilter.php';
        $filterCallback = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());
        $a_user_ids = call_user_func_array($filterCallback, [(array) $_POST["chbUser"]]);
        
        if (is_array($a_user_ids)) {
            foreach ($a_user_ids as $user_id) {
                $this->getTestObj()->disinviteUser($user_id);
            }
        } else {
            ilUtil::sendInfo($DIC->language()->txt("select_one_user"), true);
        }
        
        $DIC->ctrl()->redirect($this, self::CMD_SHOW);
    }
}
