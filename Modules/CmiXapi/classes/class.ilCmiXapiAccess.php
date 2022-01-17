<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    
    public function hasLearningProgressAccess(): bool
    {
        return ilLearningProgressAccess::checkAccess($this->object->getRefId());
    }
    
    public function hasWriteAccess(): bool
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
    
    public function hasEditPermissionsAccess(): bool
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
    
    public function hasOutcomesAccess(): bool
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
    
    public function hasStatementsAccess(): bool
    {
        if ($this->object->isStatementsReportEnabled()) {
            return true;
        }
        
        return $this->hasOutcomesAccess();
    }
    
    public function hasHighscoreAccess(): bool
    {
        if ($this->object->getHighscoreEnabled()) {
            return true;
        }
        
        return $this->hasOutcomesAccess();
    }
    
    /**
     * @param ilObjCmiXapi $object
     */
    public static function getInstance(ilObjCmiXapi $object): \ilCmiXapiAccess
    {
        return new self($object);
    }
}
