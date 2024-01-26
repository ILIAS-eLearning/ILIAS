<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiAccess
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiAccess
{
    /**
     * @var ilObjCmiXapi
     */
    protected $object;
    protected $access;
    
    /**
     * ilCmiXapiAccess constructor.
     * @param ilObjCmiXapi $object
     */
    public function __construct(ilObjCmiXapi $object)
    {
        global $DIC;
        $this->object = $object;
        $this->access = $DIC->access();
    }
    
    /**
     * @return bool
     */
    public function hasLearningProgressAccess()
    {
        return ilLearningProgressAccess::checkAccess($this->object->getRefId());
    }
    
    /**
     * @return bool
     */
    public function hasWriteAccess($usrId = null)
    {
        if (isset($usrId)) {
            return $this->access->checkAccessOfUser(
                $usrId,
                'write',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        } else {
            return $this->access->checkAccess(
                'write',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        }
    }
    
    /**
     * @return bool
     */
    public function hasEditPermissionsAccess($usrId = null)
    {
        if (isset($usrId)) {
            return $this->access->checkAccessOfUser(
                $usrId,
                'edit_permission',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        } else {
            return $this->access->checkAccess(
                'edit_permission',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        }
    }
    
    /**
     * @return bool
     */
    public function hasOutcomesAccess($usrId = null)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if (isset($usrId)) {
            return $this->access->checkAccessOfUser(
                $usrId,
                'read_outcomes',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        } else {
            return $this->access->checkAccess(
                'read_outcomes',
                '',
                $this->object->getRefId(),
                $this->object->getType(),
                $this->object->getId()
            );
        }
    }
    
    /**
     * @return bool
     */
    public function hasStatementsAccess()
    {
        if ($this->object->isStatementsReportEnabled()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return bool
     */
    public function hasHighscoreAccess()
    {
        if ($this->object->getHighscoreEnabled()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @param ilObjCmiXapi $object
     * @return ilCmiXapiAccess
     */
    public static function getInstance(ilObjCmiXapi $object)
    {
        return new self($object);
    }
}
