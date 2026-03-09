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

use ILIAS\Test\Logging\TestLogger;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilAssQuestionProcessLockerFactory
{
    protected ?int $question_id = null;
    protected ?int $user_id = null;

    public function __construct(
        private readonly ilSetting $settings,
        private readonly ilDBInterface $db,
        private readonly ilLogger|TestLogger $logger
    ) {
    }

    public function setQuestionId(int $question_id): void
    {
        $this->question_id = $question_id;
    }

    public function getQuestionId(): ?int
    {
        return $this->question_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    private function getLockModeSettingValue(): ?string
    {
        return $this->settings->get('ass_process_lock_mode', ilObjTestFolder::ASS_PROC_LOCK_MODE_NONE);
    }

    public function getLocker(): ilAssQuestionProcessLocker
    {
        switch ($this->getLockModeSettingValue()) {
            case ilObjTestFolder::ASS_PROC_LOCK_MODE_NONE:
                return new ilAssQuestionProcessLockerNone();

            case ilObjTestFolder::ASS_PROC_LOCK_MODE_FILE:
                $storage = new ilAssQuestionProcessLockFileStorage($this->getQuestionId(), $this->getUserId());
                $storage->create();
                return new ilAssQuestionProcessLockerFile($storage, $this->logger);

            case ilObjTestFolder::ASS_PROC_LOCK_MODE_DB:
                return new ilAssQuestionProcessLockerDb($this->db);
        }
    }
}
