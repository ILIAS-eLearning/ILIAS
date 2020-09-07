<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantsTimeExtensionGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 *
 * @ilCtrl_Calls ilTestParticipantsTimeExtensionGUI: ilTimingOverviewTableGUI
 */
class ilTestParticipantsTimeExtensionGUI
{
    /**
     * Command Constants
     */
    const CMD_SHOW_LIST = 'showList';
    const CMD_SHOW_FORM = 'showForm';
    const CMD_SET_TIMING = 'setTiming';
    
    /**
     * @var ilObjTest
     */
    protected $testObj;
    
    /**
     * ilTestParticipantsTimeExtensionGUI constructor.
     * @param ilObjTest $testObj
     */
    public function __construct(ilObjTest $testObj)
    {
        $this->testObj = $testObj;
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
     * @return bool
     */
    protected function isExtraTimeFeatureAvailable()
    {
        if (!($this->getTestObj()->getProcessingTimeInSeconds() > 0)) {
            return false;
        }
        
        if ($this->getTestObj()->getNrOfTries() != 1) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Execute Command
     */
    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if (!$this->isExtraTimeFeatureAvailable()) {
            ilObjTestGUI::accessViolationRedirect();
        }
        
        switch ($DIC->ctrl()->getNextClass($this)) {
            default:
                
                $command = $DIC->ctrl()->getCmd(self::CMD_SHOW_LIST) . 'Cmd';
                
                $this->{$command}();
        }
    }
    
    /**
     * show list command
     */
    public function showListCmd()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        include_once "./Modules/Test/classes/tables/class.ilTimingOverviewTableGUI.php";
        $tableGUI = new ilTimingOverviewTableGUI($this, self::CMD_SHOW_LIST);
        $tableGUI->addCommandButton(self::CMD_SHOW_FORM, $DIC->language()->txt('timing'));

        
        require_once 'Modules/Test/classes/class.ilTestParticipantList.php';
        $participantList = new ilTestParticipantList($this->getTestObj());
        $participantList->initializeFromDbRows($this->getTestObj()->getTestParticipants());
        
        $participantList = $participantList->getAccessFilteredList(
            ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId())
        );

        $addons = $this->getTestObj()->getTimeExtensionsOfParticipants();
        
        $tableData = array();
        foreach ($participantList as $participant) {
            $tblRow = array();

            $time = $this->getTestObj()->getStartingTimeOfUser($participant->getActiveId());
            if ($time) {
                $started = $DIC->language()->txt('tst_started') . ': ' . ilDatePresentation::formatDate(
                    new ilDateTime($time, IL_CAL_UNIX)
                );
                
                $tblRow['started'] = $started;
            } else {
                $tblRow['started'] = '';
            }
            
            if ($addons[$participant->getActiveId()] > 0) {
                $tblRow['extratime'] = $addons[$participant->getActiveId()];
            }
            
            $tblRow['login'] = $participant->getLogin();
            
            if ($this->getTestObj()->getAnonymity()) {
                $tblRow['name'] = $DIC->language()->txt("anonymous");
            } else {
                $tblRow['name'] = $participant->getLastname() . ', ' . $participant->getFirstname();
            }
            
            $tableData[] = $tblRow;
        }
        
        $tableGUI->setData($tableData);
        
        $DIC->ui()->mainTemplate()->setContent($DIC->ctrl()->getHTML($tableGUI));
    }
    
    /**
     * show form command
     */
    protected function showFormCmd()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $DIC->ui()->mainTemplate()->setContent($this->buildTimingForm()->getHTML());
    }
    
    /**
     * @return ilPropertyFormGUI
     */
    protected function buildTimingForm()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormAction($this));
        $form->setTableWidth("100%");
        $form->setId("tst_change_workingtime");
        $form->setTitle($DIC->language()->txt("tst_change_workingtime"));
        
        // test users
        require_once 'Modules/Test/classes/class.ilTestParticipantList.php';
        $participantList = new ilTestParticipantList($this->getTestObj());
        $participantList->initializeFromDbRows($this->getTestObj()->getTestParticipants());
        
        $participantList = $participantList->getAccessFilteredList(
            ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId())
        );
        
        $addons = $this->getTestObj()->getTimeExtensionsOfParticipants();
        
        $participantslist = new ilSelectInputGUI($DIC->language()->txt('participants'), "participant");
        
        $options = array(
            '' => $DIC->language()->txt('please_select'),
            '0' => $DIC->language()->txt('all_participants')
        );
        
        foreach ($participantList as $participant) {
            $started = "";
            
            if ($this->getTestObj()->getAnonymity()) {
                $name = $DIC->language()->txt("anonymous");
            } else {
                $name = $participant->getLastname() . ', ' . $participant->getFirstname();
            }

            $time = $this->getTestObj()->getStartingTimeOfUser($participant->getActiveId());
            if ($time) {
                $started = ", " . $DIC->language()->txt('tst_started') . ': ' . ilDatePresentation::formatDate(new ilDateTime($time, IL_CAL_UNIX));
            }
            
            if ($addons[$participant->getActiveId()] > 0) {
                $started .= ", " . $DIC->language()->txt('extratime') . ': ' . $addons[$participant->getActiveId()] . ' ' . $DIC->language()->txt('minutes');
            }
            
            $options[$participant->getActiveId()] = $participant->getLogin() . ' (' . $name . ')' . $started;
        }
        
        $participantslist->setRequired(true);
        $participantslist->setOptions($options);
        $form->addItem($participantslist);
        
        // extra time
        $extratime = new ilNumberInputGUI($DIC->language()->txt("extratime"), "extratime");
        $extratime->setInfo($DIC->language()->txt('tst_extratime_info'));
        $extratime->setRequired(true);
        $extratime->setMinValue(0);
        $extratime->setMinvalueShouldBeGreater(false);
        $extratime->setSuffix($DIC->language()->txt('minutes'));
        $extratime->setSize(5);
        $form->addItem($extratime);
        
        if (is_array($_POST) && strlen($_POST['cmd']['timing'])) {
            $form->setValuesByArray($_POST);
        }
        
        $form->addCommandButton(self::CMD_SET_TIMING, $DIC->language()->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_LIST, $DIC->language()->txt("cancel"));
        
        return $form;
    }
    
    /**
     * set timing command
     */
    protected function setTimingCmd()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $form = $this->buildTimingForm();
        
        if ($form->checkInput()) {
            $this->getTestObj()->addExtraTime(
                $form->getInput('participant'),
                $form->getInput('extratime')
            );
            
            ilUtil::sendSuccess(sprintf($DIC->language()->txt('tst_extratime_added'), $form->getInput('extratime')), true);
            $DIC->ctrl()->redirect($this, self::CMD_SHOW_LIST);
        }
        
        $DIC->ui()->mainTemplate()->setVariable("ADM_CONTENT", $form->getHTML());
    }
}
