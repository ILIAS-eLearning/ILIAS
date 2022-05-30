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
    const EVALGUI_CMD_SHOW_PASS_OVERVIEW = 'outUserResultsOverview';

    protected ?ilObjTest $testObj = null;
    protected ?ilTestAccess $testAccess = null;
    protected ?ilTestSession $testSession = null;
    protected ?ilTestObjectiveOrientedContainer $objectiveParent = null;

    public function getTestObj() : ?ilObjTest
    {
        return $this->testObj;
    }
    
    public function setTestObj(ilObjTest $testObj) : void
    {
        $this->testObj = $testObj;
    }

    public function getTestAccess() : ?ilTestAccess
    {
        return $this->testAccess;
    }
    
    public function setTestAccess(ilTestAccess $testAccess) : void
    {
        $this->testAccess = $testAccess;
    }

    public function getTestSession() : ?ilTestSession
    {
        return $this->testSession;
    }

    public function setTestSession(ilTestSession $testSession) : void
    {
        $this->testSession = $testSession;
    }
    
    public function getObjectiveParent() : ?ilTestObjectiveOrientedContainer
    {
        return $this->objectiveParent;
    }

    public function setObjectiveParent(ilTestObjectiveOrientedContainer $objectiveParent) : void
    {
        $this->objectiveParent = $objectiveParent;
    }

    public function executeCommand() : void
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
