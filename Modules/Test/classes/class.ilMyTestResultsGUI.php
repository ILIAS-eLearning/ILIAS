<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMyTestResultsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 *
 * @ilCtrl_Calls ilMyTestResultsGUI: ilTestEvaluationGUI
 * @ilCtrl_Calls ilMyTestResultsGUI: ilAssQuestionPageGUI
 * @ilCtrl_Calls ilMyTestResultsGUI: ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilMyTestResultsGUI: ilAssGenFeedbackPageGUI
 */
class ilMyTestResultsGUI
{
    /**
     * command constants
     */
    const EVALGUI_CMD_SHOW_PASS_OVERVIEW = 'outUserResultsOverview';
    
    /**
     * @var ilObjTest
     */
    protected $testObj;
    
    /**
     * @var ilTestAccess
     */
    protected $testAccess;
    
    /**
     * @var ilTestSession
     */
    protected $testSession;
    
    /**
     * @var ilTestObjectiveOrientedContainer
     */
    protected $objectiveParent;
    
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
     * @return ilTestAccess
     */
    public function getTestAccess()
    {
        return $this->testAccess;
    }
    
    /**
     * @param ilTestAccess $testAccess
     */
    public function setTestAccess($testAccess)
    {
        $this->testAccess = $testAccess;
    }
    
    /**
     * @return ilTestSession
     */
    public function getTestSession()
    {
        return $this->testSession;
    }
    
    /**
     * @param ilTestSession $testSession
     */
    public function setTestSession($testSession)
    {
        $this->testSession = $testSession;
    }
    
    /**
     * @return ilTestObjectiveOrientedContainer
     */
    public function getObjectiveParent()
    {
        return $this->objectiveParent;
    }
    
    /**
     * @param ilTestObjectiveOrientedContainer $objectiveParent
     */
    public function setObjectiveParent($objectiveParent)
    {
        $this->objectiveParent = $objectiveParent;
    }
    
    /**
     * Execute Command
     */
    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if (!$DIC->ctrl()->getCmd()) {
            $DIC->ctrl()->setCmd(self::EVALGUI_CMD_SHOW_PASS_OVERVIEW);
        }
        
        switch ($DIC->ctrl()->getNextClass()) {
            case "iltestevaluationgui":
                require_once 'Modules/Test/classes/class.ilTestEvaluationGUI.php';
                $gui = new ilTestEvaluationGUI($this->getTestObj());
                $gui->setObjectiveOrientedContainer($this->getObjectiveParent());
                $gui->setTestAccess($this->getTestAccess());
                $DIC->ctrl()->forwardCommand($gui);
                break;
                
            case 'ilassquestionpagegui':
                require_once 'Modules/Test/classes/class.ilAssQuestionPageCommandForwarder.php';
                $forwarder = new ilAssQuestionPageCommandForwarder();
                $forwarder->setTestObj($this->getTestObj());
                $forwarder->forward();
                break;
        }
    }
}
