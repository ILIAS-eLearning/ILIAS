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
    
    /**
     * ilCmiXapiAccess constructor.
     * @param ilObjCmiXapi $object
     */
    public function __construct(ilObjCmiXapi $object)
    {
        $this->object = $object;
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
    public function hasWriteAccess()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return (bool) $DIC->access()->checkAccess(
            'write',
            '',
            $this->object->getRefId(),
            $this->object->getType(),
            $this->object->getId()
        );
    }
    
    /**
     * @return bool
     */
    public function hasEditPermissionsAccess()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $editPermsAccess = $DIC->access()->checkAccess(
            'edit_permission',
            '',
            $this->object->getRefId(),
            $this->object->getType(),
            $this->object->getId()
        );
        
        if ($editPermsAccess) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return bool
     */
    public function hasOutcomesAccess()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $outcomesAccess = $DIC->access()->checkAccess(
            'read_outcomes',
            '',
            $this->object->getRefId(),
            $this->object->getType(),
            $this->object->getId()
        );
        
        if ($outcomesAccess) {
            return true;
        }
        return false;
    }
    
    /**
     * @return bool
     */
    public function hasStatementsAccess()
    {
        if ($this->object->isStatementsReportEnabled()) {
            return true;
        }
        
        return $this->hasOutcomesAccess();
    }
    
    /**
     * @return bool
     */
    public function hasHighscoreAccess()
    {
        if ($this->object->getHighscoreEnabled()) {
            return true;
        }
        
        return $this->hasOutcomesAccess();
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
