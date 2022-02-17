<?php declare(strict_types=1);

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
    protected ilObjLTIConsumer $object;
    
    /**
     * ilLTIConsumerAccess constructor.
     * @param ilObjLTIConsumer $object
     */
    public function __construct(ilObjLTIConsumer $object)
    {
        $this->object = $object;
    }

    /**
     * @param string $permission
     * @return bool
     */
    protected function checkAccess(string $permission) : bool
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
    
    public function hasWriteAccess() : bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return $this->checkAccess('write');
    }
    
    public function hasOutcomesAccess() : bool
    {
        if ($this->checkAccess('read_outcomes')) {
            return true;
        }
        
        return false;
    }
    
    public function hasEditPermissionsAccess() : bool
    {
        return $this->checkAccess('edit_permission');
    }
    
    /**
     * @return bool
     */
    public function hasLearningProgressAccess() : bool
    {
        return ilLearningProgressAccess::checkAccess($this->object->getRefId());
    }
    
    /**
     * @return bool
     */
    public function hasStatementsAccess() : bool
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
    public function hasHighscoreAccess() : bool
    {
//        Todo -check
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
    public static function getInstance(ilObjLTIConsumer $object) : ilLTIConsumerAccess
    {
        return new self($object);
    }
    
    /**
     * @return bool
     */
    public static function hasCustomProviderCreationAccess() : bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return $DIC->rbac()->system()->checkAccess(
            'add_consume_provider',
            (int) ilObjLTIAdministration::lookupLTISettingsRefId()
        );
    }
}
