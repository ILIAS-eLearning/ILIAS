<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
require_once 'Modules/Test/classes/class.ilTestProcessLocker.php';
require_once 'Modules/Test/classes/class.ilTestProcessLockerNone.php';
require_once 'Modules/Test/classes/class.ilTestProcessLockerFile.php';
require_once 'Modules/Test/classes/class.ilTestProcessLockerDb.php';

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

    public function getContextId() : ?int
    {
        return $this->contextId;
    }

    public function withContextId(int $contextId) : self
    {
        $clone = clone $this;
        $clone->contextId = $contextId;

        return $clone;
    }

    private function getLockModeSettingValue()
    {
        return $this->settings->get('ass_process_lock_mode', ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE);
    }

    /**
     * @return ilTestProcessLockerDb|ilTestProcessLockerFile|ilTestProcessLockerNone
     */
    public function getLocker() : ilTestProcessLocker
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

    public function retrieveLockerForNamedOperation() : ilTestProcessLocker
    {
        if ($this->getLocker() instanceof ilTestProcessLockerFile) {
            return $this->getLocker();
        }

        return new ilTestProcessLockerNone();
    }
}
