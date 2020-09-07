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
     * @var integer
     */
    protected $activeId;

    /**
     * @param ilSetting $settings
     * @param ilDBInterface $db
     */
    public function __construct(ilSetting $settings, ilDBInterface $db)
    {
        $this->settings = $settings;
        $this->db = $db;
        
        $this->activeId = null;
    }

    /**
     * @param int $activeId
     */
    public function setActiveId($activeId)
    {
        $this->activeId = $activeId;
    }

    /**
     * @return int
     */
    public function getActiveId()
    {
        return $this->activeId;
    }

    private function getLockModeSettingValue()
    {
        return $this->settings->get('ass_process_lock_mode', ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE);
    }

    /**
     * @return ilTestProcessLockerDb|ilTestProcessLockerFile|ilTestProcessLockerNone
     */
    public function getLocker()
    {
        switch ($this->getLockModeSettingValue()) {
            case ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_NONE:
                
                $locker = new ilTestProcessLockerNone();
                break;
                
            case ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_FILE:

                require_once 'Modules/Test/classes/class.ilTestProcessLockFileStorage.php';
                $storage = new ilTestProcessLockFileStorage($this->getActiveId());
                $storage->create();

                $locker = new ilTestProcessLockerFile($storage);
                break;
            
            case ilObjAssessmentFolder::ASS_PROC_LOCK_MODE_DB:

                $locker = new ilTestProcessLockerDb($this->db);
                break;
        }
        
        return $locker;
    }
}
