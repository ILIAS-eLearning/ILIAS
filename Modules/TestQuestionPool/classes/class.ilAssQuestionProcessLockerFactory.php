<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLocker.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockerNone.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockerFile.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockerDb.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssQuestionProcessLockerFactory
{
    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var integer
     */
    protected $questionId;

    /**
     * @var integer
     */
    protected $userId;

    /**
     * @var bool
     */
    protected $assessmentLogEnabled;

    /**
     * @param ilSetting $settings
     * @param ilDBInterface $db
     */
    public function __construct(ilSetting $settings, ilDBInterface $db)
    {
        $this->settings = $settings;
        $this->db = $db;
        
        $this->questionId = null;
        $this->userId = null;
        $this->assessmentLogEnabled = false;
    }

    /**
     * @param int $questionId
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;
    }

    /**
     * @return int
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }
    
    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param bool $assessmentLogEnabled
     */
    public function setAssessmentLogEnabled($assessmentLogEnabled)
    {
        $this->assessmentLogEnabled = $assessmentLogEnabled;
    }

    /**
     * @return bool
     */
    public function isAssessmentLogEnabled()
    {
        return $this->assessmentLogEnabled;
    }

    private function getLockModeSettingValue()
    {
        return $this->settings->get('ass_process_lock_mode', ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE);
    }

    /**
     * @return ilAssQuestionProcessLockerDb|ilAssQuestionProcessLockerFile|ilAssQuestionProcessLockerNone
     */
    public function getLocker()
    {
        switch ($this->getLockModeSettingValue()) {
            case ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE:
                
                $locker = new ilAssQuestionProcessLockerNone();
                break;
                
            case ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_FILE:

                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLockFileStorage.php';
                $storage = new ilAssQuestionProcessLockFileStorage($this->getQuestionId(), $this->getUserId());
                $storage->create();

                $locker = new ilAssQuestionProcessLockerFile($storage);
                break;
            
            case ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_DB:

                $locker = new ilAssQuestionProcessLockerDb($this->db);
                $locker->setAssessmentLogEnabled($this->isAssessmentLogEnabled());
                break;
        }
        
        return $locker;
    }
}
