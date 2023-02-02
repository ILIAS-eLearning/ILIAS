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
    public function setQuestionId($questionId): void
    {
        $this->questionId = $questionId;
    }

    /**
     * @return int
     */
    public function getQuestionId(): ?int
    {
        return $this->questionId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param bool $assessmentLogEnabled
     */
    public function setAssessmentLogEnabled($assessmentLogEnabled): void
    {
        $this->assessmentLogEnabled = $assessmentLogEnabled;
    }

    /**
     * @return bool
     */
    public function isAssessmentLogEnabled(): bool
    {
        return $this->assessmentLogEnabled;
    }

    private function getLockModeSettingValue(): ?string
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
