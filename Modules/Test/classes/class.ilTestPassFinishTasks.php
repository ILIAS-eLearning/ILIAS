<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Modules/Test/classes/class.ilTestSession.php';
/**
 * Class ilTestPassFinishTasks
 * @author Guido Vollbach <gvollbach@databay.de>
 */
class ilTestPassFinishTasks
{
    protected $testSession;

    protected $obj_id;

    protected $active_id;

    /**
     * ilTestPassFinishTasks constructor.
     * @param $active_id
     * @param $obj_id
     */
    public function __construct($active_id, $obj_id)
    {
        $this->testSession = new ilTestSession();
        $this->testSession->loadFromDb($active_id);
        $this->obj_id		= $obj_id;
        $this->active_id	= $active_id;
    }

    public function performFinishTasks(ilTestProcessLocker $processLocker)
    {
        $testSession = $this->testSession;
        
        $processLocker->executeTestFinishOperation(function () use ($testSession) {
            if (!$testSession->isSubmitted()) {
                $testSession->setSubmitted();
                $testSession->setSubmittedTimestamp();
                $testSession->saveToDb();
            }
            
            $lastStartedPass = (
                $testSession->getLastStartedPass() === null ? -1 : $testSession->getLastStartedPass()
            );
            
            $lastFinishedPass = (
                $testSession->getLastFinishedPass() === null ? -1 : $testSession->getLastFinishedPass()
            );
            
            if ($lastStartedPass > -1 && $lastFinishedPass < $lastStartedPass) {
                $testSession->setLastFinishedPass($testSession->getPass());
                $testSession->increaseTestPass(); // saves to db
            }
        });
        
        $this->updateLearningProgressAfterPassFinishedIsWritten();
    }

    protected function updateLearningProgressAfterPassFinishedIsWritten()
    {
        require_once './Modules/Test/classes/class.ilObjTestAccess.php';
        require_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
        ilLPStatusWrapper::_updateStatus(
            $this->obj_id,
            ilObjTestAccess::_getParticipantId($this->active_id)
        );
        
        $caller = $this->getCaller();
        $lp = ilLPStatus::_lookupStatus($this->obj_id, $this->testSession->getUserId());
        $debug = "finPass={$this->testSession->getLastFinishedPass()} / Lp={$lp}";
        
        ilObjAssessmentFolder::_addLog(
            $this->testSession->getUserId(),
            $this->obj_id,
            "updateLearningProgressAfterPassFinishedIsWritten has been called from {$caller} ({$debug})",
            true
        );
    }
    
    protected function getCaller()
    {
        try {
            throw new Exception();
        } catch (Exception $e) {
            $trace = $e->getTrace();
        }
        
        return $trace[3]['class'];
    }
}
