<?php declare(strict_types=1);

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
     */
    public function __construct(ilObjLTIConsumer $object)
    {
        $this->object = $object;
    }

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
        return $this->checkAccess('read_outcomes');
    }
    
    public function hasEditPermissionsAccess() : bool
    {
        return $this->checkAccess('edit_permission');
    }
    
    public function hasLearningProgressAccess() : bool
    {
        return ilLearningProgressAccess::checkAccess($this->object->getRefId());
    }
    
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
    
    public static function getInstance(ilObjLTIConsumer $object) : ilLTIConsumerAccess
    {
        return new self($object);
    }
    
    public static function hasCustomProviderCreationAccess() : bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        return $DIC->rbac()->system()->checkAccess(
            'add_consume_provider',
            (int) ilObjLTIAdministration::lookupLTISettingsRefId()
        );
    }
}
