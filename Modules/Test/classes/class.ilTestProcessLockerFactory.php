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
class ilTestProcessLockerFactory
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
     * @var null|int
     */
    protected $contextId;

    /**
     * @param ilSetting $settings
     * @param ilDBInterface $db
     */
    public function __construct(ilSetting $settings, ilDBInterface $db)
    {
        $this->settings = $settings;
        $this->db = $db;
    }

    public function getContextId(): ?int
    {
        return $this->contextId;
    }

    public function withContextId(int $contextId): self
    {
        $clone = clone $this;
        $clone->contextId = $contextId;

        return $clone;
    }

    private function getLockModeSettingValue(): ?string
    {
        return $this->settings->get('ass_process_lock_mode', ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE);
    }

    /**
     * @return ilTestProcessLockerDb|ilTestProcessLockerFile|ilTestProcessLockerNone
     */
    public function getLocker(): ilTestProcessLocker
    {
        switch ($this->getLockModeSettingValue()) {
            case ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE:

                $locker = new ilTestProcessLockerNone();
                break;

            case ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_FILE:

                $storage = new ilTestProcessLockFileStorage((int) $this->getContextId());
                $storage->create();

                $locker = new ilTestProcessLockerFile($storage);
                break;

            case ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_DB:

                $locker = new ilTestProcessLockerDb($this->db);
                break;
        }

        return $locker;
    }

    public function retrieveLockerForNamedOperation(): ilTestProcessLocker
    {
        if ($this->getLocker() instanceof ilTestProcessLockerFile) {
            return $this->getLocker();
        }

        return new ilTestProcessLockerNone();
    }
}
