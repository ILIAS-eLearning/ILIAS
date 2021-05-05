<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumerAccess
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerAccess
{
    const GLOBAL_ADMIN_ROLE_ID = 2;
    /**
     * @var ilObjLTIConsumer
     */
    protected $object;
    
    /**
     * ilLTIConsumerAccess constructor.
     * @param ilObjLTIConsumer $object
     */
    public function __construct(ilObjLTIConsumer $object)
    {
        $this->object = $object;
    }
    
    /**
     * @param $permission
     * @return bool
     */
    protected function checkAccess($permission)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return $DIC->access()->checkAccess(
            $permission,
            '',
            $this->object->getRefId(),
            $this->object->getType(),
            $this->object->getId()
        );
    }
    
    public function hasWriteAccess()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return $this->checkAccess('write');
    }
    
    public function hasOutcomesAccess()
    {
        if ($this->checkAccess('read_outcomes')) {
            return true;
        }
        
        return false;
    }
    
    public function hasEditPermissionsAccess()
    {
        return $this->checkAccess('edit_permission');
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
    public function hasStatementsAccess()
    {
        if (!$this->object->getUseXapi()) {
            return false;
        }
        
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
        if (!$this->object->getUseXapi()) {
            return false;
        }
        
        if ($this->object->getHighscoreEnabled()) {
            return true;
        }
        
        return $this->hasOutcomesAccess();
    }
    
    /**
     * @param ilObjLTIConsumer $object
     * @return ilLTIConsumerAccess
     */
    public static function getInstance(ilObjLTIConsumer $object)
    {
        return new self($object);
    }
    
    /**
     * @return bool
     */
    public static function hasCustomProviderCreationAccess()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return $DIC->rbac()->system()->checkAccess(
            'add_consume_provider',
            ilObjLTIAdministration::lookupLTISettingsRefId()
        );
    }
}
